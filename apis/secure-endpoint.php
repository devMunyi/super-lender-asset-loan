<?php 
// allowed origins
include_once ("allowed-ips-or-origins.php");

// Check the request method  
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Check the 'Host' header to determine the origin
$origin =  $_SERVER['REMOTE_ADDR'];

if ($requestMethod !== $expected_http_method) {
    http_response_code(404);
    exit;
}

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $origin);

    // Only set Access-Control-Allow-Methods for POST requests
    if ($requestMethod === $expected_http_method) {
        header("Access-Control-Allow-Methods: $expected_http_method");
    }

    // header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    // header("Content-Type: application/json; charset=UTF-8");
} else {
    http_response_code(403);
    exit;
}

require("../../../vendor/autoload.php");
include_once("../../../configs/conn.inc");
include_once("../../../php_functions/functions.php");
include_once("../../../php_functions/jwtAuth.php");
include_once("../../../configs/jwt.php");
include_once("../../../extensions/tenakata_sms.php");

// check access token validity
$payload = verifyBearerTokenFromHeaders($jwt_key);

if ($payload === null) {
    sendApiResponse(401, "Access token missing or invalid");
}

$user = $payload->user;
$added_by = intval($user->uid);
$user_type = $user->user_type ? $user->user_type : '';