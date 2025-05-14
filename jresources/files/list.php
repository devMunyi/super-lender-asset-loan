<?php
session_start();
include_once ('../../configs/20200902.php');
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$customer =  $_POST['customer'];
$action = $_POST['action'];


if($customer > 0){
    $o_customer_files = fetchtable('o_documents',"tbl = 'o_customers' AND rec = ".decurl($customer)." AND status = 1", "status", "asc", "100", "uid, title, stored_address, status");
    if((mysqli_num_rows($o_customer_files)) > 0){

        while ($f = mysqli_fetch_array($o_customer_files)) {
            $uid = $f['uid'];
            $title = $f['title'];
            $stored_address = $f['stored_address'];
            $status = $f['status'];

            if($status == 0){
                $tag = "<span class='text-red'> Restored</span>";
            }
            else{
                $tag = "";
            }

            if($action == 'EDIT') {
                $act = "<a href=\"customers?customer-add-edit=$customer&collateral=".encurl($uid)."\" title='Edit' class='pointer text-blue'><i class='fa fa-edit'></i></a> " . "<a onclick=\"delete_collateral('".encurl($uid)."')\" title='Delete' class='pointer text-red'><i class='fa fa-trash'></i></a>"." <a onclick=\"view_collateral('".encurl($uid)."')\" title='View' class='pointer text-green'><i class='fa fa-eye'></i></a>";
            }else{
                $act = "--";
            }

            $img = generateThumbnailUrl(locateImageServer($stored_address));

            if(strtolower(substr($img, -4)) === '.pdf')  //////Is pdf
            {
                $img = "custom_icons/pdf128.png";
            }

            $_row.= "<a id='fil".encurl($uid)."' class='pointer' onclick=\"view_file('".encurl($uid)."','EDIT');\"><span class='image-card' >$tag <img src=\"$img\" class=\"img-thumbnail\" width=100 height=100 alt=\"$title\"></span></a>";
        }
    }
    else{
        $_row = "No Files";
    }
    echo "$_row";
}
else{
    echo errormes("Customer not selected");
}
include_once ("../../configs/close_connection.inc");