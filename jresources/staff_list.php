<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$where_ =  $_POST['where_'] ?? '';
$offset_ =  $_POST['offset'] ?? 0;
$rpp_ =  $_POST['rpp'] ?? 10;
$page_no = $_POST['page_no'] ?? 1;
$orderby =  $_POST['orderby'] ?? "uid";
$dir =  $_POST['dir'] ?? "DESC";
$search_ = sanitizeAndEscape($_POST['search_'] ?? '', $con);


$limit = "$offset_, $rpp_";
$rows = "";

if((input_available($search_)) == 1) {
    $branch_array = array();
    $branch_ = fetchtable("o_branches", "name LIKE \"$search_%\"", "uid", "asc", "20", "uid");
    $branch_count = mysqli_num_rows($branch_);
    if ($branch_count > 0) {
        while ($branch_list = mysqli_fetch_array($branch_)) {
            $branch_id = $branch_list['uid'];
            array_push($branch_array, $branch_id);
        }
        $cust_branch_list = implode(", ", $branch_array);
        $orstaffbranch = " OR `branch` IN ($cust_branch_list)";
    }
}

$groups = array();
$user_groups = fetchtable('o_user_groups',"uid > 0","uid","asc","100","uid, name");
while($ug = mysqli_fetch_array($user_groups)){
    $gr = $ug['uid'];
    $gname = $ug['name'];
    $groups[$gr] = $gname;
}


if((input_available($search_)) == 1){
    $andsearch = " AND (name LIKE \"%$search_%\" OR email LIKE \"%$search_%\" OR national_id = \"$search_\" OR phone LIKE \"%$search_%\" $orstaffbranch)";
}
else{
    $andsearch = "";
}

// overcome n+1 problem by removing select inside a loop
$branch_names = table_to_obj("o_branches", "uid > 0", "10000", "uid", "name");
$staffStatusDet = table_to_obj2('o_staff_statuses', "uid > 0", 10000, "uid", array('name', 'color'));


//-----------------------------Reused Query
$o_users_ = fetchtable('o_users',"$where_ AND 1=1 $andsearch", "$orderby", "$dir", "$limit", "uid ,name ,email ,phone ,join_date ,user_group ,branch ,status, national_id");
///----------Paging Option
$alltotal = countotal_withlimit("o_users","$where_ AND 1=1 $andsearch", "uid", "1000");
///==========Paging Option

if ($alltotal > 0) {
while($c = mysqli_fetch_array($o_users_))
{
    $uid = $c['uid'];    $encstaff = encurl($uid);
    $name = $c['name'];
    $national_id = $c['national_id'];
    $email = $c['email'];
    $phone = $c['phone'];
    $join_date = $c['join_date'];
    $user_group = $c['user_group'];
    $branch = $c['branch'];
    $status = $c['status'];


    if($branch > 0) {
        $branch_name = $branch_names[$branch];  
    }
    else{
        $branch_name = "<i>No Branch</i>";
    }

    $status_name = $staffStatusDet[$status]['name'] ?? '';
    $state_col = $staffStatusDet[$status]['color'] ?? '';

    $row.=" <tr><td>$uid</td><td><span class='font-16'>$name </td><td>$national_id</td><td><span>$email </span></td>
 <td><span>$phone</span></td><td>".$groups[$user_group]."</td><td><span>$branch_name</span></td>
 <td><span>$join_date</span></td>
 <td><span class = 'label $ ".$state_col."'>$status_name </span></td><td><span><a href='?staff=$encstaff'><span class='fa fa-eye text-green'></span></a></span></td></tr>";
}

}else {
    $row = "<tr><td colspan='10'><i>No Records Found</i></td></tr>";
}

echo  trim($row) . "<tr style='display: none;'><td><input type='hidden' id='_alltotal_' value='$alltotal'><input type='hidden' id='_pageno_' value='$page_no'></td></tr>";
include_once ("../configs/close_connection.inc");