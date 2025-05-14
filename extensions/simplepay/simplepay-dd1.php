<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//set_time_limit(1200);
/*
 * This service is called after the daily checker products to add or update addons
 */

include_once '../configs/20200902.php';
include_once("../php_functions/functions.php");


$db = $db_;
$_SESSION['db_name'] = $db;
include_once("../configs/conn.inc");
$yesterday = datesub($date, 0, 0, 1);
$week_ago = datesub($date, 0, 0, 7);
$mark = updatedb('o_loans', "status='7'", "paid=0 AND disbursed=1 AND final_due_date = '$yesterday' AND status=3 AND status!=0");

$all_loans = table_to_array('o_loans', "status!=0 AND final_due_date = '$yesterday' AND disbursed=1 AND paid = 0", "10000000", "uid");

$all_loans_string = implode(',', $all_loans);

$all_dd1_addons = table_to_array('o_loan_addons', "addon_id=4 AND status=1 AND loan_id in ($all_loans_string)", "100000000", "loan_id");

$already_added_string = implode(',', $all_dd1_addons);
$addon_d = fetchrow('o_addons', "uid=4", "amount");


// echo $already_added_string;
if (input_available($already_added_string) == 0) {
    $already_added_string = 0;
}
// die();


$loan_total_days_array = array();
$addon_add = 0;
$loans = fetchtable('o_loans', "uid in ($all_loans_string) AND uid not in ($already_added_string)", "uid", "asc", "100000000", "uid, given_date, final_due_date, loan_balance");
while ($l = mysqli_fetch_array($loans)) {
    $uid = $l['uid'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_date'];
    $balance = $l['loan_balance'];

    //echo "$uid,";
    $days_ago = datediff3($given_date, $date);        ///////---------5
    if ($balance > 0) {
        $interest = (5 / 100) * $balance;
        $addon = addon_with_amount(4, $uid, $interest, 0);
        recalculate_loan($uid);
        $addon = $addon + 1;
    }
}


echo "$addon_add Addons Created \n";
             //   echo "$total_updated/$total_to_update Addons Updated \n";



  //  $update=updatedb('o_last_service',"last_date='$fulldate'","uid=1");
