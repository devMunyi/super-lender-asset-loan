<?php
session_start();

$_SESSION['db_name'] = 'stawika_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

echo b2b(830685, 836360, 10000);
/*
$loans = fetchtable('o_loans',"product_id in (10, 11, 6, 12) AND disbursed=1 AND paid = 0","uid","asc","10000","uid, given_date, final_due_date");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];

    $period = datediff3($given_date, $final_due_date);

    $total_instalments = total_instalments($period, 1, 7);

//die(errormes($total_instalments));

  echo "$uid $given_date $final_due_date $period, $total_instalments <br/>";
    $update_loan = updatedb('o_loans',"total_instalments='$total_instalments'","uid=$uid");


}
*/