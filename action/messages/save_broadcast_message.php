<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}


$send_sms  = permission($userd['uid'],'o_sms_outgoing',"0","create_");
if($send_sms == 0) {
    die(errormes("You don't have permission to send messages"));
    exit();
}
/////---------End of session check

//// ====== setting variables
$message = $_POST['message'];
$customer = $_POST['customerId'];
$created_by = $userd['uid'];
$cust = fetchonerow(
  'o_customers',
  "uid = '$customer' AND status > 0",
  'primary_mobile'
);

$phone = $cust['primary_mobile'];


//================== validation

if(input_available($message) == 0){
    exit(errormes("Message is required"));
}elseif((input_length($message, 3)) == 0){
    exit(errormes("Message is too short"));
}else{

}

//===================== Save operation
$fds = array('message_body','created_by', 'phone', 'queued_date');
$vals = array("$message","$created_by", "$phone", "$fulldate");

$create = addtodb('o_sms_outgoing', $fds, $vals);
if($create == 1){
    echo sucmes('Okay');
    $proceed = 1;
    $last_campaign = fetchmax('o_sms_outgoing',"message_body=\"$message\"","uid","uid");
    $cid = $last_message['uid'];
}
else{
    echo errormes('Message was not created!');
}

?>


<script>
    if('<?php echo $proceed == 1 ?>'){
        setTimeout(function () {
            // remove the modal
            $('#mainModal').modal('hide');
        }, 1000);

    }
</script>
<?php include_once '../../configs/close_connection.inc'; ?>