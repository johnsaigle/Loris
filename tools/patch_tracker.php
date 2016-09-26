<?php
/**
 * Script to track which SQL patches have been applied,
 * and to apply unapplied patches.
 *
 * PHP version 5
 *
 * @category Main
 * @package  Loris
 * @author   John Saigle <jsaigle.mcin@gmail.com>
 * @license  GPLv3
                                XXXXXXXXXXXXXXXXXXXXXXX
                       XXXXXXXXXXXXXX                  XXXX
                    XXX                                    XXXX
                   X                                           XX
                   X   XXXXXXXX                  XXXXXXXX    XX X
                  X  XXX      XXX             XXXX      XXX   XX
                XX                                             XX
               XX     XXXXXXXXX         X      XXXXXXXXX         X
              X     XX   @@    XX      X     XX   @@    XX       XX
              X    XX    @@     XX    XX    XX    @@     XX       XX
              X  XX      @@      XX   X   XX      @@      XX       X
              X  X       @@      X X X    X       @@      X X      X
              X   XX     @@    XX    X     XX     @@    XX         X
              X    XXXX XXXXXXX     X       XXXX XXXXXXX           X
              X                     X                              X
              X   XXXXXXXXX        XX           XXXXXXXXX         XX
              XX          XXXX     X          XX                  X
               XX                 X         XXX                  XX
                XXX               X                            XX
                  XX             XX                           XX
                   XXX           XXXXXX XX                  XXX
+---------------+    XX          XX                        XX
|Don't fuck with|      XXX                               XX
|   my code.    |        XX                            XX
+---------+-----+         XX  XXXXXXXXXXXXXXXXX      XX
          |                X          XXXXXX        XX
          |                 X                    XXX
          +----------------+  XX              XXX
                                XXXXXXXXXXXXXXX
 */

require_once __DIR__ . "/../vendor/autoload.php";
require_once 'generic_includes.php';
require_once 'Database.class.inc';

/** 
* Read input from STDIN. Stolen, probably from StackOverflow
*/
function read_stdin()
{
    $fr=fopen("php://stdin","r");   // open our file pointer to read from stdin
    $input = fgets($fr,128);        // read a maximum of 128 characters
    $input = rtrim($input);         // trim any trailing spaces.
    fclose ($fr);                   // close the file handle
    return $input;                  // return the text entered
}

/**
* Perform a regex match on something that looks like a patch_name according to our format
*/
function patch_match($filename) {
    # roughly similar to '0000-00-00-PatchName.sql'
    if (preg_match('/^\d{4}-\d{2}-\d{2}-/', $filename)) {
        return true;
    }
    return false;
}

/**
* Isolate array values from nested structure of DB query results
* There is likely a better way to do this
*/
function get_array_from_query_result($query_result){
    $to_ret = array();
    # TODO: this is messy.. better query possible?
    $unapplied_patches = array();
    foreach ($query_result as $key=>$value) {
        foreach ($value as $kkey=>$vvalue) {
            array_push($to_ret, $vvalue);
        }
    }
    return ($to_ret);
}

/** 
* Wrapper for DB selects with error handling
*/
function query_db($query_string,$params) {
    $DB = Database::singleton();
    try {
        if ($DB) {
            $result = $DB->pselect($query_string, $params);
        } else {
            exit ("Could not establish database connection.");
        }
    } catch (Exception $e) {
        echo "Exception occurred when trying to read from database: $e";
    }
    return $result;
}

/**
* Nice printing for arrays
*/ 
function pretty_print_array_elements($array) {
    foreach ($array as $element) {
        echo "\t * $element\n";
    }
    echo "\n";
}
##################################################### SCRIPT START ######################################################


/* Get both applied and unapplied patches from DB */
# Construct query string
$query_string ='SELECT
                patch_name
                FROM patch_record
                WHERE applied=:status';

# Get unapplied patches
$params  = array('status' => 'N');
$result = query_db($query_string,$params);
$unapplied_patches = get_array_from_query_result($result);

# Get applied patches
$params  = array('status' => 'Y');
$result = query_db($query_string,$params);
$applied_patches = get_array_from_query_result($result);

/* Scan the SQL directory for patches */
# get loris root dir
$basedir = $DB->pselectone("
    SELECT
    Value
    FROM Config
    WHERE ConfigID=:ID",
    array('ID' => '22') // this is the ConfigID of the 'base' variable representing the loris root dir
);
# Print status and isolate patch files from other files in the SQL dir
$all_patch_files = array();
$patch_path = $basedir . 'SQL/';
$files = scandir($patch_path);
if (!$files) {
    exit ("No SQL files were found in $patch_path. Exiting.\n");
} else {
    foreach($files as $file) {
        if (patch_match($file)) {
            array_push($all_patch_files, $file);
        }
    }
}
$new_patches = array_diff($all_patch_files, array_merge($applied_patches, $unapplied_patches));

if (sizeof($argv) == 1) {
    /* Print status of patch files */

    $up_to_date = sizeof(array_diff($all_patch_files, $applied_patches)) === 0;
    if ($up_to_date) {
        echo "\nYour patches are completely up-to-date!\n\n";
    }
    echo "Untracked patches:\n";
    if ($new_patches) {
        pretty_print_array_elements($new_patches);
    } else {
        echo "\tNone found -- all patches tracked.\n\n";
    }

    echo "Patches tracked but not marked as applied:\n";
    if ($unapplied_patches) {
        pretty_print_array_elements($unapplied_patches);
    } else {
        echo "\tNone found -- all patches marked as applied.\n\n";
    }

    echo "Applied patches:\n";
    if ($applied_patches) {
        pretty_print_array_elements($applied_patches);
    } else {
        echo "\tNone found -- all patches unapplied, or none exist in the table.\n\n";
    }

    if (!$up_to_date) {
        echo "Run this script as 'php $argv[0] track' to insert untracked patches into the database (as unapplied).\n";
        echo "Run this script as 'php $argv[0] apply' to mark all tracked patches as applied.\n";
    }
    exit ("=]\n");
} elseif ($argv[1] === 'track') {
    foreach ($new_patches as $patch_name) {
        try {
            $DB->insert( 
                'patch_record',
                array (
                    'patch_name' => $patch_name,
                    'applied' => 'N',
                )
            );
        } catch (Exception $e) {
            exit ("Exception occurred when trying to insert patch $patch_name into database.\n$e");
        }
    }
    echo "The following patches have been inserted to the table (as unapplied):\n";
    pretty_print_array_elements($new_patches);
    exit ("=]\n");
} elseif ($argv[1] === 'apply') {
    print_r($unapplied_patches);
    foreach($unapplied_patches as $patch_name) {
        echo "Attempting to update $patch_name as applied.\n";
        try {
            $DB->update( 
                'patch_record',
                array (
                    'applied' => 'Y'
                ),
                array (
                    'patch_name' => $patch_name
                )
            );
        } catch (Exception $e) {
            exit ("Exception occurred when trying to update patch $patch_name into database.\n$e");
        }
    }
    echo "The following patches have been marked as applied:\n";
    pretty_print_array_elements($unapplied_patches);
    exit ("=]\n");
}

#$patches_to_apply = array();
#if ($unapplied_patches) {
#    echo "Do you want to apply the old unapplied patches? (Y/n)\t";
#    $answer = read_stdin();
#    if ($answer === 'Y') {
#        $patches_to_apply = array_merge($patches_to_apply, $unapplied_patches);
#    }
#}
#if ($new_patches) { 
#    echo "Do you want to apply the new patches? (Y/n)\t";
#    $answer = read_stdin();
#    if ($answer === 'Y') {
#        $patches_to_apply = array_merge($patches_to_apply, $new_patches);
#    } else {
#        echo "New patches will not be applied.\n";
#        exit ("Inserting new patches into database as unapplied.\n");
#    }
#}
#if ($patches_to_apply) {
#    echo "You should apply the following patches: \n";
#    print_r($patches_to_apply);
#}

/*

# Print contents of an array to a file
function write_array_to_file($filename, $array) {
    file_put_contents($filename, join("\n", array_values($array)) . "\n");
}



# Load the names of already applied patches into memory
echo "Please enter your MySQL information so that I can check which patches you've applied.\n";
$username = read_stdin();
$applied_file = 'applied.txt';
echo "Trying to read list of already-applied patches from $applied_file...\n";
if (!file_exists($applied_file)) {
    echo $applied_file , ' not found.' . "\n";
    echo "Create $applied_file now? This will assume your patches are up-to-date. (Y/n) ";
    $answer = read_stdin();
    $list_file = 'to_apply.txt';
    if ($answer === 'Y') {
        write_array_to_file($applied_file, $all_patch_files);
        echo "The names of the SQL files in this directory have been written to $applied_file.\n";
    }
    exit;
} else {
    $applied_patches = file($applied_file, FILE_IGNORE_NEW_LINES);
    echo "You've applied the following patches: \n";
    foreach ($applied_patches as $patch) {
        echo " * " . "$patch\n";
    }
}

# Calculate which patches have not been applied, and prompt user
#  to apply them. The info is read from 
$unapplied_patches = array_diff($all_patch_files, $applied_patches);
$size = sizeof($unapplied_patches);
echo "You have ($size) unapplied patches";
echo ($size? ":\n" : ".\n"); 
# If there are no unapplied patches, then all patches have been applied;
# the user is up-to-date.
if (!$size) {
    echo "You're up-to-date! :clap:\n";
    exit;
} 
# Display unapplied patches and prompt user to record this information
foreach ($unapplied_patches as $patch) {
    echo " * " . "$patch\n";
}
$list_file = 'to_apply.txt';
echo "Do you want to save this list to $list_file? (Y/n) ";
$answer = read_stdin();
if ($answer === 'Y') {
    write_array_to_file($list_file, $unapplied_patches);
    echo "The names of the unapplied patch files have been written to $list_file.\n";
    echo "You should now apply the patches in $list_file, and delete it afterward.\n";
}
echo "Do you want to update $applied_file? (Y/n) ";
$answer = read_stdin();
if ($answer === 'Y') {
    write_array_to_file($applied_file, $all_patch_files);
    echo "All patch names written to $applied_file.\n";
}
}
 */
?>
