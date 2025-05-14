<?php
session_start();

$_SESSION['db_name'] = 'stawika_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND product_id in ()","given_date","asc","0, 500000","uid, given_date,  final_due_date");
while($l = mysqli_fetch_array($loans)){
    $loan_id = $l['uid'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];

    $total_days = datediff3($given_date, $final_due_date);

    echo "$loan_id, $given_date, $final_due_date, $total_days<br/>";



}
