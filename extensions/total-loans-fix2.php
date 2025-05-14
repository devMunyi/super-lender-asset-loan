<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

///////////--------------remove all wrong LO pairs


//echo send_money(254724282695,	8000.00,	201849);
/*
$all_cust = array();
$active = fetchtable('o_customers',"status = 1","uid","asc","1000000","uid");
while($c = mysqli_fetch_array($active)){
    $cid = $c['uid'];
   // echo "$cid, <br/>";
    array_push($all_cust, $cid);

}

$cust = implode(',', $all_cust);
//echo $cust;
$customer_loans = array();

$loans = fetchtable('o_loans',"disbursed=1 AND status!=0 AND customer_id in ($cust)","uid","asc","10000000","uid, customer_id");
while($l = mysqli_fetch_array($loans)){
    $cuid = $l['uid'];
    $customer_id = $l['customer_id'];
    $customer_loans = obj_add($customer_loans, $customer_id, 1);
}

*/

include_once("../configs/close_connection.inc");
?>