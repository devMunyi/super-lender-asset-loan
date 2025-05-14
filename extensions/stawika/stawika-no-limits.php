<?php
session_start();

$_SESSION['db_name'] = 'stawika_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$all_defaulters = table_to_array('o_loans',"disbursed=1 AND paid=0 AND status!=0 AND given_date >= '2022-01-01'","10000000000","customer_id");
$all_def = implode(',', $all_defaulters);

$customer_loans = array();
$total_loans_per_customer = fetchtable('o_loans',"disbursed=1 AND status!=0 AND given_date >= '2022-01-01'","uid","asc","1000000","customer_id");
while($t = mysqli_fetch_array($total_loans_per_customer)){
    $customer = $t['customer_id'];
    $customer_loans = obj_add($customer_loans, $customer, 1);

}
var_dump($customer_loans);
die();

echo "<table>";
echo "<tr><th>uid</th><th>Full name</th><th>Primary_mobile</th><th> email_address</th> <th>national id</th><th>Added Date</th><th>Total Loans</th></tr>";
$all_without_limit = fetchtable('o_customers',"uid not in ($all_def) AND status=1 AND loan_limit = 0 AND added_date >= '2022-01-01 00:00:00'","uid","asc","1000000","uid, full_name, primary_mobile, email_address, national_id, added_date");
while($a = mysqli_fetch_array($all_without_limit)){
    $uid = $a['uid'];
    $full_name = $a['full_name'];
    $primary_mobile = $a['primary_mobile'];
    $email_address = $a['email_address'];
    $national_id = $a['national_id'];
    $added_date = $a['added_date'];
    $total_loans = $customer_loans[$uid];
    echo "<tr><td>$uid</td><td>$full_name</td><td>$primary_mobile</td><td>$email_address</td> <td>$national_id</td><td>$added_date</td><td>$total_loans</td></tr>";

}

echo "</table>";