<?php

$data  = file_get_contents('php://input');

$logFile = 'mtn-out.txt';
$log = fopen($logFile, "a");
fwrite($log, $data . 'Company-' . $company . "->" . date('Y-m-d H:i:s') . "\n");
fclose($log);

$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'http://simplepayug.com/lender/apis/ug-mtn-b2c-notice.php',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => '' . $data . '',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Cookie: PHPSESSID=sqiuiucom3r6rrs1boq4rkhl4v'
    ),
));

$response = curl_exec($curl);

// // Log the cURL response
// $log = fopen($logFile, "a");
// fwrite($log, 'cURL Response: ' . $response . '->' . date('Y-m-d H:i:s') . "\n");
// fclose($log);

curl_close($curl);
