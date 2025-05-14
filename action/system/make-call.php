<?php
session_start();
include_once '../../configs/20200902.php';
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");


$recipient_phone = $_POST["phone"];
$recipient_uid = $_POST["recipient_uid"];

$userd = session_details();
$agent_uid = $userd['uid'];
$agent_phone = $userd['phone'];

$make_call = permission($userd['uid'],'o_call_logs',"0","general_");
if($make_call != 1) {
    echo ("<span class='text-red'>You don't have permission to make calls</span>");
    die();
}


$configs = table_to_obj('o_sms_settings',"status=1","20","property_name","property_value");
$username = $configs['AFT_VOICE_USERNAME'];
$apikey = $configs['AFT_VOICE_APIKEY'];
$aft_number = $configs['AFT_VOICE_NUMBER'];

//var_dump($configs);

//die();

if((input_length($apikey, 10) == 0) || (input_length($username, 1) == 0)){
    echo ("<span class='text-red'>Configs not available</span>");
    die();
}


$curl = curl_init();

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://voice.africastalking.com/call',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => 'username='.$username.'&from=%2B'.$aft_number.'&to=%2B'.$recipient_phone.'&clientRequestId=ririr',
    CURLOPT_HTTPHEADER => array(
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded',
        'apiKey: '.$apikey.''
    ),
));

$response = curl_exec($curl);
curl_close($curl);
//echo $response;


$responseData = json_decode($response, true);
$sessionId = $responseData['entries'][0]['sessionId'];


//echo "<span class='text-green'>$sessionId</span>";

// Check if the decoding was successful and entries exist
if ((input_length($sessionId,4)) == 1) {
    // Access the first entry
    $phone = $responseData['entries'][0]['phoneNumber'];
    $errorId = $responseData['entries'][0]['errorMessage'];
    $status = $responseData['entries'][0]['status'];


    $fds = array('agent_id','agent_phone','client_id','client_phone','initiated_date','call_direction','session_id','result','status');
    $vals = array("$agent_uid","$agent_phone","$recipient_uid","$recipient_phone","$fulldate","2",$sessionId,'INITIATED','1');
    $create = addtodb('o_call_logs', $fds, $vals);

   if($status == 'Queued') {
       if ($create == 1) {
           echo("<span class='text-green'>Call initiated, please wait</span>");
       } else {
           echo("<span class='text-red'>There may be an error</span>");
       }
   }
   else{
       echo("<span class='text-red'>Error occurred! $errorId</span>");
   }

//    // Output the values
//    echo "Phone Number: " . $phoneNumber . "\n";
//    echo "Session ID: " . $sessionId . "\n";
//    echo "Status: " . $status . "\n";
} else {
    echo ("<span class='text-red'>Invalid response from provider</span>");
}


