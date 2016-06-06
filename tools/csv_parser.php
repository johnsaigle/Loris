<?php 
/**
* Import script for the biobanking module. Reads data from a csv (export from external management system)
* and parses it for entry into MySQL database.
* @author John Saigle <john.saigle@mcgill.ca>
* @license  GPLv3
*/

# include useful LORIS classes
require_once __DIR__ . "/../vendor/autoload.php";
require_once 'generic_includes.php';
require_once 'Database.class.inc';

# create constants for column names in export
define('STUDY', 0);
define('STUDY_ID', 1);
define('PATIENT_FIRST_NAME', 2);
define('PATIENT_LAST_NAME', 3);
define('UNIQUE_PATIENT_ID', 4);
define('HOSPITAL_#', 5);
define('DATE_OF_BIRTH', 6);
define('csvDIAGNOSIS', 7);
define('SEX', 8);
define('AGE_AT_VISIT', 9);
define('VISIT', 10);
define('VISIT_DATE', 11);
define('csvQUALITY', 12);
define('csvTYPE', 13);
define('csvNUM_VIALS', 14);
define('csvVOLUME', 15);
define('VISIT_COMMENTS', 16);

# load csv data into memory
$date = array();
$rows = array();
$handle = fopen('bio.csv','r');
while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
    array_push($rows, $data);
}

$biospecimen_id = 100000;
# open connection to database
$db = Database::singleton();
$table_name = 'biospecimen';
$counter = 1; # skip table headers in csv data
while ($counter < sizeof($rows)) {
    $row = $rows[$counter];
    print_r($row);
	try {
	    $db->insert(
		'biospecimen',
		array (
			'biospecimen_id' => strval($biospecimen_id + $counter),
			'CandId' => '100000', #hard-coded for now, change later
			'sample_quality' => $row[csvQUALITY],
			'diagnosis' => $row[csvDIAGNOSIS],
			'sample_type' => $row[csvTYPE],
			'volume_mls' => $row[csvVOLUME],
			'num_of_vials_available' => $row[csvNUM_VIALS]
			)
	    );
	} catch (Exception $e) {
	    echo "An exception occurred when attempting to import csv information into database: $e";
	}
    $counter += 1;
}
?>
