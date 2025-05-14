<?php
session_start();
include_once("../configs/conn.inc");
include_once("../configs/20200902.php");
include_once("../php_functions/functions.php");

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);




echo send_money(254716330450, 10);
