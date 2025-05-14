<?php 

include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

$hours_to_subtract = 24; // Change this value as needed
$past_datetime = new DateTime($fulldate); // Get the current datetime
$past_datetime->modify("-{$hours_to_subtract} hours");
$past_datetime = $past_datetime->format('Y-m-d H:i:s');

$loan_query = "SELECT uid FROM o_loans WHERE added_date <= '$past_datetime' AND status = 1";

$loans = mysqli_query($con, $loan_query);

$loanUids = [];
while($row = mysqli_fetch_assoc($loans)) {
    $loanUids[] = $row['uid'];
}

if(empty($loanUids)) {
    echo "No loans found.";
    exit;
}

// implode them to a string
$loan_list = implode(",", $loanUids);


// update them to status = 6 (rejected)
updatedb("o_loans", "status = 6, total_repaid = 0, loan_balance = 0, total_repayable_amount = 0", "uid IN ($loan_list)");


// update payments attached to them by setting loan_id = 0
updatedb("o_incoming_payments", "loan_id = 0", "loan_id IN ($loan_list) AND status = 1");


//=== store event details in o_events in Bulk
$event_details = "The loan was rejected by the system because it had been in created status for 24 hours.";
$event_values = [];
foreach($loanUids as $loanId) {
    $event_values[] = "('o_loans', $loanId, '$event_details', '$fulldate', 0, 1)";
}

$event_values = implode(",", $event_values);
$bulk_insert_query = "INSERT INTO o_events (tbl, fld, event_details, event_date, event_by, status) VALUES $event_values";

mysqli_query($con, $bulk_insert_query);

/// === End of storing event details in o_events in Bulk


echo "Loan(s) with uid(s) $loan_list have been rejected.";

