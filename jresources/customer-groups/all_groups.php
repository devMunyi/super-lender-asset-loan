<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();

$where_ = $_POST['where_'];
$offset_ = $_POST['offset'];
$rpp_ = $_POST['rpp'];
$page_no = $_POST['page_no'];
$orderby = $_POST['orderby'];
$dir = $_POST['dir'];
$search_ = trim($_POST['search_']);



$limit = "$offset_, $rpp_";
$offset_2 = $offset_ + $rpp_;
$limit2 = $offset_ + $rpp_;
$rows = "";



if ((input_available($search_)) == 1) {
    $andsearch = " AND (group_name LIKE \"%$search_%\" OR group_description LIKE \"%$search_%\")";
} else {
    $andsearch = "";
}
$flag_names = table_to_obj('o_flags', "uid>0", "100", "uid", "name");
$flag_codes = table_to_obj('o_flags', "uid>0", "100", "uid", "color_code");
//-----------------------------Reused Query
$o_groups = fetchtable('o_customer_groups', "$where_ AND status >= 1 $andsearch", "$orderby", "$dir", "$limit", "*");
///----------Paging Option
$alltotal = countotal_withlimit("o_customer_groups", "$where_ AND status > 0 $andsearch", "uid", "1000");
///==========Paging Option
$group_members_array = array();
$group_loans_array = array();

$group_members = fetchtable('o_group_members',"status=1","uid","asc","1000000","group_id, customer_id");
while($g = mysqli_fetch_array($group_members)){
    $group_id = $g['group_id'];
    $customer_id = $g['customer_id'];
    $group_members_array = obj_add($group_members_array, $group_id, 1);

}

$group_loans = fetchtable('o_loans',"group_id > 0 AND loan_type=3 AND disbursed=1 AND paid=0 AND status!=0","uid","asc","1000000","group_id");
while($l = mysqli_fetch_array($group_loans)){
    $group_id = $l['group_id'];
    $group_loans_array = obj_add($group_loans_array, $group_id, 1);
}

$branches = table_to_obj("o_branches", "status=1", "1000", "uid", "name");

if ($alltotal > 0) {
    while ($g = mysqli_fetch_array($o_groups)) {


        $uid = $g['uid']; $euid = encurl($uid);
        $group_name = $g['group_name'];
        $group_branch = $branches[$g['branch']] ?? "";
        $group_description = $g['group_description'];
        $total_members = false_zero($group_members_array[$uid]);
        $total_loans = false_zero($group_loans_array[$uid]);

        $status = $g['status'];  $state = status($status);
        $act = "<a href=\"?group=$euid\"><i class='fa fa-eye'></i></a>";

        $row.= "<tr><td>$uid</td><td><h4>$group_name</h4></td><td>$total_members</td><td>$total_loans</td><td><h4>$group_branch</h4></td><td>$state</td><td>$act</td></tr>";


    }
} else {
    $row = "<tr><td colspan='6'><i>No Records Found</i></td></tr>";
}

echo trim($row) . "<tr style='display: none;'><td><input type='hidden' id='_alltotal_' value='$alltotal'><input type='hidden' id='_pageno_' value='$page_no'></td></tr>";

include_once("../configs/close_connection.inc");
