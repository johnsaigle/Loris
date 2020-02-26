<?php declare(strict_types=1);
/**
 * This tool verifies that the data contained in the LORIS database is well-formed
 * and contains no major issues. For example, it checks that tables referenced
 * in one part of the database exist elsewhere.
 */
require_once __DIR__ . "/generic_includes.php";

$instrument_arr = $DB->pselectCol("SELECT Test_name FROM test_names", []);
$check_arr      = [];

// Verifies that instruments registered in the test_names table have
// corresponding per-instrument tables containing the data.
echo "\n== Checking if instrument tables exist ==\n";
foreach ($instrument_arr as $instrument) {
    $name   = $instrument["Test_name"];
    $exists = $DB->tableExists($name);
    echo ($exists ?
        "(true)  - " :
        "(false) - "
    );
    echo "{$name}\n";

    if ($exists) {
        $check_arr[] = $name;
    }
}

echo "\n== Checking for orphaned instrument/flag ==\n";
foreach ($check_arr as $check) {
    echo "Checking {$check}...\n";

    $orphan_flag_arr = $DB->pselect(
        "
        SELECT
            flag.*
        FROM
            flag
        LEFT JOIN
            {$check}
        ON
            {$check}.CommentID = flag.CommentID
        WHERE
            Test_name = :check AND
            {$check}.CommentID IS NULL
    ",
        array(
            "check" => $check
        )
    );

    $flag_str = pad_or_trunc($check, 5) . " flag";
    foreach ($orphan_flag_arr as $orphan_flag) {
        $comment_id = $orphan_flag["CommentID"];
        echo "Warning, orphan {$flag_str}:{$comment_id}\n";
    }
    $orphan_instrument_arr = $DB->pselect(
        "
        SELECT
            {$check}.*
        FROM
            {$check}
        LEFT JOIN
            flag
        ON
            {$check}.CommentID = flag.CommentID
        WHERE
            flag.CommentID IS NULL
    ",
        array()
    );

    $check_str = pad_or_trunc($check, 10);
    foreach ($orphan_instrument_arr as $orphan_instrument) {
        $comment_id = $orphan_instrument["CommentID"];
        echo "Warning, orphan {$check_str}:{$comment_id}\n";
    }

    echo "Finished checking {$check}...\n\n";
}

echo "\n== Checking for duplicate flags in a session ==\n";
$duplicate_flag_arr = $DB->pselect(
    "
    SELECT
        *
    FROM
        flag
    WHERE
        (
            flag.CommentID NOT LIKE 'DDE_%' AND
            (
                SELECT
                    COUNT(*)
                FROM
                    flag test
                WHERE
                    test.CommentID NOT LIKE 'DDE_%' AND
                    flag.SessionID = test.SessionID AND
                    flag.Test_name = test.Test_name
            ) > 1
        ) OR
        (
            flag.CommentID LIKE 'DDE_%' AND
            (
                SELECT
                    COUNT(*)
                FROM
                    flag test
                WHERE
                    test.CommentID LIKE 'DDE_%' AND
                    flag.SessionID = test.SessionID AND
                    flag.Test_name = test.Test_name
            ) > 1
        )
",
    array()
);
foreach ($duplicate_flag_arr as $duplicate_flag) {
    $session_id = $duplicate_flag["SessionID"];
    $test_name  = $duplicate_flag["Test_name"];
    $comment_id = $duplicate_flag["CommentID"];
    echo "Warning:{$session_id} {$test_name} {$comment_id}\n";
}

echo "\n== Finished checks ==\n";
