<?php

// endpoint to periodically update token for b2c
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");
include_once("../php_functions/mpesa.php");
include_once("../php_functions/rmqUtils.php");

$raw_token = get_mpesa_access_token();

if(!$raw_token){
    echo "Failed to get token";
    // write to log file
    $log = "Failed to get token at " . date("Y-m-d H:i:s") . "\n";
    file_put_contents("./mpesa_token_update.log", $log, FILE_APPEND);

    exit();
}else{

    // echo "Raw Token: $raw_token <br>";
    $enc_key = generateEncryptionKey();
    $enc_token = encryptString($raw_token, $enc_key);
    $token_update_reponse = update_b2c_tkn_and_enc_key($enc_token, $enc_key);
    
    if ($token_update_reponse) {
        echo "Token updated successfully <br>";
    } else {
        echo "Failed to update token <br>";
    }

    if(isset($B2C_RMQ_IS_SET)){
        updatedb('o_mpesa_configs', "r_token='$raw_token'", "uid=1");
        echo "RMQ is set and token updated <br>";
    }
}

