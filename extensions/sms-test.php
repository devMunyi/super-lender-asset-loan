<?php
session_start();
$_SESSION['db_name'] = 'finabora_new_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");
require_once('../php_functions/AfricasTalkingGateway.php');



$from = $_POST['from'];
$to = $_POST['to'];
$text = trim($_POST['text']);
$date_ = $_POST['date'];
$id = $_POST['id'];
$linkId = $_POST['linkId']; //This works for onDemand subscription products

echo send_sms_interactive_(254716330450, "Delivered", $linkId);

function send_sms_interactive_($mobile_number, $message, $linkId = 0){

    global $fulldate;
    $curl = curl_init();
    $bulk_code = fetchrow('o_sms_settings',"property_name='SHORT_CODE'","property_value");
    $username = fetchrow('o_sms_settings',"property_name='SHORT_CODE_USERNAME'","property_value");
    $apiKey = fetchrow('o_sms_settings',"property_name='AFT_2WAY_KEY'","property_value");

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://content.africastalking.com/version1/messaging',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array('username' => ''.$username.'', 'to' => '' . $mobile_number . '', 'message' => '' . $message . '', 'from' => '' . $bulk_code . '', 'bulkSMSMode ' => '0', 'keyword' => 'A', 'linkId' => '' . $linkId . ''),
        CURLOPT_HTTPHEADER => array(
            'Apikey: '.$apiKey.''
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;


}

include_once("../configs/close_connection.inc");
?>