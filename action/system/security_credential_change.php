<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if($userd == null){
    echo errormes("Your session is invalid. Please re-login");
    exit();
}
/////---------End of session check


$password = $_POST['password'];


///////----------Validation
if((input_available($password)) == 0){
    echo errormes("Value is required");
    exit();
}elseif((input_length($password, 2)) == 0){
    echo errormes("password is too short");
    exit();
}


$pubkey = file_get_contents('../../mpesa/cert.cer');
//$enc = '';
openssl_public_encrypt($password, $output, $pubkey, OPENSSL_PKCS1_PADDING);
//$enc .= $output;
$init = base64_encode($output);

//echo $init;
$permi = permission($userd['uid'], 'o_mepesa_configs', 0, 'update_');
if($permi != 1){
    exit(errormes("You do not have permission to perform this action"));
}

$init_enc = encryptStringSecure($init, $sl_key);

$update = updatedb('o_mpesa_configs', "property_value='$init_enc'", "uid=3");
if($using_queue_disburse_v2 == 1){
    $update = updatedb('o_mpesa_configs', "security_credential='$init_enc'", "uid=1");
}


if($update == 1){
    echo sucmes('Password Updated Successfully');
    $event = "Security credential changed by ".$userd['name']." (".$userd['uid'].")";
    store_event('o_mpesa_configs', 3 ,"$event");
    $proceed = 1;

}else{
        echo errormes('Error Updating password');
        exit();
}

?>

<script>

    if('<?php echo $proceed; ?>' == "1"){
        setTimeout(function () {
            reload();
        },1000);
    }
</script>
