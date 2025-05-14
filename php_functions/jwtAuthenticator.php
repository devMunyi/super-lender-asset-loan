<?php 

// check access token validity
$payload = verifyBearerTokenFromHeaders($jwt_key);

if ($payload === null) {
    sendApiResponse(401, "Access token missing or invalid");
}

$user = $payload->user;
$added_by = intval($user->uid);
$user_type = $user->user_type ? $user->user_type : '';