<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$ago_30 = datesub($date, 0, 0, 30);
$ago_15 = datesub($date, 0, 0, 20);



$loans = fetchtable('o_loans',"loan_flag=99 AND paid=0","uid","asc","20","uid, given_date, loan_amount");
while($l = mysqli_fetch_array($loans)){

    $lid = $l['uid'];
    $given_date = $l['given_date'];
    $loan_amount = $l['loan_amount'];
    $given_ago = datediff3($given_date, $date);

    echo "$given_ago: $lid,";

    if($given_ago >= 26 && $given_ago <= 45) {

        $days = $given_ago - 25;
        if($days > 5){
            $days = 5;
        }
        $daily_interest = ((0.8 * $days)/100) * $loan_amount;
        $fixed_interest = ((20/100) * $loan_amount);

        $total_interest = $daily_interest + $fixed_interest;

        //  echo "$lid, $given_date, Ago: $given_ago, Days: $days, Daily_interest: $daily_interest, Fixed: $fixed_interest, Total_interest: $total_interest <br/>";

        ///----Update Interest
        $update_db = updatedb('o_loan_addons', "addon_amount='$total_interest', added_date='$fulldate'","loan_id='$lid' AND addon_id=1 AND status=1");
        echo $update_db;
        if($update_db == 1){
            recalculate_loan($lid);
        }

    }
    $upd = updatedb('o_loans',"loan_flag=0","uid='$lid'");

}