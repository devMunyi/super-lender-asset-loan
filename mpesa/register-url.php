<?php
session_start();
//include_once("../php_functions/functions.php");
//include_once("../configs/conn.inc");
//$api_password = '6,w291E3';  ////----SImple Pay
//$api_password = '6,w291E3';  ////----Tenova

$consumerKey='tGgMgioNTWp1iqW8pLn0XtTAu8QbGGGLuNs2dKJROZRmxfZ5'; ///////- Simple Pay
$consumerSecret='tgzx94qrbYKGKp1s9uewJ3UOUQcCIJ7wGGgclDdPjsY9LCiHcHavwzhrx9G9eukx';              ///////- Simple Pay
///
//$consumerKey='g7P97oG28FAXe8AUKun7AUn92ANrZCgP'; ///////- Tenova
//$consumerSecret='1jcXOhS8mQWZtPgA';              ///////- Tenova

$headers=['Content-Type:application/json; charset=utf8'];
$url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
$curl = curl_init($url);
curl_setopt($curl,CURLOPT_HTTPHEADER,$headers);
curl_setopt($curl,CURLOPT_RETURNTRANSFER,true);

curl_setopt($curl,CURLOPT_HEADER,false);

curl_setopt($curl,CURLOPT_USERPWD,$consumerKey.':'.$consumerSecret);
$result = curl_exec($curl);
$status = curl_getinfo($curl,CURLINFO_HTTP_CODE);
$result = json_decode($result);
$access_token = $result->access_token;
var_dump($result);
echo 'The access token is: '.$access_token;

//register the 3rd party urls.
$url = 'https://api.safaricom.co.ke/mpesa/c2b/v2/registerurl';

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$access_token)); //setting custom header

$simplepay = 2; ///7268432
$tenova = 4;   ///4048199


$curl_post_data = array(
    'ShortCode' => '6176614',
    'ResponseType' => 'Completed',
    'ConfirmationURL' => 'https://pembeni.supersystems.co.ke/lender/apis/incoming-pays',
    'ValidationURL' => 'https://pembeni.supersystems.co.ke/lender/apis/incoming-pays-validation'
);

$data_string = json_encode($curl_post_data);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);

$curl_response = curl_exec($curl);
print_r($curl_response);

echo "Register URL: ".$curl_response;
