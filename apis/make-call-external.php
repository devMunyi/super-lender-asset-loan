<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once ("../php_functions/functions.php");
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");


$recipient_phone = $_POST["recipient_phone"];
$agent_phone = $_POST['agent_phone'];

if(validate_phone($recipient_phone) == false){
    $details_ = '"Invalid recipient phone. Start with 254..."';
    $result_code = 100;
    $result_ = '"FAILED"';
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
if(validate_phone($agent_phone) == false){
    $details_ = '"Invalid agent phone. Start with 254..."';
    $result_code = 101;
    $result_ = '"FAILED"';
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
    $agent_uid = fetchrow('o_users',"phone='$agent_phone'","uid");
    $recipient_uid = fetchrow('o_customers',"primary_mobile='$recipient_phone'","uid");
    $sessionId = "EXT-".generateRandomString(15);

    $fds = array('agent_id','agent_phone','client_id','client_phone','initiated_date','call_direction','session_id','result','status');
    $vals = array("$agent_uid","$agent_phone","$recipient_uid","$recipient_phone","$fulldate","2",$sessionId,'INITIATED','1');
    $create = addtodb('o_call_logs', $fds, $vals);

   if($create == 1){
       $details_ = '"Phone record created"';
       $result_code = 111;
       $result_ = '"SUCCESS"';
       echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
       die();
       exit();
   }
   else{
       $details_ = '"Error creating record. Internal error"';
       $result_code = 103;
       $result_ = '"FAILED"';
       echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
       die();
       exit();

   }



