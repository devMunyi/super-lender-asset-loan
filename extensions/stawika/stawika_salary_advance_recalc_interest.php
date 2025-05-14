<?php
////----When a client Limit is a certain amount, move client to a different product
session_start();
include_once ('../configs/20200902.php');

include_once("../php_functions/functions.php");
$_SESSION['db_name'] = $db_;
include_once("../configs/conn.inc");

$addon_amount = fetchrow('o_addons',"uid=7","amount");

$loans = fetchtable('o_loans',"disbursed=1 AND paid = 0 AND status!=0 AND product_id in (7,8)","uid","asc","10000","uid, loan_amount, product_id, given_date");
$loan_interests = array();
$all_loans = array(0);
while($l = mysqli_fetch_array($loans))
{
    $loan_id = $l['uid'];
    $loan_amount = $l['loan_amount'];
    $product_id = $l['product_id'];
    $given_date = $l['given_date'];
    $given_ago = datediff3($given_date, $date);
    array_push($all_loans, $loan_id);





    $mult = ceil($given_ago/30);
    if($product_id == 7){
        if($mult > 2){
            $mult = 2;
        }
    }
    if($product_id == 8){
        if($mult > 3){
            $mult = 3;
        }
    }
    if($mult < 1){
        $mult = 1;
    }


    $total_amount = (($addon_amount * $mult)/100)*$loan_amount;
    $loan_interests[$loan_id] = $total_amount;

  // echo "$loan_id $given_date $given_ago, $mult <br/>";


}

$all_Loans_string = implode(',', $all_loans);
//echo $all_Loans_string;
$all_addons = fetchtable('o_loan_addons',"status=1 AND addon_id=7 AND loan_id in ($all_Loans_string) ","uid","asc","10000000","uid, loan_id, addon_amount");
while($ad = mysqli_fetch_array($all_addons)){
    $auid = $ad['uid'];
    $loan_id = $ad['loan_id'];
    $amount = $ad['addon_amount'];

    $new_amount = $loan_interests[$loan_id];
    if($new_amount != $amount){
        echo "Update Addon for Loan $loan_id from $amount to $new_amount  <br/>";
        $update_d = updatedb('o_loan_addons',"addon_amount='$new_amount', added_date='$fulldate'","uid='$auid'");
        recalculate_loan($loan_id);
        $event = "Loan interest updated from $amount to $new_amount by cron service";
        store_event('o_loans', $loan_id,"$event");
    }
}



