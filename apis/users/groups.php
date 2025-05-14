<?php

include_once("../../configs/conn.inc");
include_once("../../php_functions/functions.php");
header('Content-Type: application/json');
http_response_code(200);

$sql = "SELECT `name` FROM o_user_groups WHERE `status` = 1 ORDER BY `name` ASC LIMIT 1000";

// query result as an associative array
$queryExec = mysqli_query($con, $sql);
$result = mysqli_fetch_all($queryExec, MYSQLI_ASSOC);

// $totalSQL = "SELECT COUNT(*) as total FROM o_customers limit 1000";
// $totalResult = mysqli_query($con, $totalSQL);
// $total = mysqli_fetch_assoc($totalResult);
// $count = $total['total'];

echo json_encode(['status' => 'ok', 'groups' => $result]);
exit;
