<?php
$company = $_GET['c'];
$req_id = $_GET['r'];
$data  = file_get_contents('php://input');


$logFile = 'log-out.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.'Company-'.$company."->".date('Y-m-d H:i:s')."\n");
fclose($log); 

$result = json_decode(trim($data), true);

$ResultCode = $result['Result']['ResultCode'];
$ResultDesc = $result['Result']['ResultDesc'];
$TransactionID = $result['Result']['TransactionID'];
$TransactionDetails = $result['Result']['ResultParameters']['ResultParameter'];

//var_dump($TransactionDetails[0]);

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://159.65.231.232/lender/apis/b2c-notice.php?r='.$req_id,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => ''.$data.'',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Cookie: PHPSESSID=sqiuiucom3r6rrs1boq4rkhl4v'
    ),
));

$response = curl_exec($curl);

curl_close($curl);

