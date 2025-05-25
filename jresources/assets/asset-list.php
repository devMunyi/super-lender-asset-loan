<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");


$where_ = $_POST['where_'] ?? "uid > 0";
$offset_ = $_POST['offset'] ?? 0;
$rpp_ = $_POST['rpp'] ?? 20;
$page_no = $_POST['page_no'] ?? 1;
$orderby = $_POST['orderby'] ?? 'uid';
$dir = $_POST['dir'] ?? 'DESC';
$search_ = trim($_POST['search_']);


$limit = "$offset_, $rpp_";
$offset_2 = $offset_ + $rpp_;
$limit2 = $offset_ + $rpp_;


if ((input_available($search_)) == 1) {
    $andsearch = " AND (`name` LIKE \"%$search_%\" OR `description` LIKE \"%$search_%\")";
} else {
    $andsearch = "";
}

//-----------------------------Reused Query
//default or running campaign(s)
$o_assets = fetchtable("o_assets", "$where_ $andsearch", "$orderby", "$dir", "$limit", "uid, name, photo, description, added_date, selling_price, status");

///----------Paging Option
$alltotal = countotal("o_assets", "$where_ $andsearch");

///==========Paging Option

$row = "<div class=\"row\">";
if ($alltotal > 0) {
    while ($a = mysqli_fetch_array($o_assets)) {
        $uid = $a['uid'];
        $uid_enc = encurl($uid);
        $name = $a["name"];
        $description = $a['description'];
        $added_date = $a["added_date"];
        $photo_src = trim($a["photo"] ?? "");
        if (substr(trim($photo_src), 0, 4) != 'http') {
            $photo_src = $photo_src ? "assets-upload/thumb_" . $photo_src : "dist/img/avatar.png";
        }

        $selling_price = $a["selling_price"]; {
            $row .= "<div class=\"col-md-2\">
            
                <div class=\"box box-default box-solid\">
                <a  href=\"?cat=asset&asset=$uid_enc\">
                    <div style='width: 100% !important; overflow: hidden;'>
                        <div class=\"box-header text-center box-title font-bold font-14 text-black\" style='max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'>$name</div>
                    </div>
                    <div class=\"box-body\">
                        <img src=\"$photo_src\" style='height:200px; width:100%; object-fit:cover; !important;' alt='$$name'>
                    </div>
                    <div class=\"box-footer font-16\"> " . "Ksh. " . money($selling_price) . " 
                    <br/>
                    </a>
                       
                     <a class='btn btn-success pull-right' onclick='cart_add($uid)'><i class='fa fa-cart-plus'></i></a>
                    
                    </div>
                </div>
              
            
        </div>";
        }

        //////------Paging Variable ---
        //$page_total = $page_total + 1;
        /////=======Paging Variable ---


    }
} else {
    $row = "<i>No Records Found</i></div>";
}
$row .= "</div>";

echo trim($row) . "<tr style='display: none;'><td><input type='hidden' id='_alltotal_' value='$alltotal'><input type='hidden' id='_pageno_' value='$page_no'></td></tr>";

