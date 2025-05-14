<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

///////////--------------remove all wrong LO pairs
$LOs = table_to_array('o_users',"status=1 AND user_group='7'","100000","uid");
$lo_list = implode(',', $LOs);

$COs = table_to_array('o_users',"status=1 AND user_group='8'","100000","uid");
$co_list = implode(',', $COs);


$total_updates = $total_loans = 0;
$loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND status!=0 AND current_lo > 0 AND (current_lo in ($co_list) AND current_co  in ($lo_list)) AND allocation='BRANCH'","uid","asc","100000","uid, current_lo, current_co");
while($l = mysqli_fetch_array($loans)){
    $loan = $l['uid'];
   // echo $loan.',<br/>';
    $current_lo = $l['current_lo'];
    $current_co = $l['current_co'];
    ///----Remove LO and CO
    $upd = updatedb('o_loans',"current_lo='$current_co', current_co='$current_lo', loan_flag='0'","uid='$loan'");
    $total_updates = $total_updates + $upd;
    $total_loans = $total_loans + 1;
}
echo "$total_updates/$total_loans LO/CO swapped";

include_once("../configs/close_connection.inc");
?>