<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(1200);
/*
 * This service is called after the daily checker products to add or update addons
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
    $yesterday = datesub($date, 0, 0, 1);
    $day_15 = datesub($date, 0 , 0, 15);
    $day_30 = datesub($date, 0, 0,30);

//    $all_loans = table_to_array('o_loans',"status!=0 AND  given_date BETWEEN '$day_30' AND '$day_15' AND disbursed=1 AND paid = 0","10000000","uid");
   $up0 = updatedb('o_loans',"pending_event='0'","uid > 0 AND pending_event!=0");

    ////-----Mark for daily interest
    $up = updatedb('o_loans',"pending_event='7'","uid > 0 AND status!=0 AND  given_date BETWEEN '$day_30' AND '$day_15' AND disbursed=1 AND paid = 0");

    ///-----DD+1
  $up2 = updatedb('o_loans',"pending_event='4'","uid > 0 AND status!=0 AND  final_due_date = '$yesterday' AND disbursed=1 AND paid = 0");

   ///-----DD+15
  $up3 = updatedb('o_loans',"pending_event='5'","uid > 0 AND status!=0 AND  final_due_date = '$day_15' AND disbursed=1 AND paid = 0");

    ///-----DD+30
   $up4 = updatedb('o_loans',"pending_event='6'","uid > 0 AND status!=0 AND  final_due_date = '$day_30' AND disbursed=1 AND paid = 0");

   echo "Update $up, $up2, $up3, $up4";


include_once("../configs/close_connection.inc");