<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}

$permi = permission($userd['uid'],'o_customer_limits',"0","RESET");
if($permi != 1){
    exit(errormes("You don't have permission to reset customers limit!"));
}

$branch_id_enc = intval($_POST['branch_id']);

if($branch_id_enc > 0){
    $branch_id = decurl($branch_id_enc);
    $update = updatedb('o_customers', "loan_limit=0", "branch=$branch_id");
    if($update == 1)
    {
        echo sucmes('Success limiting branch');
        $proceed = 1;
        $event = "Branch limits reset to zero by [".$userd['name']."(".$userd['email'].")]";
        store_event('o_branches', $branch_id, "$event");
    }
    else
    {
        exit(errormes('Unable to limit branch'));
    }
}else{
    exit(errormes("Branch ID invalid $branch_id_enc"));
}

?>

<script>

    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
        	gotourl("branches?branch=<?php echo $branch_id_enc; ?>");
        },2000);
    }
</script>