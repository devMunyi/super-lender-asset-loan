<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$pay_ids = array();
$pays = fetchtable('o_events',"event_details LIKE '%Payment fixed and allocated to loan by system manual process%'","uid","asc","200","fld");
while($p = mysqli_fetch_array($pays)){

    $pay_id = $p['fld'];
    array_push($pay_ids, $pay_id);
}

$pay_list = implode(',', $pay_ids);

$payments = fetchtable('o_incoming_payments',"uid in ($pay_list)","uid","asc","200","uid, loan_id");
while($p = mysqli_fetch_array($payments)){
    $pid = $p['uid'];
    $lid = $p['loan_id'];
    updatedb('o_incoming_payments',"loan_id='0'","uid='$pid'");
    recalculate_loan($lid, true);
    $update = updatedb('o_loans',"paid=0 AND status=3","paid=1 AND loan_balance > 4 AND uid = '$lid'");
    echo "$pid,$lid, $update <br/>";
}