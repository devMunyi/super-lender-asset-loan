<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$ago_30 = datesub($date, 0, 0, 30);
$ago_25 = datesub($date, 0, 0, 25);


$fixed_addon = fetchrow('o_addons',"uid=16","amount");
$loans = fetchtable('o_loans',"paid=0 AND status in (3,7,8) AND disbursed=1 AND given_date >= '$ago_30'  AND  given_date >= '2024-06-01'","uid","asc","10000","uid, given_date, loan_amount");
while($l = mysqli_fetch_array($loans)){

    $lid = $l['uid'];
    $given_date = $l['given_date'];
    $loan_amount = $l['loan_amount'];
    $given_ago = datediff3($given_date, $date);

    //echo "$given_ago: $lid,";

    if($given_ago >= 11 && $given_ago <= 30) {

        $days = $given_ago - 10;
        if($days > 20){
            $days = 20;
        }
        $daily_interest = (($days)/100) * $loan_amount;
        $fixed_interest = (($fixed_addon/100) * $loan_amount);

        $total_interest = $daily_interest + $fixed_interest;

       //  echo "$lid, $given_date, Ago: $given_ago, Days: $days,Loan Amount: $loan_amount, Daily_interest: $daily_interest, Fixed: $fixed_interest, Total_interest: $total_interest <br/>";

        ///----Update Interest
        $update_db = updatedb('o_loan_addons', "addon_amount='$total_interest', added_date='$fulldate'","loan_id='$lid' AND addon_id=16 AND status=1");
        echo $update_db;
        if($update_db == 1){
            recalculate_loan($lid);
        }

    }
  //  $upd = updatedb('o_loans',"loan_flag=0","uid='$lid'");

}