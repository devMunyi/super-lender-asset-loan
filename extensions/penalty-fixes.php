<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("../configs/20200902.php");
$db = $db_;
//include_once(".configs/auth.inc");
include_once("../php_functions/functions.php");

$_SESSION['db_name'] = $db;
include_once("../configs/conn.inc");

$offset = $_GET['offset'];
$rpp = $_GET['rpp'];
$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];

if(!isset($_GET['offset'])){
   die("Offset not set");
}
if(!isset($_GET['rpp'])){
    die("RPP not set");
}
if(!isset($_GET['start_date'])){
    die("Start date not set");
}
if(!isset($_GET['end_date'])){
    die("End date not set");
}



$loans = fetchtable('o_loans',"given_date BETWEEN '$start_date' AND '$end_date' AND disbursed=1","uid","asc","$offset, $rpp","uid, total_addons, loan_amount, total_repayable_amount, given_date");

while($l = mysqli_fetch_array($loans)) {
    $loan_id = $l['uid'];
    $total_addons = $l['total_addons'];
    $loan_amount = $l['loan_amount'];

    $given_date = $l['given_date'];


    $penalty_addons_total = loan_penalty_addons($loan_id);
    if($penalty_addons_total > 0){
        ////--- store it ask JSON
        //  $sec = array("INTEREST_AMOUNT"=>$interest_addons_total);
        $andsec = "other_info = JSON_SET(
                IFNULL(other_info, '{}'),
                '$.PENALTY_AMOUNT', '$penalty_addons_total')";

       $update = updatedb('o_loans', "$andsec", "uid='$loan_id'");
        echo "Update $update, $loan_id, $given_date <br/>";

    }
}




