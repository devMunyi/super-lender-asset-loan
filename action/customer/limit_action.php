<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

$lid = $_POST['lid'];
$action = $_POST['action'];

if($lid < 1){
    die(errormes("Limit not selected"));
}
$proceed = 0;

if($action != 'APPROVE' && $action != 'REJECT'){
    die(errormes("Action invalid"));
}

$approve_perm  = permission($userd['uid'], 'o_customer_limits', "0", "general_");
if($approve_perm != 1){
    if($action == 'APPROVE'){
        $approve_perm  = permission($userd['uid'], 'o_customer_limits', "0", "APPROVE");
        if($approve_perm != 1){
            exit(errormes("You don't have permission to approve limit"));
        }
    }else if($action == 'REJECT'){
        $approve_perm  = permission($userd['uid'], 'o_customer_limits', "0", "REJECT");
        if($approve_perm != 1){
            exit(errormes("You don't have permission to approve limit"));
        }
    }else{
        exit(errormes("Action invalid"));
    }
}

$limit_status = fetchrow('o_customer_limits', "uid='$lid'", "status");
if($limit_status == 3){
    ///----Requires special approval
    $approve_perm  = permission($userd['uid'], 'o_customer_limits', "0", "SHARP_INCREMENT");
    if($approve_perm != 1){
        exit(errormes("You don't have permission to approve/reject special limit"));
    }
}

if($action == 'APPROVE') {
    $approve = updatedb('o_customer_limits', "status=1", "uid='$lid'");
    $lim = fetchonerow('o_customer_limits', "uid='$lid'", "amount, customer_uid");
    $amount_ = $lim['amount'];
    $customer_uid = $lim['customer_uid'];
    $update_cust = updatedb('o_customers', "loan_limit='$amount_'", "uid='$customer_uid'");
    if ($update_cust == 1) {
        $proceed = 1;
        echo sucmes("Limit approved");
        $event = "Limit approved by [".$userd['name']."(".$userd['email'].")] on [$fulldate]";
        store_event('o_customer_limits', $lid,"$event");
    }
    else{
        echo errormes("Error approving limit");
    }
}
else{
    $reject = updatedb('o_customer_limits', "status=0", "uid='$lid'");
    if($reject == 1){
        echo sucmes("Limit rejected successfully");
        $event = "Limit rejected by [".$userd['name']."(".$userd['email'].")] on [$fulldate]";
        store_event('o_customer_limits', decurl($lid),"$event");
        $proceed = 1;
    }
    else{
        echo errormes("Error rejecting limit");
    }
}



?>

<script>

    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function (){
            reload();
        }, 2000);
    }
</script>
