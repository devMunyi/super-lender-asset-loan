<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

///////////--------------remove all wrong LO pairs
///
echo  json_encode(real_loan_agent(13448));

include_once("../configs/close_connection.inc");
?>