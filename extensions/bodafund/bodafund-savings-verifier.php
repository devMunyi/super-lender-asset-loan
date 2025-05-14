<?php


///-------Check if client has saved since he was signed up
$customer_join_date = fetchrow('o_customers',"uid=$customer_id","date(added_date)");
$ago_ = datediff3($customer_join_date, $date);

$total_weeks = countXInY(7, $ago_);

$required_savings = $total_weeks * 50;

$total_savings = totaltable('o_incoming_payments',"customer_id = ".$customer_id." AND status=1 AND payment_category=4","amount");

$balance_savings = $required_savings - $total_savings;
if($balance_savings > 0){
    die(errormes("You need a weekly savings of Ksh. 50 to borrow. Please save Ksh. $balance_savings"));
}