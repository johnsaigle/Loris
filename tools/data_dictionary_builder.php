#!/data/web/neurodb/software/bin/php
<?php
/**
 * Purpose:
 * data_dictionary_builder.php automatically generates the following tables from ip_output.txt
 * parameter_type
 * parameter_type_category
 * parameter_type_category_rel
 * 
 * These tables (now referred to as the parameter_type.* tables) are the inputs to quat.php and the Data Query GUI.
 * 
 * Input:
 * data_dictionary_builder.php takes as input the ip_output.txt file (generated by quickform_parser.php) and inserts records for each field of each discovered NDB_BVL_Instrument.  To be complete, this tool must be run on an ip_output.txt file that constructed from all instruments.  Instrument parameter_type.Parameter_typeIDs will be reassigned autoincremented values that will no longer correspond to query_gui_stored_queries.conditionals values.
 * 
 * *
 * * WARNING THIS FILE ACTIVELY AFFECTS THE DATABASE INDICATED BY CONFIG.XML.  READ THE SCRIPT FIRST AND MODIFY AS NECESSARY.
 * *
 * 
 * Functioning:
 * Currently, this script deletes all entries in parameter_type.* relating to parameter_type_category.Type = 'Instrument' and regenerates them.  parameter_type primary keys (ParameterTypeID) are preserved to correspond to the query_gui_downloadable_queries and query_gui_stored_queries tables though Parameter_type_category primary keys ARE NOT preserved.  Entries relating to parameter_type_category.Type Metafields or manually entered custom fields of parameter_type_category.Type != 'Instrument' are preserved.
 * 
 * Optional:
 * If an instrument has a long name, a shorter identifier may be entered in the $abbreviations array.  This helps unclutter the DQG display interface.
 * 
 * Further Improvements:
 * It is recommended that this script be changed to be intelligent about its methods, possibly with command line options that allow it to be more selective about its actions and deletion targets.
 * 
 * Usage:
 * php data_dictionary_builder.php
 * 
 * Deprecated:
 * This script used to delete absolutely all entries from parameter_type.*, without regard to their parameter_type_category.Type.
 * 
 * ex cmd: php data_dictionary_builder.php
 *
 * @package main
 * @subpackage query_gui
 */

//Ensure php version compatability
//taken from php.net notes
if (version_compare(phpversion(),'4.3.0','<'))
{
    define('STDIN',fopen("php://stdin","r"));
    register_shutdown_function( create_function( '' , 'fclose(STDIN);
    fclose(STDOUT); fclose(STDERR); return true;' ) );
}


// PEAR::Config
require_once "Config.php";

// define which configuration file we're using for this installation
$configFile = "../project/config.xml";

// load the configuration data into a global variable $config
$configObj = new Config;
$root =& $configObj->parseConfig($configFile, "XML");
if(PEAR::isError($root)) {
    die("Config error: ".$root->getMessage());
}
$configObj =& $root->searchPath(array('config'));
$config =& $configObj->toArray();
$config = $config['config'];
unset($configObj, $root);

// require all relevant OO class libraries
require_once "../php/libraries/Database.class.inc";
require_once "../php/libraries/NDB_Config.class.inc";

/*
* new DB Object
*/
$DB =& Database::singleton($config['database']['database'], $config['database']['username'], $config['database']['password'], $config['database']['host']);
if(PEAR::isError($DB)) {
    print "Could not connect to database: ".$DB->getMessage()."<br>\n";
    die();
}

//Get the entries we already have in the DB
getColumns("Select Name, ParameterTypeID from parameter_type", $DB, $parameter_types);

//Delete in the parameter_type table relating to parameter_type_category.Type = 'Instrument', without affecting other entries.
//get parameter_type_category.Type ='Instrument' ParamenterTypeCategoryIDs
getColumn("select ParameterTypeCategoryID from parameter_type_category where Type = 'Instrument'", $DB, $instrumentParameterTypeCategoryIDs);
$instrumentParameterTypeCategoryIDString = implode(', ', $instrumentParameterTypeCategoryIDs);

//get all 'Instrument' ParameterTypeIDs
getColumn("select ParameterTypeID from parameter_type_category_rel where ParameterTypeCategoryID in ($instrumentParameterTypeCategoryIDString)", $DB, $instrumentParameterTypeIDs);
$instrumentParameterTypeIDString = implode(', ', $instrumentParameterTypeIDs);

//delete all 'Instrument' entries from parameter_type_category_rel
$DB->run("delete from parameter_type_category_rel where ParameterTypeID in ($instrumentParameterTypeIDString)"); //where 1=1");

//delete all 'Instrument' entries from parameter_type_category
$DB->run("delete from parameter_type_category where ParameterTypeCategoryID in ($instrumentParameterTypeCategoryIDString)");

//delete all 'Instrument' entries from parameter_type
$DB->run("delete from parameter_type where ParameterTypeID in ($instrumentParameterTypeIDString)");

print "Cleared data from BVL instruments\n";

//Instruments with excessively wordy names.  Entries are OPTIONAL
//It would be really nice to have a table_names.Abbreviation field, but messy to change the names *everywhere*.
$abbreviations=array(
'childs_health_questions_12'=>'chq12',
'childs_health_questions_18_36'=>'chq18_36',
'childs_health_questions_6'=>'chq6',
'child_bearing_attitudes'=>'cba',
'ecbq_temperament'=>'ecbq',
'edinburgh_postnatal_depression_scale'=>'epds',
'health_well_being'=>'hwb',
'home_environment_evaluation'=>'hee',
'ibq_temperament'=>'ibq',
'montreal_prenatal'=>'montreal_prenatal',
'parental_bonding_inventory'=>'pbi',
'state_trait_anxiety_inventory'=>'stai',
'med_records_24' => 'med_rec_24',
'med_records_recruit' => 'med_rec_recr',
'general_medical_history' => 'gmh'
);



print "Reading instruments\n";
//Read the ip_output.txt staging file.
$fp=fopen("ip_output.txt","r");
$data=fread($fp, filesize("ip_output.txt"));
fclose($fp);

print "Parsing instruments\n";
$instruments=explode("{-@-}",trim($data));

//process all HTML_QuickForm Elements found in ip_output.txt
$tblCount=0;
$parameterCount=0;

foreach($instruments AS $instrument){
    $catId="";
    $items=explode("\n",trim($instrument));
    foreach($items AS $item){
        $paramId="";
        $bits=explode("{@}",trim($item));
        switch($bits[0]){
            case "table":
                $table=$bits[1];
                print "At $table\n";
            break;

            case "title":
                $title=$bits[1];
                 $error=$DB->insert("parameter_type_category", array('Name'=>$title, 'Type'=>'Instrument'));
                 $catId=$DB->lastInsertID;
                 $tblCount++;
            break;

            case "header":
                continue;
            break;

            //for HTML_QuickForm versions of standard HTML Form Elements...
            default:
//continue; // jump straight to validity for debugging
                if(ereg("^Examiner", $bits[1])) {
                    // Treat examiner specially, since it's a select box but we need
                    // to treat it as a varchar. derive_timepoint_variables will derive
                    // the name from the examiner id
                    $bits[0] = "varchar(255)";
                } else if($bits[0]=="select"){
                    $bits[0]=enumizeOptions($bits[3], $table, $bits[1]);
                } else if($bits[0]=="textarea"){
                    $bits[0]="text";
                } else if($bits[0]=="text"){
                    $bits[0]="varchar(255)";
                } else if($bits[0]=="selectmultiple"){
                    $bits[0]="varchar(255)";
                } else if($bits[0]=="checkbox"){
                    $bits[0]="varchar(255)";
                } else if($bits[0]=="static"){
                    $bits[0]="varchar(255)";
                }

print "Inserting $table $bits[1]\n";
                $parameterCount++;
                $bits[2]=htmlspecialchars($bits[2]);
                //find values to insert
                $Name = (array_key_exists($table, $abbreviations) ? $abbreviations[$table] : $table ) . "_" . $bits[1];
                $ParameterTypeID = array_key_exists($Name, $parameter_types) ? $parameter_types[$Name] : '';
                $error=$DB->insert("parameter_type", array('ParameterTypeID'=>$ParameterTypeID,'Name'=>$Name, 'Type'=>$bits[0], 'Description'=>$bits[2], 'SourceField'=>$bits[1], 'SourceFrom'=>$table, 'CurrentGUITable'=>'quat_table_' . ceil(($parameterCount  - 0.5) / 200), 'Queryable'=>'1')); //500 Instrument parameters per quat_table
//                $error=$DB->insert("parameter_type", array('Name'=>(array_key_exists($table, $abbreviations) ? $abbreviations[$table] : $table ) . "_" . $bits[1], 'Type'=>$bits[0], 'Description'=>$bits[2], 'SourceField'=>$bits[1], 'SourceFrom'=>$table, 'CurrentGUITable'=>'quat_table_' . ceil(($parameterCount  - 0.5) / 500), 'Queryable'=>'1')); //500 Instrument parameters per quat_table
                print_r($error);
                if($ParameterTypeID === '') {
                    $paramId= $DB->lastInsertID;
                } else {
                    $paramId = $ParameterTypeID;
                }
                $error=$DB->insert("parameter_type_category_rel",array("ParameterTypeID"=>$paramId, "ParameterTypeCategoryID"=>$catId));
        }   
    }

    if(empty($table)) continue;
print "Inserting validity for $table\n";
    // Insert validity
    $Name = (array_key_exists($table, $abbreviations) ? $abbreviations[$table] : $table ) . "_Validity";
    $ParameterTypeID = array_key_exists($Name, $parameter_types) ? $parameter_types[$Name] : '';
    $error=$DB->insert("parameter_type", 
        array('ParameterTypeID'=>$ParameterTypeID,
              'Name'=>$Name, 
              'Type'=>'enum(\'Questionable\', \'Invalid\', \'Valid\')', 
              'Description'=>"Validity of $table", 
              'SourceField'=>'Validity', 'SourceFrom'=>$table, 'CurrentGUITable'=>'quat_table_' . ceil(($parameterCount  - 0.5) / 150), 'Queryable'=>'1'));
    if($ParameterTypeID === '') {
        $paramId= $DB->lastInsertID;
    } else {
        $paramId = $ParameterTypeID;
    }
    $error=$DB->insert("parameter_type_category_rel",array("ParameterTypeID"=>$paramId, "ParameterTypeCategoryID"=>$catId));
    // Insert administration
print "Inserting administration for $table\n";
    $Name = (array_key_exists($table, $abbreviations) ? $abbreviations[$table] : $table ) . "_Administration";
    $ParameterTypeID = array_key_exists($Name, $parameter_types) ? $parameter_types[$Name] : '';
    $error=$DB->insert("parameter_type", 
        array('ParameterTypeID'=>$ParameterTypeID,
              'Name'=>$Name, 
              'Type'=>'enum(\'None\', \'Partial\', \'All\')', 
              'Description'=>"Administration for $table", 
              'SourceField'=>'Administration', 'SourceFrom'=>$table, 'CurrentGUITable'=>'quat_table_' . ceil(($parameterCount  - 0.5) / 150), 'Queryable'=>'1'));
    if($ParameterTypeID === '') {
        $paramId= $DB->lastInsertID;
    } else {
        $paramId = $ParameterTypeID;
    }
    $error=$DB->insert("parameter_type_category_rel",array("ParameterTypeID"=>$paramId, "ParameterTypeCategoryID"=>$catId));
    // Insert examiner
}

//Copies the modified descriptions from the parameter_type_override to parameter_type
$elements = $DB->pselect("SELECT * FROM parameter_type_override WHERE Description IS NOT NULL",array());
foreach ($elements as $element){
	
	$description = $element['Description'];
	$name = $element['Name'];
	
	$DB->update('parameter_type',array('Description'=>$description),array('Name'=>$name));
}


//Print completion info message
echo "\n\nData Dictionary generation complete:  $tblCount new categories added and $parameterCount new parameters added\n\n";


//script specific utility functions
function enumizeOptions($options, $table, $name){
    $options=explode("{-}",$options);
    foreach($options as $option){
        $option=explode("=>",$option);
        if($option[0]!='NULL'){
            $enum[]=$option[0];
        }
    }
    if(!is_array($enum)){
        echo "$table $name $options\n";
    }
    $enum=implode(",",$enum);
    return "enum($enum)";
}

function getColumn($query, &$DB, &$result){
    $DB->select($query, $TwoDArray);
    foreach($TwoDArray as $container=>$cell) {
        foreach($cell as $key=>$value) {
            $result[] = $value;
        }
    }
    return $result;
}

//Builds an array parameterTypeID=>Name
function getColumns($query, &$DB, &$result){
    $DB->select($query, $TwoDArray);
    foreach($TwoDArray as $containers=>$cells) {
        $values = array_values($cells);
        $result[$values[0]] = $values[1];
    }
    return $result;
}

?>
