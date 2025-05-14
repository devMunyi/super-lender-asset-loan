<?php
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
$curl = curl_init();


$consumerKey='8XyNzJNDogiGcAYBZ8SmCgQjsXW3UMND'; ///////- Simple Pay
$consumerSecret='ttNWIYpqkawDqrMA';              ///////- Simple Pay
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

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://api.safaricom.co.ke/mpesa/reversal/v1/request',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS =>'{
   "Initiator": "TENAKATAWEBAPI",
    "SecurityCredential": "BzFLlGkhDG5tIZrH8f2u5iAf9gZvQYbs+NxbC8tZPe/5V5iFiFijSKpUWzyGfLRe6UM3vUZK/rioSvsAAETXLqYDxEIw4w9fxLMeOcSHNRHyzrzv3fJvfN7gUeXYnQJ+98dgP5bULekun4ZxrLbNIOchxekj9SclA7MO9VaSYXYS2w6ozvWdrt7zPrnwfwE3p7H1R1XkyD+cW/IBP+T1zCEyp4JT7MrUV9ma3cnbZWrbsTF+CXLKfYPbzOMDMvSoDqXm1ZMgYylSoAQhIfY6/h9oS9VdJjnonn8Y9c1xNqhNwdnXl33YCnpZig6SDN+JVDDcfz8wiDhg3Q6xbJHiQA==",
    "CommandID": "TransactionReversal",
    "TransactionID": "RK47E7E7QLLN",
    "Amount": "20.00",
    "ReceiverParty": "3033631",
    "RecieverIdentifierType": "11",
    "ResultURL": "https://tenakata.superlender.co.ke",
    "QueueTimeOutURL": "https://tenakata.superlender.co.ke",
    "Remarks": "Test",
    "Occasion": "No oc"
}',
    CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer <Access-Token>',
        'Content-Type: application/json',
        'Cookie: visid_incap_2742146=+nM2fdriTnSdm1IZIm7tHZ1QtmQAAAAAQUIPAAAAAABU+aTdkeZOiLDIYU2SgM8o'
    ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;