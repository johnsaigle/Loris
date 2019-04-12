<?php

require_once 'generic_includes.php';


$query = "SELECT c.DoB,mpf.Date_taken
    FROM candidate c
    INNER JOIN session s on s.CandID = c.CandID
    INNER JOIN flag f on s.ID = f.SessionID
    INNER JOIN mri_parameter_form mpf on mpf.CommentID = f.CommentID
    WHERE f.test_name='mri_parameter_form'";
$result = \Database::singleton()->pselect($query, array());
print_r($result);
