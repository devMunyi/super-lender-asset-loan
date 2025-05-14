<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../configs/archive_conn.php");
include_once ("../php_functions/functions.php");

$months_3 = datesub($date, 0, 0, 100);
///////////--------------All uncleared loans
$loans_total_addons = array();
$all_loans = array();
$loans = fetchtable('o_loans',"disbursed=1 AND given_date >= '$months_3' AND paid=0 AND status = 7","uid","asc","1000000","uid, total_addons");
while($l = mysqli_fetch_array($loans)){
    $lid = $l['uid'];
    $total_addons = $l['total_addons'];
    $loans_total_addons[$lid] = $total_addons;
    array_push($all_loans, $lid);


}

//echo json_encode($loans_total_addons);

//die();
$loan_list = implode(',', $all_loans);

$loan_addons_array = array();
$adds = fetchtable('o_loan_addons',"status=1 AND loan_id in ($loan_list)","uid","asc","100000000","uid, loan_id, addon_id, addon_amount");
while($a = mysqli_fetch_array($adds)){
    $auid = $a['uid'];
    $lid = $a['loan_id'];
    $addon_amount = $a['addon_amount'];
    $loan_addons_array = obj_add($loan_addons_array, $lid, $addon_amount);


}

//echo json_encode($loan_addons_array);

for($i = 0; $i <= sizeof($all_loans); ++$i){
    $loan_id = $all_loans[$i];
    $calculated_amount = round($loans_total_addons[$loan_id],0);
    $addons_amount = round($loan_addons_array[$loan_id],0);

    if(($calculated_amount) != ($addons_amount)) {
      //  echo "$loan_id, $calculated_amount, $addons_amount; <br/>";
        recalculate_loan($loan_id);
    }
    else{
      // echo "Same: $loan_id, $calculated_amount, $addons_amount; <br/>";
    }

}


include_once("../configs/close_connection.inc");
?>