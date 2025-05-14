<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$ago_30 = datesub($date, 0, 0, 30);
$ago_15 = datesub($date, 0, 0, 20);



$loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND given_date BETWEEN '$ago_30' AND '$ago_15'","uid","asc","1000000","uid, given_date, loan_amount");
while($l = mysqli_fetch_array($loans)){

    $lid = $l['uid'];
    $given_date = $l['given_date'];
    $loan_amount = $l['loan_amount'];
    $given_ago = datediff3($given_date, $date);

    if($given_ago >= 26 && $given_ago <= 30) {

        $days = $given_ago - 25;
        if($days > 5){
            $days = 5;
        }
        $daily_interest = ((0.8 * $days)/100) * $loan_amount;
        $fixed_interest = ((20/100) * $loan_amount);

        $total_interest = $daily_interest + $fixed_interest;

      //  echo "$lid, $given_date, Ago: $given_ago, Days: $days, Daily_interest: $daily_interest, Fixed: $fixed_interest, Total_interest: $total_interest <br/>";

      $update_l = updatedb('o_loans',"loan_flag=99","uid='$lid'");
      echo $update_l;

    }

}