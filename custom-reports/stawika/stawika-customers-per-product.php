<?php
session_start();

$_SESSION['db_name'] = 'stawika_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$products = table_to_obj('o_loan_products',"status=1","100","uid","name");
$total_customers_per_product = array();

$all_loans = fetchtable('o_loans',"disbursed=1 AND given_date >= '2022-11-22'","uid","asc","1000000","distinct customer_id, product_id");
while($l = mysqli_fetch_array($all_loans)){
    $cust = $l['customer_id'];
    $product_id = $l['product_id'];

    echo "$cust $product_id <br/>";

    $total_customers_per_product = obj_add($total_customers_per_product, $product_id, 1);

}

foreach($total_customers_per_product as $pro => $customers) {

    echo $products[$pro].':'.$customers.'<br/>';

}
