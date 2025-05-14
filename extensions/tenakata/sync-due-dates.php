<?php

session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$dues = fetchtable('o_loans', "uid > 0 AND disbursed = 1", "uid", "DESC", "1000000", "uid, given_date, final_due_date");

// echo mysqli_num_rows($dues);
$updated = $skipped = 0;
while ($due = mysqli_fetch_assoc($dues)) {
    $due_id = intval($due['uid']);
    $given_date = $due['given_date'];
    $expected_final_due_date = addDaysToDate($given_date, 30);
    $final_due_date = $due['final_due_date'];


    if ($final_due_date !== $expected_final_due_date && $given_date !== '0000-00-00') {
        $sql = "UPDATE o_loans SET final_due_date = '$expected_final_due_date' WHERE uid = $due_id";
        $result = mysqli_query($con, $sql);
        if ($result) {
            echo "Pay ID => $due_id, FINAL DUE => $final_due_date, Expected due date => $expected_final_due_date <br>";
            $updated += 1;
        } else {
            echo "Pay ID => $due_id, SKIPPED ON SQL EXECUTION, FINAL DUE => $final_due_date, Expected due date => $expected_final_due_date <br>";
            $skipped += 1;
        }
    } else {
        echo "Pay ID => $due_id, SKIPPED FINAL DUE => $final_due_date, Expected due date => $expected_final_due_date <br>";
        $skipped += 1;
    }
}

echo "UPDATED $updated <br>";
echo "SKIPPED $skipped <br>";

/*

Pay ID => 165901, FINAL DUE => 2023-10-14, Expected due date => 2023-10-16
Pay ID => 165900, FINAL DUE => 2023-10-14, Expected due date => 2023-10-16


*/
