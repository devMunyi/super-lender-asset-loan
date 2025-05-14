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
$productsFetch = fetchtable("o_loan_products", "status=1", "uid", "desc", "$limit ", "uid, name, status");

$payload = array();
$products_count = 0;

while ($p = mysqli_fetch_assoc($productsFetch)) {

    $one_product = array();
    $one_product['uid'] = $p['uid'];
    $one_product['name'] = $p['name'];
    $one_product['status'] = $p['status'];

    array_push($payload, $one_product);
    $products_count += 1;
}


sendApiResponse(200, "OK", "Success", $payload);
