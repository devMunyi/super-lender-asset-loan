<?php
session_start();
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
//set_time_limit(1200);
/*
 * This service is called after the daily checker products to add or update addons
 
 Error Code: 1054. Unknown column 'e.event_details' in 'where clause'

 
 */

include_once("../configs/auth.inc");
include_once '../configs/20200902.php';

include_once("../php_functions/functions.php");


$db = $db_;
$_SESSION['db_name'] = $db;
include_once("../configs/conn.inc");

$last_ = fetchrow('o_last_service', "uid=1", "last_date");
$dt = new DateTime($last_);

$last_date = $dt->format('Y-m-d');

$today = $date;
$day_15 = datesub($date, 0 , 0, 15);
$day_30 = datesub($date, 0, 0,30);

$all_loans = table_to_array('o_loans',"status!=0 AND  pending_event=7 AND disbursed=1 AND paid = 0 AND product_id = 1","30","uid");

$all_loans_string = implode(',', $all_loans);
// echo $all_loans_string."<br/>";

//die();


$all_daily_addons = table_to_array('o_loan_addons',"addon_id=7 AND status=1 AND loan_id in ($all_loans_string)","100000000","loan_id");
$already_added_string = implode(',', $all_daily_addons);
$addon_d = fetchrow('o_addons',"uid=4","amount");

// echo $already_added_string;

// die();


$loan_total_days_array = array();
$addon_add = $addon_update = 0;
$loans = fetchtable('o_loans', "uid in ($all_loans_string)", "uid", "asc", "100000000", "uid, given_date, final_due_date, loan_amount");
while ($l = mysqli_fetch_array($loans)) {
    $uid = $l['uid'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_date'];
    $loan_amount = $l['loan_amount'];
    $days_ago = datediff3($given_date, $date);        ///////---------5
    if($days_ago > 14){
        if($days_ago > 30){
            $days_ago = 30;
        }
        $days_passed = $days_ago - 14;
        $interest = ($days_passed/100)*$loan_amount;

          echo "$uid: $interest <br/>";

        if(in_array($uid, $all_daily_addons) == 1){
            ///-----Update current daily
            $update = addon_with_amount_update(7, $uid, $interest, false);
            $addon_update += 1;
            //---Update loan details to avoid recalc
        }
        else{
            ///-----Create new addons
            $addon = addon_with_amount(7, $uid, $interest, 0, false);
            $addon_add += 1;
            ///-----Update loan details
            /// ---Update loan details to avoid recalt

        }
    }



}

/////--------total repayable addons
$loan_addons_total_array = array();
$all_addons = fetchtable('o_loan_addons',"status = 1 AND loan_id in ($all_loans_string)","uid","asc","100000000","loan_id, addon_amount");
while($aa = mysqli_fetch_array($all_addons)){
    $lid = $aa['loan_id'];
    $aamount = $aa['addon_amount'];
    $loan_addons_total_array =  obj_add($loan_addons_total_array, $lid, $aamount);

}


//   echo "$total_updated/$total_to_update Addons Updated \n";

////////////////////////////////-------------------Final Loop
$loans_f = fetchtable('o_loans', "uid in ($all_loans_string)", "uid", "asc", "100000000", "uid,  total_repaid, loan_amount");
while ($ll = mysqli_fetch_array($loans_f)) {
    $loan_uid = $ll['uid'];
    $loan_total_repaid = $ll['total_repaid'];
    $loan_amount = $ll['loan_amount'];

    $total_repayable = $loan_addons_total_array[$loan_uid] + $loan_amount;
    $total_addons = $loan_addons_total_array[$loan_uid];
    $loan_balance = $total_repayable - $loan_total_repaid;

    // echo "$loan_uid: $total_repayable: $loan_balance <br/>";

    $update = updatedb('o_loans',"total_repayable_amount='$total_repayable', total_addons='$total_addons',loan_balance='$loan_balance', pending_event=0","uid='$loan_uid'");


}
echo "$addon_add: Addons Created, $addon_update: Addons updated \n";
//  $update=updatedb('o_last_service',"last_date='$fulldate'","uid=1");