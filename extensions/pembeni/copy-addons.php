<?php
session_start();
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

ini_set('display_errors', 0); ini_set('display_startup_errors', 0); error_reporting(E_ALL);
$mass_addons = "";
$loans = fetchtable('o_loans',"loan_flag=19 AND uid > 0","uid","ASC","10000","uid, loan_amount, total_addons");
while($l = mysqli_fetch_array($loans)){

    $uid = $l['uid'];
    $amount = $l['loan_amount'];
    $total_addons = $l['total_addons'];

    $mass_addons = "('$uid', '5', '$total_addons', '$fulldate', '1'),".$mass_addons;
    $upd = updatedb('o_loans',"loan_flag=0","uid='$uid'");

    echo "$upd";

}
echo "$upd <br/>";
//echo $mass_addons;

$jflds = array('loan_id', 'addon_id', 'addon_amount', 'added_date', 'status');
$mass3 = addtodbmulti('o_loan_addons', $jflds, rtrim($mass_addons, ","));
//echo $mass3.'<br/>';