<?php
session_start();

include_once("../configs/auth.inc");
include_once '../configs/20200902.php';

include_once("../php_functions/functions.php");


$db = $db_;
$_SESSION['db_name'] = $db;
include_once("../configs/conn.inc");

/////------Customers with more than 50k and are in main product
$customers_to_premium = fetchtable('o_customers',"status=1 AND loan_limit >= 51000 AND primary_product='1'","uid","asc","100","uid");
while($c = mysqli_fetch_array($customers_to_premium)){
    $uid = $c['uid'];
    $up = updatedb('o_customers',"primary_product='4'","uid='$uid'");
    echo "$up";
}
echo "<br/>";

/////------Customers with less than 50k and are in premium product
$customers_to_premium = fetchtable('o_customers',"status=1 AND loan_limit < 51000 AND primary_product='4'","uid","asc","100","uid");
while($c = mysqli_fetch_array($customers_to_premium)){
    $uid = $c['uid'];
    $up = updatedb('o_customers',"primary_product='1'","uid='$uid'");
    echo "$up";
}