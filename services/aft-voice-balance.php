<?php
session_start();
//include_once("../configs/auth.inc");
include_once '../configs/20200902.php';
include_once("../php_functions/functions.php");


$db = $db_;
$_SESSION['db_name'] = $db;
include_once("../configs/conn.inc");

$response = voice_balance();

$result = json_decode($response, true);
//$balance = $result['ResultDetails']['CREDIT_BALANCE'];
//
//$bal = explode(' ', $response);
$balance = preg_replace("/[^0-9\.]/", "", $response);

$bal = round($balance, 0);
echo $bal;

$upd = updatedb('o_summaries', "value_='$bal'", "name='VOICE_BALANCE'");

include_once("../configs/close_connection.inc");


function voice_balance()
{
    $curl = curl_init();

    $username = fetchrow('o_sms_settings', "property_name='AFT_VOICE_USERNAME'", "property_value");
    $apiKey = fetchrow('o_sms_settings', "property_name='AFT_VOICE_APIKEY'", "property_value");

    if (input_length($username, 3) == 1) {
        //  $apiKey = 'cc1e8a7a26e73da5414d2d4a1cdc7927bfbf0bda9876d3f883e70d0b1a2e4f30';


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.africastalking.com/version1/user?username=' . $username,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'apiKey:' . $apiKey
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return $response;
    } else {
        return "NO  SETTINGS";
    }
}