<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

$permi = permission($userd['uid'],'o_incoming_payments',"0","delete_");
if($permi != 1){
    die(errormes("You don't have permission to delete payment"));
    exit();
}

$pid = decurl($_POST['pid']);



////////////////////////
if($pid > 0){}
else{
    die(errormes("Payment ID Invalid"));
    exit();
}
$payment = fetchonerow('o_incoming_payments',"uid='$pid'");
$loan_id = $payment['loan_id'];
$group_id = $payment['group_id'];

$update = updatedb('o_incoming_payments',"status=0","uid = $pid");
if ($update == 1) {

    $event = "Payment deleted  by [".$userd['name']."(".$userd['email'].")] on [$fulldate]";
    store_event('o_incoming_payments', $pid,"$event");

    echo sucmes('Payment deleted Successfully');
    if($loan_id > 0) {
        recalculate_loan($loan_id);
    }
    $proceed = 1;
} else {
    echo errormes('Error deleting Payment'.$update);
}
?>

<script>
    if('<?php echo $proceed; ?>'){
        setTimeout(function () {
            group_payment_list('<?php echo $group_id; ?>');
        },1500);
    }
</script>
