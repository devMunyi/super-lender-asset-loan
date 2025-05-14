<?php
session_start();
include_once("../configs/auth.inc");
include_once '../configs/20200902.php';

    include_once("../php_functions/functions.php");


    $db = $db_;
    $_SESSION['db_name'] = $db;
    include_once("../configs/conn.inc");


    $addons_total = array();
    $one_addon = array();


    $addons = fetchtable('o_loan_addons',"status=1 AND addon_id=2","uid","asc","10000","loan_id, addon_id, uid");
    while($a = mysqli_fetch_array($addons)){
        $uid = $a['uid'];
        $loan_id = $a['loan_id'];
        $addon_id = $a['addon_id'];

        $one_addon[$loan_id] = $uid;

        $addons_total = obj_add($addons_total, $loan_id, 1);
    }


foreach ($addons_total as $loan_id => $total ){

    if($total > 1){
        echo "Loan $loan_id <br/>";
        $aid = $one_addon[$loan_id];
        echo updatedb('o_loan_addons',"status=0","uid='$aid'");
        recalculate_loan($loan_id);
    }
}