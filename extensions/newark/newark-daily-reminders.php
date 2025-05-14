<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

/*
 * We are setting the daily reminders here because they are too complex and custom to set on the UI
 *
 */

$ago_start = datesub($date, 0, 0, 60);
$ago_end = datesub($date, 0, 0, 1);

$mass_message = "";

$arrears_array = [2,5,10,15];
$dues_array = [20,22,24,26,30,30];
$overdues_array = [31,35,40,45,55,60];

$all_loans_array = array();
$customer_list_array = array();

$q = "disbursed=1 AND paid=0 AND status!=0 AND given_date BETWEEN '$ago_start' AND '$ago_end'";

$loans = fetchtable('o_loans',"$q","uid","asc","100000","uid, customer_id");
while($l = mysqli_fetch_array($loans)){

    $lid = $l['uid'];
    $customer_id = $l['customer_id'];
   // array_push($all_loans_array,$lid);
    array_push($customer_list_array,$customer_id);
}

$customer_list = implode(',',$customer_list_array);
$customer_names = table_to_obj('o_customers',"uid in ($customer_list)","1000000","uid","full_name");

//$loan_list = explode(",", $all_loans_array);

$loans = fetchtable('o_loans',"$q","uid","asc","10000","uid, given_date, loan_amount, final_due_date, customer_id, total_repayable_amount, account_number, total_repaid, loan_balance");
while($l = mysqli_fetch_array($loans)){

    $lid = $l['uid'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];
    $loan_amount = $l['loan_amount'];
    $loan_balance = $l['loan_balance'];
    $total_repayable_amount = $l['total_repayable_amount'];
    $total_repaid = $l['total_repaid'];
    $account_number = $l['account_number'];
    $due_ago = intval(datediff3($final_due_date, $date));
    $given_ago = intval(datediff3($given_date, $date));
    $cid = $l['customer_id'];
    $customer_name = $customer_names[$cid];
    $message = "";
     if(in_array($given_ago, $arrears_array)){
         $current_instalment = ($total_repayable_amount/30)*$given_ago;
         if($current_instalment > $total_repaid){
             ////------Arrears message
            // echo "Arrears Message<br/>";
             $arrears+=1;
             $current_instalment_balance = $current_instalment - $total_repaid;
             $message = "Malipo yako ya kila siku yamechelewa na $current_instalment_balance. lipa SAHII ili upate nafasi ya kuongezewa limit yako utakapomaliza. TILL NO. 640134.";
         }

     }
    if(in_array($given_ago, $dues_array)){
         ////-----Dues message
        $dues+=1;
        $message = "Your loan is DUE Current balance is KES $loan_balance. TILL NO: 640134. Get an INSTANT REPEATER LOAN by texting LOAN AMOUNT to 21045. DO NOT PAY CASH";
     }
    if(in_array($given_ago, $overdues_array)){
        ////-----Overdues Message
        $overdues+=1;
        $message = "Your NEWARK loan is in DEFAULT with KSH $loan_balance. Pay NOW to avoid recovery consequences and get your record clean and increase chances of getting another loan. TILL NO: 640134.  Visit your nearest branch. DO NOT PAY CASH";
    }

   // echo "$message";
    if(input_length($message, 10) == 1){

        $mass_message = $mass_message . ',("'.$account_number.'","'.$message.'","'.$fulldate.'","o_loans","'.$lid.'","1")';

    }

       ///Overdue

}
$fds =  array('phone','message_body','queued_date','source_tbl','source_record','status');
$sent = addtodbmulti('o_sms_outgoing',$fds, ltrim($mass_message,','));

echo "Arrears: $arrears, Due: $dues, Overdue: $overdues, Sent: $sent";