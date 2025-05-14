<?php
session_start();
include_once '../configs/20200902.php';
$_SESSION['db_name'] = 'maria_simple';
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

$limit = $_GET['limit'];

$loans_to_sync = fetchtable('o_loans',"to_sync=1","uid","asc","$limit","uid");
while($l = mysqli_fetch_array($loans_to_sync)){
    $loan_id = $l['uid'];
    ///----Update payments
    $pay = updatedb('o_incoming_payments',"to_sync=1","loan_id='$loan_id'");
    $adds = updatedb('o_loan_addons',"to_sync=1","loan_id='$loan_id'");
    $event = updatedb('o_events',"to_sync=1","tbl='o_loans' AND fld='$loan_id'");
    $upd = updatedb('o_loans',"to_sync='2'","uid='$loan_id'");
    echo "$event, $adds, $pay, $upd<br/>";

}

include_once("../configs/close_connection.inc");