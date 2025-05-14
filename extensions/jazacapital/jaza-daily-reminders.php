<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");



$start_date = datesub($date, 0, 0, 31);
$end_date = datesub($date, 0, 0, 1);

$arrears_array = [3,7,11,14];

$customer_ids = table_to_array('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <='$end_date'","1000000","customer_id");
$customer_list = implode(',', $customer_ids);
$customer_names = table_to_obj('o_customers',"uid in ($customer_list)","100000000","uid","full_name");



$interest_addons = $membership_addons = $processing_addons = $penalty_addons = $daily_penalty_addons = array();


$loan_l  = table_to_array('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date'","10000000","uid","uid","asc");
$loan_list = implode(',', $loan_l);

$all_addons = fetchtable('o_loan_addons',"status=1 AND loan_id in ($loan_list)","uid","asc","1000000000","loan_id, addon_id, addon_amount");
while($aa = mysqli_fetch_array($all_addons)){
    $lid = $aa['loan_id'];
    $addon_id = $aa['addon_id'];
    $addon_amount = $aa['addon_amount'];

    if($addon_id == 3 || $addon_id == 4 || $addon_id == 6){
        ////-----Interest
        $interest_addons[$lid]+= $addon_amount;
    }
    elseif ($addon_id == 1){
        ////----Membership
        $membership_addons[$lid]+= $addon_amount;
    }
    elseif ($addon_id == 2){
        ////----Processing
        $processing_addons[$lid]+= $addon_amount;
    }
    elseif ($addon_id ==  5){
        ////---All penalties
        $penalty_addons[$lid]+= $addon_amount;
    }


}


?>
<?php
$loans = fetchtable('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <='$end_date'","uid","asc","100000000","uid, customer_id, account_number, loan_amount, total_repayable_amount, total_repaid, loan_balance, total_addons, given_date, current_lo, current_co, total_addons,current_branch, status, final_due_date");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $customer_id = $l['customer_id'];
    $account_number = $l['account_number'];
    $loan_amount = $l['loan_amount'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];
    $total_repayable_amount = $l['total_repayable_amount'];
    $total_repaid = $l['total_repaid'];
    $loan_balance = $l['loan_balance'];
    $current_lo = $l['current_lo'];
    $current_co = $l['current_co'];
    $total_addons = $l['total_addons'];
    $branch = $l['current_branch'];

    $given_ago = intval(datediff3($given_date, $date));

    $name = $customer_names[$customer_id];
    $names_array = explode(' ', $name);
    $first_name = trim($names_array[0]);

    /// $interest = $interest_addons[$uid];  ///------
    $interest = $loan_amount * 0.23;

    $other_addons = $total_addons - $interest_addons[$uid];
    $principle_interest_paid = false_zero($total_repaid - $other_addons);
    $interest_principal = $loan_amount + $interest;
    $days_ago = datediff3($given_date, $date);
    $membership_fees = $membership_addons[$uid];
    $processing_fees = $processing_addons[$uid];
    $full_length = datediff3($given_date, $final_due_date);
    if($days_ago > $full_length){
        $days_ago = $full_length;
    }
    $full_length = 30;
    $daily_expected = round(($days_ago/$full_length)*$interest_principal);
   if(in_array($given_ago, $arrears_array)) {
       $deficit = false_zero($daily_expected - $principle_interest_paid);
       if ($deficit > 20) {
           ///----Has a deficit
           $arrears_num+=1;
           $mass_message = "Dear $first_name, your loan is in arrears of KES $deficit. Pay via TILL 9028009 or USSD *483 *5292# USILIPE CASH";
           //echo $mass_message.'<br/>';
           if(input_length($mass_message, 10) == 1){
               $fds =  array('phone','message_body','queued_date','source_tbl','source_record','status');
               $vals = array("$account_number","$mass_message","$fulldate","o_loans","$uid","1");
               $create = addtodb('o_sms_outgoing', $fds, $vals);
               echo $create;

              // $mass_message = $mass_message . ',("'.$account_number.'","'.$mass_message.'","'.$fulldate.'","o_loans","'.$uid.'","1")';

           }
       }
   }

}
/*
echo $mass_message.'<br/>';
$fds =  array('phone','message_body','queued_date','source_tbl','source_record','status');
$sent = addtodbmulti('o_sms_outgoing',$fds, ltrim($mass_message,','));
echo "SENT: $sent,";

echo "Arrears: $arrears_num,";
*/







?>



