<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");
$userd = session_details();
$staff_id = intval($userd["uid"]);

// ensure staff id is not 0
if ($staff_id == 0) {
    exit();
}

$alltotal = countotal("o_notifications", "status = 1 AND staff_id = $staff_id", "uid");

echo   trim($alltotal);
