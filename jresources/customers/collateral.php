<?php

session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();

$customer_id = $_POST['customer_id'] ?? 0;

if ($customer_id == 0) {
    echo "<div class='row'><span class='font-18 font-italic text-black text-mute'>Customer id was not parsed!</span></div>";
    exit();
} else {
    $customer_id = decurl($customer_id);
}

$o_collateral_ = fetchtable("o_collateral", "status > 0 AND customer_id=$customer_id", "uid", "desc", "0,10", "uid ,category ,title ,description ,money_value ,document_scan_address ,doc_reference_no ,filling_reference_no ,added_date ,added_by ,status ");
$category_names = table_to_obj("o_asset_categories", "uid > 0", "1000", "uid", "name");
$collateral_statuses = table_to_obj("o_collateral_statuses", "uid > 0", "1000", "uid", "name");
if (mysqli_num_rows($o_collateral_) > 0){
    while ($i = mysqli_fetch_array($o_collateral_)) {
        $uid = $i['uid'];
        $category = $i['category'];
        $category_name = $category_names[$category] ?? "";
        $title = $i['title'];
        $description = $i['description'];
        $money_value = $i['money_value'];
        $document_scan_address = $i['document_scan_address'];
        $doc_reference_no = $i['doc_reference_no'];
        $filling_reference_no = $i['filling_reference_no'];
        $added_date = $i['added_date'];
        $added_by = $i['added_by'];
        $status = $i['status'];
        $name = $collateral_statuses[$status] ?? "";


        echo "<tr><td>$category_name</td><td>$title</td><td>$description</td><td>$money_value</td><td>$doc_reference_no</td><td>$filling_reference_no</td><td>$added_date</td><td>$name</td></tr>";
    }
} else {
    echo "<tr><td colspan='8'><i>No Records Found</i></td></tr>";
}

// include close connection
include_once("../../configs/close_connection.inc");
?>
