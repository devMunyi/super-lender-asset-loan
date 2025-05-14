<?php
//session_start();
//include_once("../php_functions/functions.php");
//include_once("../configs/conn.inc");
//$api_password = 'Tenovaonline@2022';
// $api_password = 'Juhudibora4322!';

// $api_password = 'ReggieKasyoka197703#';
$api_password = 'q6YV#M?Fh2_?+)6';



$pubkey = file_get_contents('cert.cer');
//$enc = '';
openssl_public_encrypt($api_password, $output, $pubkey, OPENSSL_PKCS1_PADDING);
//$enc .= $output;
$init = base64_encode($output);

echo $init;
