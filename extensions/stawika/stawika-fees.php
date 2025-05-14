<?php
session_start();
$_SESSION['db_name'] = 'stawika_db';
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");


$send = send_money(254716330450, 2200);
