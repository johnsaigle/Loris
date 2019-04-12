<?php declare(strict_types=1);

require_once 'generic_includes.php';


$query = "SELECT c.CandID,c.DoB,mpf.Date_taken
    FROM candidate c
    INNER JOIN session s on s.CandID = c.CandID
    INNER JOIN flag f on s.ID = f.SessionID
    INNER JOIN mri_parameter_form mpf on mpf.CommentID = f.CommentID
    WHERE f.test_name='mri_parameter_form'";
$result = \Database::singleton()->pselect($query, array());

$template = "UPDATE candidate SET Age_At_MRI = '%s' WHERE CandID ='%s';";

$UPDATEQueue = array();
$skipped = 0;

foreach ($result as $row) {
    if (empty($row['Date_taken'] || empty($row['CandID']))) {
        $skipped++;
        continue;
    }
    $age = \Utility::calculateAge($row['DoB'], $row['Date_taken'])['year'];
    $UPDATEQueue[] = sprintf($template, $age, $row['CandID']);
}

echo implode("\n", $UPDATEQueue);

$skippedMessage = "\nSkipped $skipped candidates due to missing DoB in candidate table " .
    "or missing Date_taken in mri_parameter_form table.\n";

fwrite(STDERR, $skippedMessage);

