<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

//echo $db_;
$yesterday = datesub($date, 0, 0, 1);
$sixty_ago = datesub($date, 0, 0, 60);

echo $yesterday.$sixty_ago;
$total = 0;
$added_ = 0;
$skipped = 0;
$loans = fetchtable('o_loans',"uid in (9547,
9730,
9946,
9719,
9781,
9699,
9934,
9939,
9931,
9941,
9660,
9918,
9622,
9924,
9817,
9605,
9823,
9895,
9888,
9879,
9837,
9566,
9928,
9938,
9932,
9937,
9912,
9904,
9908,
9911,
9849,
9901,
9891,
9873,
9830,
9809,
9745,
9856,
9818,
9794,
9811,
9783,
9787,
9785,
9780,
9760,
9747,
9735,
9731,
9701)","uid","asc","100000","uid, final_due_date, loan_amount, total_repaid");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $final_due_date = $l['final_due_date'];
    $loan_amount = $l['loan_amount'];
    $total_repaid = $l['total_repaid'];
    //---------------This is an inefficient way, fix it in the future by removing the function outside
    $remove = remove_addon(10, $uid, 1);


   echo "$remove<br/>";

}


?>