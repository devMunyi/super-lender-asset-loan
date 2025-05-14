<?php

include_once("../../configs/config.inc");
include_once("../../configs/mtn_config.php");
include_once("../../php_functions/functions.php");
include_once("../../php_functions/mtn_functions.php");

$mtn_access_token = createMTNAccessToken();

$token_obj = createMTNAccessToken();
$access_token = $token_obj["payload"];

if ($access_token) {
    
}
