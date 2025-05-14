<?php
header("Content-type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>';

session_start();
//include_once '../configs/20200902.php';
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$data_ = json_encode($_POST);
$logFile = 'call-in.txt';
$log = fopen($logFile,"a");
fwrite($log, "Callback $data_".','.date('Y-m-d H:i:s')."\n");
fclose($log);
/*
$aft_agent = "+254702332796";
$aft_message = "Welcome to Zidicash!  Please note that this call will be recorded for quality assurance purposes. Your call will be picked shortly. Thank you for your patience!";
$support_number = "+254702332796"
*/

/////////////////////////////////-------Record initial requests
/// ---Outbound
/// {
//  "callSessionState": "Ringing",
//  "direction": "Inbound",
//  "callerCountryCode": "-1",
//  "callerNumber": "zidi.jonah",
//  "sessionId": "ATVId_7d8b9d8da63fa3d401101720f6c090aa",
//  "clientDialedNumber": "254702332796",
//  "destinationNumber": "+254711082942",
//  "callerCarrierName": "None",
//  "callStartTime": "2025-02-13 08:32:37",
//  "isActive": "1"
//}
////--Inbound
///{
//  "callSessionState": "Ringing",
//  "direction": "Inbound",
//  "callerCountryCode": "254",
//  "callerNumber": "+254716330450",
//  "sessionId": "ATVId_8727006c461fa5d03e10c621887746ec",
//  "destinationNumber": "+254711082942",
//  "callerCarrierName": "Safaricom",
//  "callStartTime": "2025-02-13 08:35:15",
//  "isActive": "1"
//}
///
///
///
$app_name = fetchrow('o_sms_settings',"property_name='AFT_VOICE_USERNAME'","property_value");
$sessionId_ = $_POST['sessionId'];
$exists = checkrowexists('o_call_logs',"session_id='$sessionId_'");
if($exists == 0){
        if(isset($_POST['clientDialedNumber'])){
            ///----Its outgoing
            $direction = 2;
            $callerNumber = $_POST['callerNumber'];
            $ag = explode('.', $callerNumber);
            $agent_uid = $ag[1];
            $agent_det = fetchonerow('o_users',"uid='$agent_uid'","uid, phone");
            $agent_phone = $agent_det['phone'];
            $client_number = $_POST['clientDialedNumber'];

            $data_ = json_encode($_POST);
            $logFile = 'call-in.txt';
            $log = fopen($logFile,"a");
            fwrite($log, ">>>>>>>>> $callerNumber, $agent_uid <<<".','.date('Y-m-d H:i:s')."\n");
            fclose($log);
        }
        else{
            ///----Its incoming
            $direction = 1;
            $agent_uid = 0;
            $agent_phone = "$support_number";
            $client_number = ltrim($_POST['callerNumber'], '+');

            ////----Check if any agent had tried to call client
            $last_out = fetchmaxid('o_call_logs',"client_phone='$client_number' AND agent_id > 0","uid, agent_id, agent_phone");
            if($last_out['uid'] > 0){
                $agent_uid = $last_out['agent_id'];
                $agent_phone = $last_out['agent_phone'];
            }

        }

    $client_det = fetchonerow('o_customers',"primary_mobile='$client_number'","uid, primary_mobile");
    $recipient_uid = $client_det['uid'];
    $recipient_phone = $client_det['primary_mobile'];


    ///-----Its a new session
    $fds = array('agent_id','agent_phone','client_id','client_phone','initiated_date','call_direction','session_id','result','status');
    $vals = array("$agent_uid","$agent_phone","$recipient_uid","$recipient_phone","$fulldate","$direction",$sessionId_,'INITIATED','1');
    $create = addtodb('o_call_logs', $fds, $vals);

}



///
////////////////////////////////-------End of record initial request




$agent = $support_number;
$message = "Welcome, Please hold";

$agents = "$agent";


if (isset($_POST['clientDialedNumber'])) {
   /* {"callSessionState": "Ringing",
  "direction": "Inbound",
  "callerCountryCode": "-1",
  "callerNumber": "zidi.jonah",
  "sessionId": "ATVId_0ecbcf270efaf2bf6dd0aeceaa8f507a",
  "clientDialedNumber": "254702332796",
  "destinationNumber": "+254711082942",
  "callerCarrierName": "None",
  "callStartTime": "2025-02-12 17:14:18",
  "isActive": "1"
       }  */
    ///-------Outgoing call
    $message = "Connecting";
    $agent_details = $_POST['callerNumber'];
    $dialer = $_POST['callerNumber'];
    $sessionId = $_POST['sessionId'];
    ///----Pick session details
    $session_details = fetchonerow('o_call_logs',"session_id='$sessionId' AND result in('INITIATED','RECEIVED')","uid, agent_id, agent_phone");
    if($session_details['uid'] > 0){
       ////--Update log
        $agent_caller_phone = $session_details['agent_phone'];
        $agent_id = $session_details['agent_id'];
    }
    $agents = "$dialer";

}
else{
    ////------Incoming Calls
    /*
 {
  "callSessionState": "Ringing",
  "direction": "Inbound",
  "callerCountryCode": "254",
  "callerNumber": "+254716330450",
  "sessionId": "ATVId_ac6c5e67fca7687a0c8318df05d8d51a",
  "destinationNumber": "+254711082942",
  "callerCarrierName": "Safaricom",
  "callStartTime": "2025-02-12 17:04:47",
  "isActive": "1"
}
     */

    $message = $aft_message;
    $isActive = $_POST['isActive'];
    $callerNumber = $_POST['callerNumber'];
    $sessionId = $_POST['sessionId']; // Unique session identifier

    if($isActive == "1"){
        //----New incoming call SMH
        $session_details = fetchonerow('o_call_logs',"session_id='$sessionId' AND result in('INITIATED','RECEIVED')","uid, agent_id, agent_phone");
        if($session_details['uid'] > 0){
            ////--Update log
            $agent_caller_phone = $session_details['agent_phone'];
            $agent_id = $session_details['agent_id'];

            $agents = "$app_name.$agent_id,$agent_caller_phone";

            $logFile = 'call-in.txt';
            $log = fopen($logFile,"a");
            fwrite($log, "###### $agents *******".','.date('Y-m-d H:i:s')."\n");
            fclose($log);
        }
        else{
           // $agents = "254716330450";
        }
    }
   // $agents = "254716330450";




}

if (isset($_POST['clientDialedNumber'])) {
    ///-------Outgoing call
    $agents = $_POST['clientDialedNumber'];
}
else{
    ////------Incoming Calls

  //  $agents = "zidi.1,254716330450";

}


?>

<Response>
    <Say><?php echo $message; ?></Say>
    <Dial record="true" sequential="false" phoneNumbers="<?php echo $agents;?>"/>
</Response>

