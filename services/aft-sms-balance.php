<?php
session_start();
include_once '../configs/20200902.php';
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");
include_once("../php_functions/bongasms.php");

echo updateSmsBalance();

include_once("../configs/close_connection.inc");