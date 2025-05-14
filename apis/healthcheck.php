<?php

include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");
header('Content-Type: application/json');
http_response_code(200);

$sql = "SELECT c.uid, c.passport_photo AS passportPhoto, c.full_name AS fullName, u.name AS agent, c.email_address AS emailAddress, b.name AS branch, c.physical_address as physicalAddress, cs.name AS `status` FROM o_customers c LEFT JOIN o_users u ON c.current_agent = u.uid LEFT JOIN o_branches b ON b.uid = c.branch LEFT JOIN o_customer_statuses cs ON cs.code = c.status order by c.uid DESC LIMIT 10";

// query result as an associative array
$result = mysqli_query($con, $sql);
$customers = mysqli_fetch_all($result, MYSQLI_ASSOC);

// $totalSQL = "SELECT COUNT(*) as total FROM o_customers limit 1000";
// $totalResult = mysqli_query($con, $totalSQL);
// $total = mysqli_fetch_assoc($totalResult);
// $count = $total['total'];


$count = countotal_withlimit("o_customers", "uid > 0", "*", "1000");
echo json_encode(['status' => 'ok', 'count' => $count, 'customers' => $customers]);
exit;
