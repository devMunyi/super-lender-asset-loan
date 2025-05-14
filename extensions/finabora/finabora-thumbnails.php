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
    ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

    $upload_location = '../uploads_/';
    $stored_address = 'IvdcjpVhizWIcnrGTu1TCiC06.png';


    $file_name_only = pathinfo($stored_address, PATHINFO_FILENAME);
    makeThumbnails($upload_location, $stored_address, 100, 100, "thumb_".$file_name_only);
    echo $file_name_only;

    $customers = fetchtable('o_documents',"status=1","uid","asc","100000","uid, stored_address");
    while($cu = mysqli_fetch_array($customers)){
        $uid = $cu['uid'];
        $stored_address = $cu['stored_address'];


        $file_name_only = pathinfo($stored_address, PATHINFO_FILENAME);
        echo "$file_name_only <br/>";
           makeThumbnails($upload_location, $stored_address, 100, 100, "thumb_".$file_name_only);


    }

    ?>
</div>

</body>
</html>

