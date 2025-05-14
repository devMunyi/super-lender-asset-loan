<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");


$where_ =  $_POST['where_'];
$offset_ =  $_POST['offset'];
$rpp_ =  $_POST['rpp'];
$page_no = $_POST['page_no'];
$orderby =  $_POST['orderby'];
$dir =  $_POST['dir'];
$search_ = sanitizeAndEscape($_POST['search_'], $con);


$limit = "$offset_, $rpp_";
$offset_2 = $offset_ + $rpp_;
$limit2 = $offset_ + $rpp_;
$rows = "";


if ((input_available($search_)) == 1) {
    $andsearch = " AND (name LIKE \"$search_%\")";
} else {
    $andsearch = "";
}

$region_names = table_to_obj("o_regions", "uid > 0", "1000", "uid", "name");
$branch_status_names = table_to_obj2('o_branch_statuses', "uid > 0",10 ,"uid", array('name', 'color'));

//-----------------------------Reused Query
$o_branch_ = fetchtable('o_branches', "$where_ AND status > 0 $andsearch", "$orderby", "$dir", "$limit", "uid ,name, region_id, address, added_date, status, freeze");
///----------Paging Option
$alltotal = countotal("o_branches", "$where_ AND status > 0 $andsearch");
///==========Paging Option

if ($alltotal > 0) {
    while ($b = mysqli_fetch_array($o_branch_)) {
        $uid = $b['uid'];
        $encbranch = encurl($uid);
        $name = $b['name'];
        $region_id = $b['region_id'];
        $address = $b['address'] && trim($b['address']) > 0 ? $b['address'] : "<i>Unspecified</i>";
        $added_date = $b['added_date'];
        $status = $b['status'];


        if ($region_id > 0) {
            $region_name = $region_names[$region_id];
        } else {
            $region_name = "<i>Unspecified</i>";
        }

        $branch_status_arr = $branch_status_names[$status];
        $status_name = $branch_status_arr['name'] ?? "";
        $freeze = $b['freeze'] ?? "NONE";
        if($freeze == 'API'){
            $freeze = "API Loans";
        }elseif($freeze == 'BOTH'){
            $freeze = "API & MANUAL Loans";
        }elseif($freeze == 'MANUAL') {
            $freeze = "Manual Loans";
        }

        $state_col = $branch_status_arr['color'] ?? "";

        $row .= " <tr><td>$uid</td><td><span class='font-16'>$name </td>
 <td><span>$region_name</span></td>
 <td>$address</td>
 <td><span>$added_date</span></td>
 <td><span class = 'label $ " . $state_col . "'>$status_name </span></td><td>$freeze</td><td><span><a href='?branch=$encbranch'><span class='fa fa-eye text-green'></span></a></span></td></tr>";

        //////------Paging Variable ---
        //$page_total = $page_total + 1;
        /////=======Paging Variable ---


    }
} else {
    $row = "<tr><td colspan='7'><i>No Records Found</i></td></tr>";
}

echo  trim($row) . "<tr style='display: none;'><td><input type='hidden' id='_alltotal_' value='$alltotal'><input type='hidden' id='_pageno_' value='$page_no'></td></tr>";

include_once("../configs/close_connection.inc");
