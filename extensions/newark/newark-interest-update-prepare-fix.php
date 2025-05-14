<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");


$ago_15 = datesub($date, 0, 0, 20);



$loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND given_date >=  '$ago_15'","uid","asc","1000000","uid, given_date, loan_amount");
while($l = mysqli_fetch_array($loans)){

    $lid = $l['uid'];
    $given_date = $l['given_date'];
    $loan_amount = $l['loan_amount'];
    $given_ago = datediff3($given_date, $date);

    $update_l = updatedb('o_loans',"loan_flag=98","uid='$lid'");
    echo $update_l;

}