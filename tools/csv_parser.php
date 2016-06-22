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

# create constants for column names in export. used to abstract away the orderings of the csv table form the rest of the script.
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

# load csv data into memory
$rows = array();
$handle = fopen('tm_dump.csv','r');
while (($data = fgetcsv($handle, 5000, ",")) !== FALSE) {
    array_push($rows, $data);
}

# open connection to database
$db = Database::singleton();
$table_name = 'biospecimen';
$counter = 1; // the '0' row in the csv file will be the table headers. Skip them

# iterate over all rows, filling individual rows with table info
while ($counter < sizeof($rows)) {
    $row = $rows[$counter];
    print_r($row);
	try {
	    $db->insert(
		'biospecimen',
		array ( // The order of the following dictionary matches what will be displayed 
			// if you run the 'describe' query on the 'biospecimen' table.
			// if a given field is missing, that means it is assigned by LORIS proper, not this script.
			'biospecimen_id' => $row[SAMPLE_NUMBER],
			'sample_type' => $row[SAMPLE_TYPE],
			'CandID' => '100000', /*TODO: Get 'candidate id' from a future csv file. May actually be assigned in LORIS.
						We will need some sort of algorithm to map candidate IDs from the source file to 
						LORIS study candidates*/
			'date_collected' => $row[COLLECTION_DATE],
			//'study' =>$row[], //TODO: Same as above -- must have a way to map source studies to LORIS studies
			'diagnosis' =>$row[DIAGNOSIS_AT_EVENT],
			'sample_quality' =>$row[QUALITY],
			'date_received' =>$row[RECEIPT_DATE],
			'date_symptom_onset' =>$row[SYMPTOM_ONSET],
			'date_preparation' =>$row[PREPARATION_DATE],
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
			)
	    );
	} catch (Exception $e) {
	    echo "An exception occurred when attempting to import csv information into database: $e";
	}
    $counter += 1;
}
?>
