<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

///////////--------------remove all wrong LO pairs
$LOs = table_to_array('o_users',"status=1 AND user_group='7'","10000","uid");
$lo_list = implode(',', $LOs);

$COs = table_to_array('o_users',"status=1 AND user_group='8'","10000","uid");
$co_list = implode(',', $COs);

$upd = updatedb('o_loans',"loan_flag='0'","loan_flag='3'");

$total_updates = $total_loans = 0;
$loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND status!=0 AND current_lo > 0 AND (current_lo not in ($lo_list) OR current_co not in ($co_list)) AND allocation='BRANCH'","uid","asc","100000","uid");
while($l = mysqli_fetch_array($loans)){
    $loan = $l['uid'];
   // echo $loan.',<br/>';
    ///----Remove LO and CO
    $upd = updatedb('o_loans',"loan_flag='3'","uid='$loan'");
    $total_updates = $total_updates + $upd;
    $total_loans = $total_loans + 1;
}
echo "$total_updates/$total_loans LO/CO removed";

include_once("../configs/close_connection.inc");
?>