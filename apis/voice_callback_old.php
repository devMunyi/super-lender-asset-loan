<?php
header("Content-type: text/xml");
echo '<?xml version="1.0" encoding="UTF-8"?>';

session_start();
//include_once '../configs/20200902.php';
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");



$agent = $support_number;
$message = $aft_message;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Retrieve POST data
    $isActive = $_POST['isActive'];
    $callerNumber = $_POST['callerNumber'];
    $sessionId = $_POST['sessionId']; // Unique session identifier

    $callerNumber = make_phone_valid(ltrim($callerNumber, '+'));
// $data  = file_get_contents('php://input');
//    $logFile = 'call-in.txt';
//    $log = fopen($logFile,"a");
//    fwrite($log, $callerNumber.'OOOOOOOO'.date('Y-m-d H:i:s')."\n");
//    fclose($log);

    $existing_session = fetchmax('o_call_logs', "client_phone='$callerNumber' AND result='INITIATED'", "uid", "uid, agent_phone");
    if ($existing_session['uid'] > 0) {
        ///----Existing session, connect to the agent phone
        $agent_phone = $existing_session['agent_phone'];
        $agent_uid = $existing_session['uid'];
        $message = "Please wait";
        $update = updatedb('o_call_logs', "result='RECEIVED'", "uid='$agent_uid'");
        $agent = "+$agent_phone";
    } else {
        $select_available_agent = "";
    }

   /* $logFile = 'call-in.txt';
    $log = fopen($logFile, "a");
    fwrite($log, '*****' . $agent_phone . '*****' . date('Y-m-d H:i:s') . "\n");
    fclose($log);  */



?>


<?php
}
?>

<Response>
    <Say><?php echo $message; ?></Say>
    <Dial record="true" sequential="false" phoneNumbers="<?php echo $agent;?>"/>
</Response>

