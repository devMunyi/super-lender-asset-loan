<?php
session_start();
$data  = file_get_contents('php://input');
//include_once '../configs/20200902.php';
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$queryString = $data;

// Parse query string into an associative array
parse_str($queryString, $params);

// Extract required parameters
$durationInSeconds = isset($params['durationInSeconds']) ? (int)$params['durationInSeconds'] : null;
$amount = isset($params['amount']) ? number_format((float)$params['amount'], 2) : null;
$sessionId = isset($params['sessionId']) ? $params['sessionId'] : null;
$recordingUrl = isset($params['recordingUrl']) ? urldecode($params['recordingUrl']) : null;

if(isset($params['amount']))
{
   // $sessionId = $params['sessionId'];
    $upd = updatedb('o_call_logs',"recording_url='$recordingUrl', duration_seconds='$durationInSeconds', amount_charged='$amount'","session_id='$sessionId'");

    $logFile = 'call-events.txt';
    $log = fopen($logFile,"a");
    fwrite($log, "$recordingUrl, $durationInSeconds, $amount, $sessionId".''."Event->".date('Y-m-d H:i:s')."\n");
    fclose($log);
}


$logFile = 'call-events.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.''."Event->".date('Y-m-d H:i:s')."\n");
fclose($log);



