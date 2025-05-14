<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$ago_30 = datesub($date, 0, 0, 30);
$ago_15 = datesub($date, 0, 0, 20);



$loans = fetchtable('o_loans',"loan_flag=98","uid","asc","20","uid, given_date, loan_amount");
while($l = mysqli_fetch_array($loans)){

    $lid = $l['uid'];
    $given_date = $l['given_date'];
    $loan_amount = $l['loan_amount'];



      $total_interest = (0.2 * $loan_amount);
      //  echo "$lid, $given_date, Ago: $given_ago, Days: $days, Daily_interest: $daily_interest, Fixed: $fixed_interest, Total_interest: $total_interest <br/>";

        ///----Update Interest
        $update_db = updatedb('o_loan_addons', "addon_amount='$total_interest', added_date='$fulldate'","loan_id='$lid' AND addon_id=1 AND status=1");
        echo $update_db."($lid)";
        if($update_db == 1){
            recalculate_loan($lid);
        }


    $upd = updatedb('o_loans',"loan_flag=0","uid='$lid'");

}