<?php
session_start();

include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$from_ = $_GET['from'];
$to_ = $_GET['to'];
$product = $_GET['product_id'];

//////----------------------Adds
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Reports</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>



<div class="container">

    <?php


    $customers = fetchtable('o_customers',"status=1 AND branch!=5","full_name","asc","10000","uid, customer_code, full_name, primary_mobile");
    while($cu = mysqli_fetch_array($customers)){
        $uid = $cu['uid'];
        $primary_mobile = $cu['primary_mobile'];

        echo "$uid $primary_mobile ,<br/>";
        $message_body_conv = "Dear Esteemed Customers, we have automated our systems. To take a loan, send the word LOAN to 24570 and access an instant loan. For queries dial 0739451342";
        $q = queue_message($message_body_conv, $primary_mobile);
        echo $q;
    }

    ?>
</div>

</body>
</html>

