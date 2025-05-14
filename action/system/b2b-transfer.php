<?php
session_start();
include_once('../../configs/20200902.php');
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if($userd == null){
    echo errormes("Your session is invalid. Please re-login");
    exit();
}
/////---------End of session check


$from = $_POST['from'] ?? 0;
$to = $_POST['to'] ?? 0;
$amount = $_POST['amount'] ?? 0;



///////----------Validation
if(empty($from) || !is_numeric($from)){
    exit(errormes("From input is required/Invalid"));
}

if(empty($to) || !is_numeric($to)){
    exit(errormes("To input is required/Invalid"));
}

if(empty($amount) || !is_numeric($amount)){
    exit(errormes("Amount input is required/Invalid"));
}

////// ======== End of Validation

$permi = permission($userd['uid'], 'o_summaries', 0, 'update_');
if($permi != 1){
    exit(errormes("You do not have permission to perform this action"));
}


// make b2b transfer
if($existing_b2b == 1){
    $result = b2b($from, $to, $amount);
}else{
    $result = b2b_v2($from, $to, $amount);
}
$result = json_decode($result, true);
$expected_response = array('ResponseCode' => '0', 'ResponseDescription' => 'Accept the service request successfully.');

if($result['ResponseCode'] != $expected_response['ResponseCode']){
    echo errormes($result['ResponseDescription'] ?? $result['errorMessage'] ?? 'Error making B2B Transfer');
    exit();
}else {
    echo sucmes('B2B Transfer Initiated Successfully');
    $event = "B2B Transfer made by ".$userd['name']." (".$userd['uid'].") from $from to $to of $amount";
    store_event('o_summaries', 1 ,"$event");
    $proceed = 1;
}

?>

<script>

    if('<?php echo $proceed; ?>' == "1"){
        setTimeout(function () {
            reload();
        },1000);
    }
</script>
