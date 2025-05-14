<?php
session_start();
require_once("../configs/conn.inc");
require_once("../php_functions/functions.php");

$half_limit_after = 7;
$limit_removed_after = 15;


$month_ago_3 = datesub($date, 0, 0, 180);


$loan_statuses = table_to_obj('o_loan_statuses',"uid > 0","100","uid","name");
$customers_array = array();
$to_half_array = array();
$to_remove_array = array();

$loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND status !=0 AND final_due_date >= '$month_ago_3' AND final_due_date <= '$date'","uid","desc","0,10000","uid, customer_id, given_date, final_due_date, loan_amount, loan_balance, account_number, status");
while($l = mysqli_fetch_array($loans)) {
    $uid = $l['uid'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];
    $final_due_date = $l['final_due_date'];
    $loan_amount = $l['loan_amount'];
    $loan_balance = $l['loan_balance'];
    $customer_id = $l['customer_id'];
    $account_number = $l['account_number'];


    $loan_status = $l['status'];

    $days_passed = datediff($date, $final_due_date);
    if($days_passed < 0){
        $days_passed_p = $days_passed * -1;
        if($days_passed_p == $half_limit_after){
            array_push($customers_array, $customer_id);
            ////half
            $event = "Limit halved automatically by system on [$fulldate]";
            // echo "Half for $account_number $customer_id<br/>";
            array_push($to_half_array, $customer_id);
            //        store_event('o_loans', decurl($loan_id),"$event");
        }
        if($days_passed_p == $limit_removed_after){
            array_push($customers_array, $customer_id);
            $event = "Limit removed automatically by system on [$fulldate]";
            // echo "Remove for $account_number $customer_id<br/>";
            array_push($to_remove_array, $customer_id);
            ///Remove
        }
    }

    //  echo "$uid Given Date[$given_date] Final Due Date [$final_due_date] Balance[$loan_balance]  Days[$days_passed] Status [$loan_status] <br/>";

}

echo "<hr/>";
//var_dump($to_half_array);
$customers_string = implode(',', $customers_array);

$customers = fetchtable('o_customers',"uid in ($customers_string)","uid","asc","1000000","uid, loan_limit, primary_mobile");
while($c = mysqli_fetch_array($customers)){
    $cus_id = $c['uid'];
    $loan_limit = $c['loan_limit'];
    $primary_mobile = $c['primary_mobile'];
    $new_limit = $loan_limit;
    if($loan_limit > 0) {
        // echo "$primary_mobile $loan_limit <br/>";
        $event = "Updated";
        if (in_array($cus_id, $to_half_array)) {
            $new_limit = round($loan_limit / 2);
            // echo "Half ($primary_mobile) $cus_id from $loan_limit to  $new_limit<br/>";
            $event = "Halved";

        }
        if (in_array($cus_id, $to_remove_array)) {
            $new_limit = 0;
            //  echo "Remove ($primary_mobile) $cus_id from $loan_limit to  $new_limit<br/>";
            $event = "Zeroed";
        }

        $fds = array('customer_uid','amount','given_date','given_by','comments','status');
        $vals = array("$cus_id","$new_limit","$fulldate","0","$event automatically by system","1");
        $create = addtodb('o_customer_limits', $fds, $vals);
        if($create == 1){
            $update_cust = updatedb('o_customers',"loan_limit='$new_limit'","uid='$cus_id'");
            // echo "Sucess($update_cust)";
            store_event('o_customers', $cus_id,"Limit $event from $loan_limit to  $new_limit by system on $fulldate");
        }
        else{
            echo "Missed($cus_id)";
            echo "$update_cust";
        }

    }
}





include_once("../configs/close_connection.inc");