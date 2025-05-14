<?php
$data  = file_get_contents('php://input');

//echo "Success".$data;


$logFile = 'log-v.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.'Company-'.$company."->".date('Y-m-d H:i:s')."\n");
fclose($log);

echo '{"success":1,"status":"0","message":"Saved Successfully"}';

