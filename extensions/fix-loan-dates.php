<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$sql = "SELECT l.uid, e.fld, l.given_date, l.final_due_date, DATE(e.event_date), l.status, l.loan_balance FROM o_events e right join o_loans l ON l.uid = e.fld WHERE e.event_details LIKE '%Transaction was successful%' AND DATE(e.event_date) BETWEEN '2021-01-01' AND '2024-08-31' AND l.disbursed = 1 AND DATE(e.event_date) != l.given_date AND tbl = 'o_loans' order by e.uid DESC";

$result = mysqli_query($con, $sql);

$count = 0;
while ($row = mysqli_fetch_array($result)) {
    $loan_id = $row['uid'];
    $fld = $row['fld'];
    $given_date = $row['given_date'];
    $event_date = $row['DATE(e.event_date)'];

    if($given_date != $event_date && $fld == $loan_id){
        // make given_date the same as event_date and add 30 days
        $given_date = $event_date;
        $final_due_date = move_to_monday(date('Y-m-d', strtotime($given_date . ' + 30 days')));

        echo "Loan ID: $loan_id, Given Date: $given_date, Final Due Date: $final_due_date <br/>";

        updatedb('o_loans', "given_date='$given_date', final_due_date='$final_due_date'", "uid='$loan_id'");
        $count++;
    }
}

echo "<br>Fixed $count loan dates<br>";

// close the connection
mysqli_close($con);