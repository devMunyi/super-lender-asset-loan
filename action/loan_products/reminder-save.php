<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$rid = $_POST['rid'];
$pid = $_POST['pid'];
$loan_day = $_POST['loan_day'];
$custom_event = $_POST['custom_event'];
$loan_status = $_POST['loan_status'];
$message = $_POST['message'];
$status = $_POST['status'];

$userd = session_details();

if((input_length($message, 5)) == 0){
    die(errormes("Message too short"));
    exit();
}

if($rid > 0){
    ////--------------Its an update
    $save = updatedb('o_product_reminders',"product_id='$pid', loan_day='$loan_day', custom_event='$custom_event', loan_status='$loan_status', message_body='$message', added_date='$fulldate', status='$status'","uid='$rid'");

    $event = "Product reminder updated by [".$userd['name']."(".$userd['email'].")] on [$fulldate] with details. Loan day: $loan_day, Event: $custom_event, Message: $message";
    store_event('o_product_reminders', $pid,"$event");

}
else{
    ////--------------Its a create
    $fds = array('product_id','loan_day','custom_event','loan_status','message_body','added_date','status');
    $vals = array("$pid","$loan_day","$custom_event","$loan_status","$message","$fulldate","$status");
    $save = addtodb('o_product_reminders', $fds, $vals);

    $event = "Product reminder created by [".$userd['name']."(".$userd['email'].")] on [$fulldate] with details. Loan day: $loan_day, Event: $custom_event, Message: $message";
    store_event('o_product_reminders', $pid,"$event");
}

if($save == 1){
    sucmes("Reminder saved");
    $proceed = 1;
}
else{
    errormes("Unable to save reminder");
}
?>
<script>

    if('<?php echo $proceed; ?>'){
        modal_hide();
        setTimeout(function () {
            product_reminders('<?php echo $pid; ?>')
        },1000);
    }
</script>

