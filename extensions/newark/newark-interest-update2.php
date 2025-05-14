<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$ago_30 = datesub($date, 0, 0, 30);
$ago_15 = datesub($date, 0, 0, 20);



$loans = fetchtable('o_loans',"loan_flag=99","uid","asc","20","uid, given_date, loan_amount");
while($l = mysqli_fetch_array($loans)){

    $lid = $l['uid'];
    $given_date = $l['given_date'];
    $loan_amount = $l['loan_amount'];
    $given_ago = datediff3($given_date, $date);


            recalculate_loan($lid);

    $upd = updatedb('o_loans',"loan_flag=0","uid='$lid'");
    echo $upd;

}