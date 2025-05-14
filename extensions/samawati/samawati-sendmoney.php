<?php
session_start();
include_once '../configs/20200902.php';
$_SESSION['db_name'] = 'tenakata_db';
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

/*
function send_money2($msisdn, $amount, $loan_id=0){
    global $fulldate;
    global $server2;
    if((input_length($server2, 5)) == 0){
        $server = 'https://www.superlender.co.ke';
    }
    else{
        $server = $server2;
    }
    $platform = company_settings();
    $company_id = $platform['company_id'];

   // echo $server;
    //die();
    $consumerKey = fetchrow('o_mpesa_configs',"uid='4'","property_value");
    $consumerSecret = fetchrow('o_mpesa_configs',"uid='5'","property_value");

    $headers = ['Content-Type:application/json; charset=utf8'];
    $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($curl, CURLOPT_HEADER, false);

    curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
    $result = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $result = json_decode($result);
    $access_token = $result->access_token;


    $url = 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest';//url b2c

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $access_token)); //setting custom header

    $InitiatorName = fetchrow('o_mpesa_configs',"uid='7'","property_value");;
    $SecurityCredential = fetchrow('o_mpesa_configs',"uid='3'","property_value");;
    $Amount = $amount;
    $PartyA = fetchrow('o_mpesa_configs',"uid='1'","property_value");;
    $PartyB = $msisdn;
    $Remarks = "L$loan_id";
    $QueueTimeOutURL = $server.'/apis/mpesa_queue_timeout.php';
    $ResultURL = $server.'/apis/b2c-notice.php?c='.$company_id.'&r='.$loan_id;
    $Occasion = '';

   // echo $consumerKey;

    $curl_post_data = array(
        //Fill in the request parameters with valid values
        'InitiatorName' => $InitiatorName,//This is the credential/username used to authenticate the transaction request.
        'SecurityCredential' => $SecurityCredential,//Base64 encoded string of the B2C short code and password,
        'CommandID' => 'PromotionPayment',//Unique command for each transaction type
        'Amount' => $Amount,//The amount being transacted
        'PartyA' => $PartyA,//Organization’s shortcode initiating the transaction.
        'PartyB' => $PartyB,//Phone number receiving the transaction
        'Remarks' => $Remarks,//Comments that are sent along with the transaction.
        'QueueTimeOutURL' => $QueueTimeOutURL,//The timeout end-point that receives a timeout response.
        'ResultURL' => $ResultURL,//The end-point that receives the response of the transaction
        'Occasion' => $Occasion //optional
    );

    $data_string = json_encode($curl_post_data);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

    $curl_response = curl_exec($curl);
    print_r($curl_response.$ResultURL);

    $json = $curl_response;
    $j = json_decode($curl_response);
    $state = $j->ResponseCode;
    $desc = $j->ResponseDescription;
    $errorMessage = $j->errorMessage;


    echo "$curl_response resp";


}
*/

$send = send_money(254716330450, 20);
include_once("../configs/close_connection.inc");