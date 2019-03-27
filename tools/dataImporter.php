<?php declare(strict_types=1);
/**
 * This script is designed to work with the dataExtractor tool to push data into
 * an Open Science LORIS instance. 
 *
 * After data has been extracted by the other tool,
 * this script will take as input that data dump (in CSV format) as well as a 
 * mapping file of PSCIDs from the source study to new PSCIDs in the Open 
 * Science database. This mapping file must be kept local and private. 
 *
 * This tool will link the two CSV files by comparing the old PSCIDs and
 * generate SQL statements needed to add the source data into the open database.
 * TODO Optionally this script will automatically run the commands on behalf
 * of the user.
 *
 * This script relies heavily on the column layout of the two CSV files. Old PSCIDs
 * from the source file must be in the left-most column of each file. Further
 * structural requirements are described in the @var annotations for the constants
 * below.
 *
 * @category Tools
 * @package  Open Science
 * @author   John Saigle <john.saigle@mcin.ca>
 * @license  http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 * @link     https://www.github.com/aces/Loris
 */

/**
 * Minimum number of arguments needed to run this script.
 *
 * @var int
 */
const NUM_ARGS_REQUIRED = 4;

/*
 * Expected indices of parameters passed to this script via $argv
 *
 * @var int
 */
const MAPPING_ARG_INDEX = 2;
const DATA_ARG_INDEX = 3;
const EXCLUDED_ARG_INDEX = 4; // Used only for visit label imports

/**
 * These constants represent the indices of columns within the mapping file.
 * The file passed must have the headers organized in this order. 
 * The names of the columns in the csv file don't matter as long as they are formatted
 * like this.
 *
 *
 * Format:
 *      OLD_PSCID,NEW_PSCID,NEW_CANDID
 *
 * @var int
 */
const OLD_PSCID = 0;
const NEW_PSCID = 1;
const NEW_CANDID = 2;

/**
 * The DATA file passed must have the headers organized in this order. This
 * file is generated by running the dataImporter tool. 
 * The CSV header COLUMN_NAME MUST match the source and destination column 
 * name in the MySQL database as this is how the script determines where the 
 * data should be updated.
 * COLUMN_NAME contains the data from the source database.
 *
 * Format:
 *      OLD_PSCID,COLUMN_NAME
 *
 * @var int
 */
const COLUMN_NAME = 1;

/**
 * These constants are used to make SQL command output more clear.
 *
 * @var int
 */
const SET_COLUMN = 0;
const SET_NEWVALUE = 1;
const WHERE_COLUMN = 0;
const WHERE_OLDVALUE = 1;

/**
 * Command line argument for the mode of data importation.
 */
const COLUMN_IMPORT = 'column';
const VISIT_IMPORT = 'visits';

require_once 'generic_includes.php';

$usage = <<<USAGE
Usage: 

To import a single field from a table:
php {$argv[0]} %s <mapping.csv> <data.csv>
    <mapping.csv>   A CSV file containg columns with OLDPSCID, NEWPSCID, and
                    NEWCANDID
    <data.csv>      A CSV file containing columns with OLDPSCID,DATA

To import visit labels:
php {$argv[0]} %s <mapping.csv> <visits.csv> [excluded.csv]
    <mapping.csv>   A CSV file containg columns with OLDPSCID, NEWPSCID, and
                    NEWCANDID
    <visits.csv>    A CSV file containing information from the 'session' table.
                    Generated by dataExtractor.
    [excluded.txt]  Optional. A file containing Visit_labels to be excluded
                    from import. One per line.
        
 
USAGE;


/* BEGIN SCRIPT */

// Die if not enough arguments.
if (count($argv) < NUM_ARGS_REQUIRED) {
    die (sprintf($usage, COLUMN_IMPORT, VISIT_IMPORT));
}

// Die if invalid execution mode supplied.
$mode = $argv[1];
if ($mode !== COLUMN_IMPORT && $mode !== VISIT_IMPORT) {
    die (sprintf($usage, COLUMN_IMPORT, VISIT_IMPORT));
}

// Get data from CSV files.
$mappingRows = populateArrayFromCSV($argv[MAPPING_ARG_INDEX]);
$dataRows = populateArrayFromCSV($argv[DATA_ARG_INDEX]);

// Creating this array allows referencing the columns by index. This allows the
// use of name constants to access the data from within the CSV files instead
// of hard-coding column names into this script.
$mappingHeaders = array_keys($mappingRows[0]);
$dataHeaders = array_keys($dataRows[0]);

// Find all the candidates present in both the data file and the mapping file by
// doing a set intersection on the list of old PSCIDs. 
// It's more efficient to do this once before iterating over every data row 
// rather than checking if a PSCID exists in both arrays on each iteration of 
// the data.csv file.
$oldPSCIDsInMappingFile = array();
$oldPSCIDsInDataFile = array();
foreach($mappingRows as $row) {
    $oldPSCIDsInMappingFile[] = $row[$mappingHeaders[OLD_PSCID]];
}
foreach($dataRows as $row) {
    $oldPSCIDsInDataFile[] = $row[$dataHeaders[OLD_PSCID]];
}
// array_intersect preserves keys but we don't want them.
$sharedCandidates = array_values(
    array_intersect($oldPSCIDsInMappingFile, $oldPSCIDsInDataFile)
);

if (count($sharedCandidates) < 1) {
    die(
        'No shared candidates in mapping file and data file. Nothing to update.'
        . PHP_EOL
    );
}

echo sprintf(
    "Found %s PSCID(s) in common between mapping file %s and data file %s\n\n",
    count($sharedCandidates),
    $argv[MAPPING_ARG_INDEX],
    $argv[DATA_ARG_INDEX]
);

// Build a one-to-one mapping of old to new PSCIDs with the old IDs as the key.
$PSCIDMapping = array();
foreach($mappingRows as $row) {
    $oldPSCID = $row[$mappingHeaders[OLD_PSCID]];
    $newPSCID = $row[$mappingHeaders[NEW_PSCID]];
    $PSCIDMapping[$oldPSCID] = $newPSCID;
}

// Create a mapping of PSCIDs to CandIDs to allow for direct lookup of a CandID
// given a PSCID. It is more efficient to use this dictionary than searching the
// CSV rows for a CandID each time we need one.
$candIDMapping = array();
foreach ($mappingRows as $row) {
    $newPSCID = $row[$mappingHeaders[NEW_PSCID]];
    $newCandID = $row[$mappingHeaders[NEW_CANDID]];
    $candIDMapping[$newPSCID] = $newCandID;
}
    

// Prepare the table name and column names to be updated.
switch ($mode) {
case COLUMN_IMPORT:
    // The name of the column to update.
    // NOTE For now only the candidate table is supported.
    $table = 'candidate';
    $dataColumn = $dataHeaders[COLUMN_NAME];
    break;
case VISIT_IMPORT:
    // Visit label information is found in the session table.
    $table = 'session';
    // A string of all the column names to update.
    $setColumns = implode(',', $dataHeaders);
    
    // Get visit labels to exclude, if any
    $excludedFile = $argv[EXCLUDED_ARG_INDEX] ?? ''; 
    if (!empty($excludedFile)) {
        $excludedVisitLabels = explode("\n", file_get_contents($excludedFile));
    }
}
    
// Create a queue of commands to execute or print depending on the operation
// mode of the script
$INSERTQueue = array();
$UPDATEQueue = array();

// A count of visit labels excluded from importation.
$excludedCount = 0;

// Get a list of existing sessions so we know when to UPDATE vs. INSERT data.
// Again create a lookup mapping so avoid searching the entire array each time.
// Since a candidate will often have multiple Visit_labels, this array will be
// multi-dimensional containing a mapping of a (new) CandID to the Visit_labels
// that exists for them in the DB, e.g.
//
// Array (
//     'CandID' => Array (
//          'V1',
//          'V2'
//     )
// )
$existingSessions = array();
$result = $DB->pselect(
    'SELECT CandID,Visit_label FROM session',
    array()
);
foreach ($result as $row) {
    // Add the visit label in this row to the array of visit labels in
    // $existingSessions. Indexed by a CandID.
    $visitLabelsForCandidate = $existingSessions[$row['CandID']] ?? array();
    array_push(
        $visitLabelsForCandidate,
        $row['Visit_label']
    );
    $existingSessions[$row['CandID']]  = $visitLabelsForCandidate;
}
unset($result);

$existingPSCIDs = $DB->pselectCol(
    'SELECT PSCID from candidate',
    array()
);

// If some PSCIDs exist in the mapping file but not in the database, they will
// be added to this array and printed to the user as it may be important to
// know.
$skippedPSCIDs = array();

// Iterate over every shared candidate. Then generate the necessary UPDATE 
// command needed to add the COLUMN data from the data file into the new DB.
//
// Iterating over every row in the CSV file instead of the list of PSCIDs
// enables this loop to operate with O(n) time. Iterating over PSCIDs does not
// allow for direct lookup of data cells as the CSV file must be searched each
// time for the PSCID. This results in O(n^2) execution time. To avoid this
// problem the PSCID mapping has been created above.
foreach ($dataRows as $row) {
    // Get the old PSCID. Should always be in the column at index 0.
    $oldPSCID = $row[$dataHeaders[OLD_PSCID]];
    
    // Skip candidates not present in mapping file.
    if (!in_array($oldPSCID, $sharedCandidates, true)) {
        continue;
    }

    $newPSCID = $PSCIDMapping[$oldPSCID];

    // Check that the new PSCID from the mapping file is actually present in
    // the Database. It's possible that the candidate has been deleted since
    // the mapping file was created.
    $newPSCIDExists = in_array(
        $newPSCID,
        $existingPSCIDs,
        true
    );
    if (!$newPSCIDExists) {
        // Don't add a skipped candidate to the skipped array if we've already
        // encountered it. This can occur for example when multiple visit labels
        // are listed for a single candidate.
        $skippedAlready = in_array(
            $newPSCID,
            $skippedPSCIDs,
            true
        );
        if (!$skippedAlready) {
            $skippedPSCIDs[] = $newPSCID;
        }
        continue;
    }

    $newCandID = $candIDMapping[$newPSCID];

    // Populate the data for the MySQL command to be output/run by this script,
    // including table name, SET, and WHERE data.
    switch ($mode) {
    case COLUMN_IMPORT:
        // Retrive the cell containing the new data for this candidate.
        //$data = array($dataColumn => $row[$dataColumn]);
        $data = $row;
        // Discard the PSCID value in this CSV row. It is equal to the old
        // PSCID and only used for linking. It should not be included in the
        // UPDATE statement.
        unset($data['PSCID']);
        $where = array('PSCID' => $newPSCID);
        $UPDATEQueue[] = array(
            'table' => $table,
            'data' => $data,
            'where' => $where
        );
        break;
    case VISIT_IMPORT:
        // Skip this visit label if it is in the list of excluded labels.
        if (isset($excludedVisitLabels)) {
            if (in_array($row['Visit_label'], $excludedVisitLabels, true)) {
                $excludedCount++;
                continue 2;
            }
        }
        $data = $row;
        // We don't want PSCID information from the CSV file included in the
        // SET statement. It's only used for linking.
        unset($data['PSCID']);
        
        // Prepare command information.
        $where = array('CandID' => $candIDMapping[$newPSCID]);
        $command = array(
            'table' => $table,
            'data' => $data,
            'where' => $where
        );

        if (!isset($existingSessions[$newCandID])
            || !in_array(
                $row['Visit_label'],
                $existingSessions[$newCandID],
                true
            )
        ) {
            // INSERT if the candidate doesn't have any visit labels or if the
            // current visit label is not present in their list of visit labels.
            $command['data']['CandID'] = $newCandID;
            $INSERTQueue[] = $command;
        } else {
            // UPDATE if Visit label already present for this candidate
            $command['where']['Visit_label'] = $row['Visit_label'];
            $UPDATEQueue[] = $command;
        }
        break;
    }
}

if (isset($excludedVisitLabels)) {
    print "$excludedCount visit label(s) excluded." . PHP_EOL . PHP_EOL;
}
$skippedPSCIDCount = count($skippedPSCIDs);
if ($skippedPSCIDCount > 0) {
    print "Skipped processing information for $skippedPSCIDCount candidate(s) " .
        "as they do not exist in the candidate table: " .
        PHP_EOL .
        implode("\n", array_map('formatBulletPoint', $skippedPSCIDs)) .
        PHP_EOL . PHP_EOL;
}

$report = array_merge(
    formatUPDATEStatements($UPDATEQueue), 
    formatINSERTStatements($INSERTQueue, $dataHeaders)
);

if (count($report) < 1) {
    die('No SQL commands generated.' . PHP_EOL);
}

// Write the report to file.
$path = $config->getSetting('base') . 'project/data_import/'; 
if (!is_dir($path)) {
    mkdir($path);
}
$target = $path . date("Ymd-His") . '_dataImporterOutput.sql';
file_put_contents($target, implode("\n", $report));

echo "SQL commands to import data from {$argv[DATA_ARG_INDEX]} written to " .
    "$target." .
    PHP_EOL;


/* END SCRIPT */

// Format commands for printing.
function formatUPDATEStatements(array $commandQueue)
{
    if (count($commandQueue) < 1) {
        return array();
    }
    $table = $commandQueue[0]['table'];
    $formattedCommand = <<<SQL
UPDATE $table
SET %s
WHERE %s;

SQL;
    $report = array();
    foreach ($commandQueue as $command) {
        $setString = array();
        $whereString = array();
        // Iterate over all columns and create a formatted string. Will only be one
        // column for COLUMN_IMPORT mode but several for VISIT_IMPORT.
        foreach ($command['data'] as $column => $value) {
            $value = quoteWrapUnlessNULL($value);
            $setString[] = "$column = $value";
        }
        // Interpolate the $setString into the SQL heredoc above and add it to the
        // final $report output.
        foreach ($command['where'] as $column => $value) {
            $whereString[] = "$column = '$value'";
        }
        $report[] = sprintf(
            $formattedCommand, 
            implode(', ', $setString),
            implode(' AND ', $whereString)
        );
    }
    return $report;
}

/**
 *
 * @return string[]
 */
function formatINSERTStatements(array $commandQueue, array $columnNames): array {
    if (count($commandQueue) < 1) {
        return array();
    }
    // Format commands for printing.
    $report = array();
    $table = $commandQueue[0]['table'];
    $formattedCommand = <<<SQL
INSERT INTO $table
(%s)
VALUES(%s);

SQL;
    foreach ($commandQueue as $command) {
        $report[] = sprintf(
            $formattedCommand,
            implode(',', array_keys($command['data'])),
            implode(',', array_map('quoteWrapUnlessNULL', array_values($command['data'])))
        );
    }
    return $report;
}


// Converts a csv file into a two-dimensional PHP array where each element
// contains an associative array of the column names and the values.
//
// e.g. file.csv:
//
//      oldPSCID,newPSCID,newCandID
//      MON001,MON999,123456
//      ...
//
// becomes:
// [0] => Array (
//     [oldPSCID] => MON001,
//     [newPSCID] => MON999,
//     [newCandID] => 123456
// ),
// ...
//
// @throws InvalidArgumentException If no data in files or if they're inaccessible
//
// @return array The contents of the CSV file loaded into an associative array.
//
function populateArrayFromCSV(string $filename): array {
    $data = csvToArray($filename);
    if (count($data) < 1) {
        throw new InvalidArgumentException(
            "No CSV data found in $filename" . PHP_EOL
        );
    }
    return $data;
}

/**
 * Convert a comma separated file into an associated array.
 * The first row should contain the array keys.
 *
 * Example:
 *
 * @param string $filename Path to the CSV file
 * @param string $delimiter The separator used in the file
 * @return array
 * @link http://gist.github.com/385876
 * @author Jay Williams <http://myd3.com/>
 * @copyright Copyright (c) 2010, Jay Williams
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
function csvToArray(string $filename='', string $delimiter=','): array
{
	if(!file_exists($filename) || !is_readable($filename))
		return array();

	$header = null;
	$data = array();
	if (($handle = fopen($filename, 'r')) !== FALSE)
	{
		while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
		{
			if (!$header)
				$header = $row;
			else
				$data[] = array_combine($header, $row);
		}
		fclose($handle);
	}
	return $data;
}

/**
 * Wraps a string in single quotes. Useful for an array_map callback when
 * preparing values to be used in a SET statement.
 *
 */
function quoteWrapUnlessNULL(string $string): string
{
    if ($string === 'NULL') {
        return $string;
    }
    return sprintf("'%s'", $string);
}

function formatBulletPoint(string $string): string
{
    return sprintf("\t* $string");
}
