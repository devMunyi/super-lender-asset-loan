<?php
session_start();
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

$offset = $_GET['offset'];
$rpp = $_GET['rpp'];
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
// Open the CSV file

$loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND given_date BETWEEN '$start_date' AND '$end_date'","uid","asc","$offset,$rpp");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $status = $l['status'];
    $status_name = $loan_statuses[$status];
    $loan_amount = $l['loan_amount'];
      $interest_rate = $loan_amount * 0.22;
      $original = fetchrow('o_loan_addons',"status=1 AND addon_id=5 AND loan_id=$uid AND status=1","addon_amount");
    ////----Update addon
    $upd = updatedb('o_loan_addons',"addon_amount='$interest_rate'","status=1 AND addon_id=5 AND loan_id=$uid AND status=1");
    if($upd == 1){
        recalculate_loan($uid);
        store_event('o_loans', $uid, "Block addon fixed to $interest_rate, original value: $original");
        echo "Done $uid, $status_name<br/>";

    }
    ///------Recalc loan

    //echo "$uid , $status_name<br/>";

}