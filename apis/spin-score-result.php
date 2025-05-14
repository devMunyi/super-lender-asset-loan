<?php 

include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

$data = file_get_contents('php://input');
$logFile = 'spin-score-result.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.'Company-'.$company."->".date('Y-m-d H:i:s')."\n");
fclose($log);

$data = json_decode($data, true);


$file_unique_id = $data['file_unique_id'] ?? "";
$timestamp = $data['timestamp'] ?? "";

if(empty($file_unique_id)){
    exit(errormes("Invalid Request"));
}


$json_encoded = json_encode($data);
$update = updatedb("o_spin_scoring", "result='$json_encoded', spin_status=2, processed_date='$timestamp'", "doc_reference_id='$file_unique_id'");
