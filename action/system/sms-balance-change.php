<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if ($userd == null) {
    echo errormes("Your session is invalid. Please re-login");
    exit();
}
/////---------End of session check


$balance = $_POST['balance'];

///////----------Validation
if ((input_available($balance)) == 0) {
    exit(errormes("Balance input is required"));
} elseif ((input_length($balance, 2)) == 0) {
    exit(errormes("Balance input is too short"));
}

$permi = permission($userd['uid'], 'o_summaries', 0, 'update_');
if ($permi != 1) {
    exit(errormes("You do not have permission to perform this action"));
}
$update = updatedb('o_summaries', "value_= '$balance'", "uid=3");
if ($update == 1) {
    echo sucmes('SMS Balance Updated Successfully');
    $event = "SMS Balance changed by " . $userd['name'] . " (" . $userd['uid'] . ")";
    store_event('o_summaries', 1, "$event");
    $proceed = 1;
} else {
    echo errormes('Error Updating SMS Balance');
    exit();
}

?>

<script>
    if ('<?php echo $proceed; ?>' == "1") {
        setTimeout(function() {
            reload();
        }, 1000);
    }
</script>