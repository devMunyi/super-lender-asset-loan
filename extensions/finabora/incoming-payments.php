<?php
session_start();
include_once ("../");
$company = $_GET['c'];
$data  = file_get_contents('php://input');

/*
$logFile = 'log.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.'Company-'.$company."->".date('Y-m-d H:i:s')."\n");
fclose($log); */

$result = json_decode(trim($data), true);

$TransID = $result['TransID'];
$TransTime = $result['TransTime'];
$TransAmount = $result['TransAmount'];
$BillRefNumber = $result['BillRefNumber'];
$OrgAccountBalance = $result['OrgAccountBalance'];
$name = $result['FirstName'].' '.$result['MiddleName'].' '.$result['LastName'];
$MSISDN = $result['MSISDN'];

$branch_id = 0;
$latest_loan_id = 0;


if($TransAmount > 0) {

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://159.65.231.232/lender/extensions/finabora-incoming-pays',
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
    // echo $response;


    die();


        
    
}
else{
    echo "Amount invalid";
}