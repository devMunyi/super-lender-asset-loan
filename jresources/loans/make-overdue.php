<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$all_loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND final_due_date < '$date' AND status=3","uid","asc","1000000","uid, final_due_date");
while($l = mysqli_fetch_array($all_loans)){

    $uid = $l['uid'];
    $final_due_date = $l['final_due_date'];

    $up = updatedb('o_loans',"status=7","uid='$uid'");
}

