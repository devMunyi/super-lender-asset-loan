<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

function verifyBearerToken($jwt_token)
{

    global $jwt_key;
    try {
        $payload = JWT::decode($jwt_token, new Key($jwt_key, 'HS256'));
        return $payload;
    } catch (ExpiredException $e) {
        // Token has expired
        return ['error' => 'Token has expired'];
    } catch (Exception $e) {
        // Other JWT errors (invalid signature, malformed token, etc.)
        return ['error' => 'Invalid token'];
    }
}

function generateBearerToken($jwt_key, $user)
{
    global $access_token_lifespan;
    global $refresh_token_lifespan;
    $issued_at = time();
    $at_expiration_time = $issued_at + $access_token_lifespan;
    $rt_expiration_time = $issued_at + $refresh_token_lifespan;

    $access_token_data = [
        'iat' => $issued_at,
        'exp' => $at_expiration_time,
        'user' => $user
    ];

    $refresh_token_data = [
        'iat' => $issued_at,
        'exp' => $rt_expiration_time,
        'user' => $user
    ];


    $access_token = JWT::encode($access_token_data, $jwt_key, 'HS256');
    $refresh_token = JWT::encode($refresh_token_data, $jwt_key, 'HS256');

    $response = [
        'access_token' => $access_token,
        'refresh_token' => $refresh_token,
        'access_token_expires_in' => $access_token_lifespan / (60 * 60) . "h", // 8hrs
        'refresh_token_expires_in' => $refresh_token_lifespan / (60 * 60 * 24) . "d", // 7 days
        'token_type' => 'Bearer',
        'scope' => 'read write', // Adjust as needed
        'user' => [
            'group' => $user['user_group'],
            'branch' => $user['branch'],
            'name' => $user['username'],
            'email' => $user['email'],
        ]
    ];

    return $response;
}

// Verify Bearer Token
function verifyBearerTokenFromHeaders($jwt_key)
{
    $allHeaders  = getallheaders();
    if (isset($allHeaders['Authorization']) && preg_match('/Bearer\s(\S+)/', $allHeaders['Authorization'], $matches)) {
        $jwt = $matches[1]; // Extract Bearer token
        try {
            $payload = JWT::decode($jwt, new Key($jwt_key, 'HS256'));

            // Token is valid, you can access $decoded for user information
            // Perform additional checks if needed

            return $payload;
        } catch (Exception $e) {
            // Token is invalid
            return null;
        }
    } else {
        // Bearer token not found in Authorization header
        return null;
    }
}

function sendApiResponse($statusCode, $message, $status = "FAILED", $payload = null)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');

    $response = [
        "status" => $status,
        "message" => $message,
        "payload" => $payload
    ];

    echo json_encode($response);
    exit;
}

function store_event_return_void($tbl, $fld, $event_details)
{
    global $fulldate;
    $ses = session_details();
    $event_by = $ses['uid'] ?? 0;
    $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
    $vals = array("$tbl", "$fld", "$event_details", "$fulldate", "$event_by", "1");
    addtodb('o_events', $fds, $vals);
}

function sendApiResponse2($statusCode, $count, $status = "FAILED", $payload = null)
{
    http_response_code($statusCode);
    header('Content-Type: application/json');

    $response = [
        "status" => $status,
        "count" => $count,
        "payload" => $payload
    ];

    return exit(json_encode($response));
}


function getJwtSignature($jwt)
{
    // Split the JWT into parts
    $jwtParts = explode('.', $jwt);

    if (count($jwtParts) !== 3) {
        throw new Exception("Invalid JWT");
    }

    // The signature is the third part (index 2)
    return $jwtParts[2];
}

function generateToken2($userid, $token, $device_id, $browser_name, $IPAddress, $OS)
{
    global $fulldate;
    global $one_session; ////Login to only one session

    $userid = intval($userid);

    // echo "User ID: $userid <br> Device ID: $device_id <br> Browser: $browser_name <br> IP: $IPAddress <br> OS: $OS <br> Date: $fulldate <br>";

    $token_expiry = dateadd($fulldate, 0, 0, 30); ///one month
    /////Remove other tokens for the user
    if ($one_session == 1) {
        $cleartokens = updatedb('o_tokens', "status=2, expiry_date='$fulldate'", "userid=$userid AND status=1");
    }
    // echo "Token: $token";
    $fds = array("userid", "token", "creation_date", "expiry_date", "device_id", "browsername", "IPAddress", "OS", "status");
    $vals = array($userid, "$token", "$fulldate", "$token_expiry", "$device_id", "$browser_name", "$IPAddress", "$OS", "1");

    // echo "vals";
    // var_dump($vals);

    $create = addtodb("o_tokens", $fds, $vals);
    return "Create: $create";
}
