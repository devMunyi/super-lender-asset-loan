<?php
// reset customer limit to 0, when the customer is dormant for last 7 days

include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$loans = fetchtable2("o_loans", "product_id != 4 AND (disbursed = 1 AND status > 0 OR status IN (1, 2))", "uid", "ASC", "uid, account_number, customer_id, final_due_date");
$active_customers = [];

while ($l = mysqli_fetch_assoc($loans)) {
    $cust_id = intval($l['customer_id']);
    $final_due_date = $l['final_due_date'];

    $date1 = new DateTime($final_due_date);
    $date2 = new DateTime($date);
    $interval = $date1->diff($date2);
    $daysDifference = $interval->days;

    if (($date2 > $date1 && $daysDifference < 7) || $date1 >= $date2) {
        $active_customers[] = $cust_id;
    }
}

$list_of_customers = implode(",", $active_customers);

$dormants = fetchtable2("o_customers", "uid NOT IN($list_of_customers) AND loan_limit > 0", "uid", "ASC");

// $dormants_arr = [];
while ($d = mysqli_fetch_assoc($dormants)) {
    $uid = $d['uid'];
    $added_date = $d['added_date'];
    $status = intval($d['status']);
    // $dormants_arr[] = $uid;

    $date_two = new DateTime($date);
    $date_one = new DateTime($added_date);
    $interval_two = $date_one->diff($date_two);
    $daysDifference_two = $interval_two->days;

    // handle leads 
    if ($date_two >= $date_one && $daysDifference_two < 7 && $status == 3) {
    } else {
        updatedb("o_customers", "loan_limit = 0", "uid = $uid AND loan_limit > 0 AND primary_product != 4");
        store_event("o_customers", $uid, "Customer loan limit reset to 0 by system at $fulldate due to dormancy of 1 week");
    }
}
