<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if($userd == null){
    echo errormes("Your session is invalid. Please re-login");
    exit();
}




/////---------End of session check

$agent_id = $_POST['agent'];



///////----------Validation
if($agent_id < 1){
    echo errormes("Agent not selected");
    exit();
}


$dec_agent = decurl($agent_id);
//die(errormes($dec_agent));
$proceed = 0;
$total_accounts = countotal('o_loans',"disbursed=1 AND paid=0 AND status!=0 AND current_agent='$dec_agent'","uid");
$up = updatedb('o_loans',"current_agent='0'","disbursed=1 AND paid=0 AND status!=0 AND current_agent='$dec_agent'");
if($up == 1){
    echo sucmes("$total_accounts Accounts unallocated successfully");
    $proceed = 1;
}
else{
    echo errormes("Accounts unallocated successfully");
}

?>

<script>

    if('<?php echo $proceed; ?>' == "1"){
        setTimeout(function () {
            reload();
        },1000);
    }
</script>
