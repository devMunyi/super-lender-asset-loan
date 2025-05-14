<?php

include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$yesterday = datesub($date, 0, 0, 1);
$three_weeks_ago = datesub($date, 0, 0, 21);

/////// =====> Begin Zero limiting Case of DD+1
$yestereday_query = "SELECT c.uid as customer_id FROM o_loans l LEFT JOIN o_customers c ON c.uid = l.customer_id WHERE l.status != 0 AND l.final_due_date <= '$yesterday' AND l.disbursed = 1 AND l.paid = 0 AND c.loan_limit > 0";

$yestereday_result = mysqli_query($con, $yestereday_query);

$all_customers = [];
while($row = mysqli_fetch_assoc($yestereday_result)) {
    $all_customers[] = $row['customer_id'];
}

$all_customers_string = implode(',', $all_customers);
updatedb('o_customers', "loan_limit=0", "uid in ($all_customers_string)");
/// ===> End Zero limiting Case of DD+1


/// ===> Begin Zero limiting Case of no partial payments three weeks ago since loan was given
$three_weeks_ago_query = "SELECT c.uid as customer_id, c.loan_limit, l.given_date, l.status FROM o_loans l LEFT JOIN o_customers c ON c.uid = l.customer_id WHERE l.status != 0 AND l.given_date <= '$three_weeks_ago' AND l.disbursed = 1 AND l.paid = 0 AND c.loan_limit > 0 AND l.total_repaid = 0";

$three_weeks_ago_result = mysqli_query($con, $three_weeks_ago_query);

$all_customers = [];
while($row = mysqli_fetch_assoc($three_weeks_ago_result)) {
    $all_customers[] = $row['customer_id'];
}

$all_customers_string = implode(',', $all_customers);
updatedb('o_customers', "loan_limit=0", "uid in ($all_customers_string)");
/// ===> End Zero limiting Case of no partial payments three weeks ago since loan was given



