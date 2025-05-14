<?php
$expected_http_method = 'GET';
include_once("../../configs/cors.php");
include_once("../../vendor/autoload.php");
include_once("../../configs/conn.inc");
include_once("../../configs/jwt.php");
include_once("../../php_functions/jwtAuthUtils.php");
include_once("../../php_functions/jwtAuthenticator.php");
include_once("../../php_functions/functions.php");

$limit = $_GET['limit'] ?? 1000;
$fetchBranches = fetchtable("o_branches", "status=1", "uid", "desc", "$limit ", "uid, name, status");

$payload = array();
$branches_count = 0;

while ($p = mysqli_fetch_assoc($fetchBranches)) {

    $one_branch = array();
    $one_branch['uid'] = $p['uid'];
    $one_branch['name'] = $p['name'];
    $one_branch['status'] = $p['status'];

    array_push($payload, $one_branch);
    $branches_count += 1;
}


sendApiResponse(200, "OK", "Success", $payload);