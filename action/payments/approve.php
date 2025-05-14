<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}

$permi = permission($userd['uid'],'o_incoming_payments',"0","APPROVE");
if($permi != 1){
    exit(errormes("You don't have permission to approve payment allocation"));
}

$pid = decurl($_POST['pid']);



////////////////////////
if($pid > 0){}
else{
    exit(errormes("Payment ID Invalid"));
}
$payment = fetchonerow('o_incoming_payments',"uid='$pid'");
$loan_id = $payment['loan_id'];
$group_id = $payment['group_id'];

$update = updatedb('o_incoming_payments',"status=1","uid = $pid");
if ($update == 1) {

    $event = "Payment approved  by [".$userd['name']."(".$userd['email'].")] on [$fulldate]";
    store_event('o_incoming_payments', $pid,"$event");

    echo sucmes('Payment approved Successfully');
    if($loan_id > 0) {
        recalculate_loan($loan_id);
    }
    $proceed = 1;
} else {
    echo errormes('Error approving Payment'.$update);
}
?>

<script>
    if('<?php echo $proceed; ?>'){
        setTimeout(function () {
            group_payment_list('<?php echo $group_id; ?>');
        },1500);
    }
</script>
