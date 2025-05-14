<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

$cid = $_POST['cid'];
$new_limit = $_POST['new_limit'];
$limit_reason = $_POST['limit_reason'];

if($cid < 1){
    die(errormes("Customer invalid"));
}
$proceed = 0;


$update_limit = permission($userd['uid'], 'o_customer_limits', "0", "update_");
if($update_limit != 1){
    exit(errormes("You don't have permission to update limit"));
}

$pending_limit = checkrowexists('o_customer_limits',"customer_uid='$cid' AND status in (2,3)");
if($pending_limit == 1){
    exit(errormes("Customer has a limit pending approval"));
}

////------Security check to check that limit is not increased drastically
if($limit_maker_checker == 1) {
    $limit_status = 2;

    $last_lim = fetchmax('o_customer_limits', "customer_uid='$cid' AND status in (1,2)", "uid", "amount");
    $last_limit = $last_lim['amount'];
    $change = $new_limit - $last_limit;
    if ($change >= 5000 && $last_limit >= 1000) {
         $limit_status = 3;
         $limit_reason="[SHARP LIMIT INCREMENT FROM KSH. $last_limit to $new_limit. REQUIRES SPECIAL ADMIN APPROVAL]. Agent\'s Remarks: ".$limit_reason;
    }
    if ($change >= 40000) {
          $limit_status = 3;
          $limit_reason="[INCREMENT EXCEEDS Ksh. 40,000 FROM KSH. $last_limit to $new_limit. REQUIRES SPECIAL ADMIN APPROVAL]. Agent\'s Remarks: ".$limit_reason;
    }
}
else{
    $limit_status = 1;
}

////------
$user_id = $userd['uid'];
$fds = array('customer_uid','amount','given_date','given_by','comments','status');
$vals = array("$cid","$new_limit","$fulldate","$user_id","$limit_reason","$limit_status");
$create = addtodb('o_customer_limits', $fds, $vals);
if($create == 1){

    if($limit_maker_checker != 1) {
        $update_cust = updatedb('o_customers', "loan_limit='$new_limit'", "uid='$cid'");
    }
    echo sucmes("Limit updated successfully");
    $proceed = 1;
}
else{
    echo errormes("Unable to create limit. Please retry $create");
}


?>

<script>

    if('<?php echo $proceed; ?>' === "1"){
        update_limit_popup('<?php echo $cid; ?>','EDIT');
        setTimeout(function (){
            reload();
        }, 2000);
    }
</script>