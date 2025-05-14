<?php
session_start();
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

$limit = $_GET['limit'];
if($limit < 1){
    die("Enter $_GET[limit]");
}

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
$mass_addons = "";
$loans = fetchtable('o_loans',"loan_flag=20 AND uid > 0","uid","ASC","$limit","uid, loan_amount, total_addons");
while($l = mysqli_fetch_array($loans)){

    $uid = $l['uid'];
    $amount = $l['loan_amount'];
    $total_addons = $l['total_addons'];
    echo recalculate_loan($uid);
    $mass_addons = "('$uid', '6', '$total_addons', '$fulldate', '1'),".$mass_addons;
    $upd = updatedb('o_loans',"loan_flag=0","uid='$uid'");
    echo "$upd<br>";


}
