<?php 

$expected_http_method = 'GET';
include_once ("../../secure-endpoint.php");

$data = json_decode(file_get_contents('php://input'), true);
// $customer_id = intval($data["customer_id"]);

?>