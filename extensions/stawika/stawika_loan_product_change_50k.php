<?php
////----When a client Limit is a certain amount, move client to a different product
session_start();

$_SESSION['db_name'] = 'stawika_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$customers = fetchtable('o_customers',"loan_limit >= 50000 AND status=1 AND primary_product=1","uid","asc","1000","uid");
while($c = mysqli_fetch_array($customers)){

    $cid = $c['uid'];
    /////-----Update product
    $upd = updatedb('o_customers',"primary_product='9'","uid='$cid'");
    echo $upd;
    store_event('o_customers', "$cid","Customer with 50k limit moved to 15% product by system process");

}
echo "<br/>";
$customers = fetchtable('o_customers',"loan_limit < 50000 AND status=1 AND primary_product=9","uid","asc","1000","uid");
while($c = mysqli_fetch_array($customers)){

    $cid = $c['uid'];
    /////-----Update product
    $upd = updatedb('o_customers',"primary_product='1'","uid='$cid'");
    echo $upd;
    store_event('o_customers', "$cid","Customer with less than 50k limit moved to main product by system process");

}