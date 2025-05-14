<?php
session_start();
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

$addons  = fetchtable('o_loan_addons',"added_date='2024-12-19 12:01:20' AND status=1 AND addon_id=17","uid","asc","1000","uid, loan_id");
while($a = mysqli_fetch_array($addons)){
    $uid = $a['uid'];
    $loan_id = $a['loan_id'];

    echo "$uid Lid: $loan_id<br/>";
    $upd = updatedb('o_loan_addons',"status=0","uid='$uid'");
   echo  recalculate_loan($loan_id);
}
