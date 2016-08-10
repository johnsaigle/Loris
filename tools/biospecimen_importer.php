<?php 
/**
* Import script for the biobanking module. Reads data from a csv (export from external management system)
* and parses it for entry into MySQL database.
* @author John Saigle <john.saigle@mcgill.ca>
* @license  GPLv3
*/

# Function to convert the date format to be compatible with MySQL
// Tissue Metrix dates are in the format n-M-Y, e.g. 6-Jan-2010. MySQL is Y-m-d, e.g. 2010-01-06
function convert_date($csv_date) {
    if (!$csv_date) return;
    $dateTime = DateTime::createFromFormat('j-M-Y', $csv_date);
    $date = date_format($dateTime, 'Y-m-d');
    return $date;
}

# Display how to use the program, and exit
function print_usage_and_quit() {
    echo 'Usage: php biospecimen_import.php data_file.csv $CandID [v | h]' . "\n";
    exit(1);
}

######### PROGRAM EXECUTION BEGINS #########

# Display help if used incorrectly
// case of empty arguments
if (!isset($argv[1]) || !isset($argv[2])){
    echo 'Oops! Please specify both an input file and a candidate ID.' ."\n";
    print_usage_and_quit();
}
// case of wrong file type
if (!preg_match("/[A-Za-z0-9]*\.csv$/",$argv[1])) {
    echo "Oops! Please use a csv file as input.\n";
    print_usage_and_quit();
}
// set vars based on command line arguments
$data_file = $argv[1];
$CandID = $argv[2];

// determine whether verbose mode or help mode set
$verbose = 0;
$very_verbose = 0;
if (isset($argv[3])) {
    if ($argv[3] == 'v' || $argv[3] == 'verbose') {
        $verbose = 1;
    } elseif ($argv[3] == 'vv') {
        $very_verbose = 1;
    }
    elseif ($argv[3] == 'h' or $argv[3] == 'help' or $argv[3] == '?') {
        print_usage_and_quit();
    }
}


# include useful LORIS classes
require_once __DIR__ . "/../vendor/autoload.php";
require_once 'generic_includes.php';
require_once 'Database.class.inc';

# Create constants for column names in export. used to abstract away the orderings of the csv table form the rest of the script.
# if somewhere down the line the columns in the csv are re-ordered, the constants msut be changed to match. 
# note: many of these may not be useful, but they are contained in the format. These serve as functional variables as well as just noting the columns.
# if the column info changes, these constants will need to be altered to correspond
define('BANK_ID', 0);
define('SEX', 1);
define('DATE_OF_BIRTH', 2);
define('DIAGNOSIS_AT_EVENT', 3);
define('SYMPTOM_ONSET', 4);
define('AGE', 5);
define('VISIT', 6);
define('VISIT_DATE', 7);
define('VISIT_COMMENTS', 8);
define('COLLECTION_DATE', 9);
define('RECEIPT_DATE', 10);
define('PREPARATION_DATE', 11);
define('STUDY_ID', 12);
define('STORAGE_ADDRESS', 13);
define('INVENTORY_STATUS', 14);
define('STORAGE_STATUS', 15);
define('EXTERNAL_NUMBER', 16);
define('SAMPLE_STUDY', 17);
define('SAMPLE_NUMBER', 18);
define('SAMPLE_TYPE', 19);
define('SOP_EMPLOYED', 20);
define('FT_CYCLES', 21);
define('HEMOLYSIS_INDEX', 22);
define('CONCENTRATION', 23);
define('RATIO', 24);
define('QUALITY', 25);
define('QUANTITY_ON_HAND', 26);
define('QUANTITY_UNITS', 27);
define('COMMENTS', 28); //TODO: import into LORIS?

# Check if data is normalized, i.e. that the cells are populated
# and header information is stripped via rep.pl
$answer = readline("Did you normalize the data?\t(Y/n) ");
if ($answer != 'Y') {
    echo "Normalizing data...";
    $file_name = str_replace(".csv", "", $data_file);
    if ($very_verbose) {
        echo "Populating empty cell entries using script 'rep.pl'...\n";
    }
    try {
        exec ("tail -n +2 $data_file | perl rep.pl > $file_name" . "_processed.csv");
        $data_file = $file_name . "_processed.csv";
        echo " done.\n";
        echo "Now using the new file $data_file as input.\n";
    } catch (Exception $e){
        echo "Data normalization failed.";
    }
}

# load csv data into memory
$rows = array();
$handle = fopen($data_file,'r');
if (!$handle) die ("Can't open $data_file. Typo?\n");
while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
    array_push($rows, $data);
}

# customize informaiton relating to the database
# the $DB variable comes from generic_includes.php
$table_name = 'biospecimen'; 
$counter = 0; // after processing, the table headers are removed, so we start at 0 instead of 1
$duplicates = 0;

# iterate over all rows, filling individual rows with table info
while ($counter < sizeof($rows)) {
    $row = $rows[$counter];
    // double check that the data has been processed. if the ID is NaN, then it is unprocessed
    if (!preg_match("/^[0-9]*$/",$row[SAMPLE_NUMBER])) {
        if ($answer == 'Y') {
            echo "No, you didn't. Pay attention and follow the instructions next time.\n";
        } else {
            echo "Header information detected. Data has not been processed.\n";
            echo "Please use rep.pl to process the csv data, or run the script again and enter 'n' when asked if the data has been processed.\n";
        }
        exit(1);
    }
    $biospecimen_array = array( 
        // The order of the following dictionary matches what will be displayed 
        // if you run the 'describe' query on the 'biospecimen' table.
        // if a given field is missing, that means it is assigned by LORIS proper, not this script.
        'biospecimen_id' => $row[SAMPLE_NUMBER],
        'sample_type' => $row[SAMPLE_TYPE],
        'CandID' => $CandID, /*TODO: Get 'candidate id' from a future csv file. May actually be assigned in LORIS.
        We will need some sort of algorithm to map candidate IDs from the source file to 
        LORIS study candidates*/

        # Tissue Metrix dates are in the format n-M-Y, e.g. 6-Jan-2010. MySQL is Y-m-d, e.g. 2010-01-06
        'date_collected' => convert_date($row[COLLECTION_DATE]),
        //'study' =>$row[], //TODO: Same as above -- must have a way to map source studies to LORIS studies
        'diagnosis' =>$row[DIAGNOSIS_AT_EVENT],
        'sample_quality' =>$row[QUALITY],
        'date_received' =>convert_date($row[RECEIPT_DATE]),
        'date_symptom_onset' =>convert_date($row[SYMPTOM_ONSET]),
        'date_preparation' =>convert_date($row[PREPARATION_DATE]),
        'storage_address' =>$row[STORAGE_ADDRESS],
        'inventory_status' =>$row[INVENTORY_STATUS],
        'storage_status' =>$row[STORAGE_STATUS],
        'external_number' =>$row[EXTERNAL_NUMBER],
        'sop_employed' =>$row[SOP_EMPLOYED],
        'ft_cycles' =>$row[FT_CYCLES],
        'hemolysis_index' =>$row[HEMOLYSIS_INDEX],
        'concentration' =>$row[CONCENTRATION],
        'ratio' =>$row[RATIO],
        'quantity_on_hand' =>$row[QUANTITY_ON_HAND],
        'quantity_units' =>$row[QUANTITY_UNITS]
    );

    # Insert into database
    $biospecimen_id = $biospecimen_array['biospecimen_id'];
    if ($very_verbose) {
        echo "Processing biospecimen with id $biospecimen_id. Details: \n";
        print_r($biospecimen_array);
        echo "\n";
    }
    try {
        $query = ("SELECT * FROM $table_name WHERE biospecimen_id=:id");
        $params = array('id'=>$biospecimen_id);
        $already_exists = $DB->pselect($query,$params);
        if ($already_exists) {
            if ($verbose) {
                echo "$biospecimen_id already exists in the database. Skipping to next id...\n";
            }
            $duplicates += 1;
        }
        else { 
            $DB->insert($table_name,$biospecimen_array);
            if ($verbose) {
                echo ("Sample $biospecimen_id inserted into table $table_name.\n");
            }
        }
    } catch (Exception $e) {
        echo "An exception occurred when attempting to insert specimen with index $counter. Perhaps biospecimen with id $biospecimen_id already exists in $table_name?\n";
    }
    $counter += 1;
}
if ($duplicates >= 5) {
    echo "Many of these samples already exist in the database. Truncate the table first next time.\n";
    sleep(2);
}
$num_new = $counter - $duplicates;
echo "Import complete. $counter specimen(s) processed. $num_new new sample(s) registered. $duplicates sample(s) skipped.\n";
?>
