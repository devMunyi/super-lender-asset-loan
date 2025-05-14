<?php
include_once ('tenakata_sms.php');
$loan_id;
$customer_id;
$customer_name;
$customer_group_id;
$disbursed_amount;
$loan_amount;
$national_id;
$id_protected = hideMiddleDigits($national_id);
$distributor_phone;
$customer_phone;
///----Loan has been created but won't be disbursed

///--------Queue the B2B request
$group = fetchonerow('o_customer_groups',"uid='$customer_group_id'","till");
$till = $group['till'];

if($group['till'] > 1000) {
    $fds = array('loan_id', 'amount', 'added_date', 'trials', 'short_code', 'status');
    $vals = array("$loan_id", "$disbursed_amount", "$fulldate", "$till", '1', '1');
    $queue = addtodb('o_b2b_queues', $fds, $vals);
}
else{
    $event_details = "B2B transaction not scheduled because the Till number is unavailable";
    $fds = array('tbl','fld','event_details','event_date','event_by','status');
    $vals = array("o_loans", $loan_id,"$event_details","$added_date", 0, 1);
    $event_logged = addtodb('o_events',$fds,$vals);
}
///
/// -------Send Messages
$distributor_message = "Kindly Provide $customer_name ID $id_protected with stock worth $loan_amount. Thank you";
$customer_message = "Good news! Cash worth $loan_amount disbursed to distributor. Your goods will be delivered. Thank you";

$res = sendSMS($distributor_phone, $distributor_message)."<br/>";
$res = sendSMS($customer_phone, $customer_message)."<br/>";


///
/// -------Mark Loan as disbursed
$mark = updatedb('o_loans',"disbursed=1 AND status=3","uid='$loan_id'");
if($mark == 1){
    $event_details2 = "Loan marked as disbursed by system";
    $fds = array('tbl','fld','event_details','event_date','event_by','status');
    $vals = array("o_loans", $loan_id,"$event_details2","$added_date", 0, 1);
    $event_logged = addtodb('o_events',$fds,$vals);
}