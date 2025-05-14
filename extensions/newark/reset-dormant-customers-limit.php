<?php
// All dormant customer past 30 days to have zero loan limit

include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$loans = fetchtable2("o_loans", "(disbursed = 1 AND status > 0) OR status IN (1, 2)", "uid", "ASC", "customer_id, final_due_date");
$active_customers = [];

while ($l = mysqli_fetch_assoc($loans)) {
    $cust_id = intval($l['customer_id']);
    $final_due_date = $l['final_due_date'];

    $date1 = new DateTime($final_due_date);
    $date2 = new DateTime($date);
    $interval = $date1->diff($date2);
    $daysDifference = $interval->days;

    if (($date2 > $date1 && $daysDifference <= 30) || $date1 >= $date2) {
        if(!in_array($cust_id, $active_customers)){
            $active_customers[] = $cust_id;
        }
    }
}

$list_of_customers = implode(",", $active_customers);

$dormants = fetchtable2("o_customers", "uid NOT IN($list_of_customers) AND loan_limit > 0 AND status NOT IN(0, 3) AND total_loans > 0", "uid", "ASC", "uid, added_date, status");

// $dormants_arr = [];
$counter = 0;
while ($d = mysqli_fetch_assoc($dormants)) {
    $uid = $d['uid'];
    $added_date = $d['added_date'];
    $status = intval($d['status']);
    // $dormants_arr[] = $uid;

    $date_two = new DateTime($date);
    $date_one = new DateTime($added_date);
    $interval_two = $date_one->diff($date_two);
    $daysDifference_two = $interval_two->days;

    echo "Resetting loan limit for customer: $uid, status: $status<br/>";
    updatedb("o_customers", "loan_limit = 0", "uid = $uid AND loan_limit > 0");
    store_event("o_customers", $uid, "Customer loan limit reset to 0 by system at $fulldate due to dormancy past 30 days");

    $counter++;
}


echo "Total customers reset: $counter<br/>";
