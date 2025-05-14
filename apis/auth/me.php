<?php

$expected_http_method = 'GET';
include_once("../../vendor/autoload.php");
include_once("../../configs/cors.php");
include_once("../../configs/conn.inc");
include_once("../../configs/jwt.php");
include_once("../../php_functions/jwtAuthUtils.php");
include_once("../../php_functions/jwtAuthenticator.php");
include_once("../../php_functions/functions.php");


sendApiResponse(200, "", "OK", $user);

