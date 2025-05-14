<?php
session_start();
$company = $_GET['c'];
$data  = file_get_contents('php://input');


$logFile = 'logx.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.'Company-'.$company."->".date('Y-m-d H:i:s')."\n");
fclose($log);
