<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

//echo $db_;
//$start_date = $_GET['start_date'];
//$end_date = $_GET['end_date'];

$start_date = datesub($date, 0,0,1);
$end_date = $date;

///---Check all payments coming in,
$pay_loans = table_to_obj('o_incoming_payments',"payment_date BETWEEN '$start_date' AND '$end_date' AND group_id=0 AND status=1","100000","uid","loan_id");
//----Check the loans if they belong to a specific group

if(sizeof($pay_loans) > 0) {
    $pay_loans_list = implode(",", array_values($pay_loans));
   // echo $pay_loans_list;
    /////-------Payments without groups
    $loans_list = fetchtable('o_loans', "uid in ($pay_loans_list) disbursed=1 AND product_id in (2,6) AND group_id > 0","uid","asc","1000000","group_id, uid");
    while($l = mysqli_fetch_array($loans_list)){
        $gid = $l['group_id'];
        $lid = $l['uid'];
        $pid = array_search($lid, $pay_loans);

         $update_p = updatedb('o_incoming_payments',"group_id='$gid'","group_id=0 AND uid='$pid'");
         echo "Pay $update_p, Update: $update_p <br/>";

    }

}

//die();

///----Check all group loans given in that period
$customer_list_array = table_to_obj('o_loans',"disbursed=1 AND  status!=0 AND given_date BETWEEN '$start_date' AND '$end_date' AND group_id=0 AND product_id in (2,6)","100000","uid","customer_id");

$customer_list = array_values($customer_list_array);

//var_dump($customer_list);
if(sizeof($customer_list) > 0) {
    $customer_list_ = implode(",", $customer_list);
    ///-----Check the customer groups
    $customer_groups_array = table_to_obj('o_group_members', "status=1 AND customer_id in ($customer_list_)", "10000000", "customer_id", "group_id");
    ////----We have customer group


    foreach ($customer_groups_array as $customer_id => $group_id){
        $loan_id = array_search($customer_id, $customer_list_array);
        /////-----Update the loan with group id, and payments
        if($loan_id > 0){
            //////We have found a loan to update
            $update_l = updatedb('o_loans',"group_id='$group_id'","uid='$loan_id' AND group_id=0");
            $update_p = updatedb('o_incoming_payments',"group_id='$group_id'","group_id=0 AND loan_id='$loan_id' AND status=1");
            //////Also update the associated payments
            echo "Loan $loan_id, Update($update_l) <br/>";
            echo "Payments , Update($update_p) <br/>";
        }

    }




}

//echo "$update_l Loans Updated, Payments Updated";



?>