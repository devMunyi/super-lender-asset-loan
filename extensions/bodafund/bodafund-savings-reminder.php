<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$customer_savings_array = array();
$savings = fetchtable('o_incoming_payments',"status=1 AND payment_category=4","uid","asc","1000000","uid, customer_id, amount");
while($s = mysqli_fetch_array($savings)){
    $cid = $s['customer_id'];
    $amount = $s['amount'];
    $customer_savings_array = obj_add($customer_savings_array, $cid, $amount);
}

$customers = fetchtable('o_customers',"status=1","uid","asc","100000","uid, primary_mobile, date(added_date) as date_joined");
while($c = mysqli_fetch_array($customers)){
    $uid = $c['uid'];
    $primary_mobile = $c['primary_mobile'];
    $date_joined = $c['date_joined'];

    $ago_ = datediff3($date_joined, $date);

    $total_weeks = countXInY(7, $ago_);

    $required_savings = $total_weeks * 50;

    if ($ago_ % 7 === 0) {
        //////-----------Change this in the future
        $total_savings = $customer_savings_array[$uid];
        //echo
        $balance_savings = $required_savings - $total_savings;

        if($balance_savings > 0){
            echo "$uid Balance. $balance_savings, Total savings: $total_savings,ago:$ago_, $primary_mobile<br/>";
            $message = "Dear Bodafund member, please make your weekly savings of Ksh. 50 to continue enjoying our services and qualify for higher loan limits. Please pay a total of Ksh. $balance_savings. Dial *789*600# to continue";
          //  echo "$message to $primary_mobile <br/>";

            $fds = array('phone','message_body','queued_date','created_by','status');
            $vals = array($primary_mobile,"$message", "$fulldate","1","1");
            $save_ = addtodb('o_sms_outgoing', $fds, $vals);
        }
    }


}










