<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");


$where_ =  $_POST['where_'] ?? '';
$offset_ =  $_POST['offset'] ?? 0;
$rpp_ =  $_POST['rpp'] ?? 10;
$page_no = $_POST['page_no'] ?? 1;
$orderby =  $_POST['orderby'] ?? "uid";
$dir =  $_POST['dir'] ?? "DESC";
$search_ = trim($_POST['search_'] ?? "");
$camp_id = intval($_POST['camp_id']);

$camp_ = fetchonerow("o_campaigns", "uid = '$camp_id'", "status, target_customers");
$camp_status = $camp_["status"];
$camp_target_audience = $camp_["target_customers"];


$limit = "$offset_, $rpp_";
$offset_2 = $offset_ + $rpp_;
$limit2 = $offset_ + $rpp_;
$rows = "";




if ((input_available($search_)) == 1) {
    //----Search Filters
    $branch_array = array();
    $branch_ = fetchtable2("o_branches", "name LIKE \"%$search_%\"", "uid", "asc", "uid");
    $branch_count = mysqli_num_rows($branch_);
    if ($branch_count > 0) {
        while ($branch_list = mysqli_fetch_array($branch_)) {
            $branch_id = $branch_list['uid'];
            array_push($branch_array, $branch_id);
        }
        $cust_branch_list = implode(", ", $branch_array);
        $orcustbranch = " OR `branch` IN ($cust_branch_list)";
    }

    //-----End Search Filters

    $andsearch = " AND (uid = \"$search_\" OR full_name LIKE \"%$search_%\" OR primary_mobile = \"$search_\" OR branch LIKE \"%$search_%\" $orcustbranch )";
} else {
    $andsearch = "";
}

//-----------------------------Reused Query
$customer_list = array();

$dob_curdate = substr($date, 5);

/////-------Check if audience is a file or a category
if (is_numeric($camp_target_audience) && $camp_target_audience > 0) {
    /////-----Its a custom list not a file
    if ($camp_target_audience == 1) {
        ///---All Defaulters
        $customer_list = table_to_array('o_loans', "status=7", "100000", "account_number");
    } elseif ($camp_target_audience == 4) {
        ////---All with active loans, No default
        $customer_list = table_to_array('o_loans', "status=3", "100000", "account_number");
    } elseif ($camp_target_audience == 5) {
        ///---- All leads & active customers
        $customer_list = table_to_array('o_customers', "status IN (1, 3)", "100000", "primary_mobile");
    } else {
        echo "<tr><td colspan='5'>" . errormes("Invalid audience") . "</td></tr>";
    }
} else {
    $doc = "../../campaign_uploads/$camp_target_audience";
    if (file_exists($doc)) {
        // echo "<tr><td>The file $doc exists</td></tr>";
    } else {
        echo "<tr><td colspan='3'>The file $doc does  not exists</td></tr>";
        die();
    }

    $open  = fopen($doc, "r");
    if ($open !== false) {
        while (($data = fgetcsv($open, 1000000, ",")) !== FALSE) {
            $phone = $data[0];
            array_push($customer_list, $phone);
        }
        fclose($open);
    } else {
        exit(errormes("Unable to open the file: $doc"));
    }
}

$customers_ = implode(',', $customer_list);
$andlist = " AND primary_mobile in ($customers_)";






$o_customers_ = fetchtable("o_customers", "$where_ AND status IN (1, 3) $andsearch $andlist", "$orderby", "$dir", "$limit", "uid ,primary_mobile, full_name, dob, branch, status");

$alltotal = countotal_withlimit("o_customers", "$where_ AND status IN (1, 3) $andsearch $andlist", "uid", '1000');
$branch_names = table_to_obj("o_branches", "uid > 0", "1000", "uid", "name");
$customer_status_names = table_to_obj2("o_customer_statuses", "code > 0", "100", "code", ["name", "color"]);



if ($alltotal > 0) {
    while ($l = mysqli_fetch_array($o_customers_)) {
        $uid = $l['uid'];
        $uid_enc = encurl($uid);
        $full_name = $l['full_name'];
        $phone_number = $l['primary_mobile'];
        $branch = $l['branch'];
        $branch_name = $branch_names[$branch] ?? "";
        $status = $l['status'];
        $state_name = $customer_status_names[$status]['name'] ?? "";
        $state_color = $customer_status_names[$status]['color'] ?? "";
        $dob = $l['dob'] ?? "";


        $row .= "<tr><td>$uid</td>
                                <td><span>$full_name</span><br/> <span class='text-muted font-13 font-bold'>$phone_number</span>
                                </td>
                                <td><span>$branch_name</span></td>
                                <td><span class='label " . $state_color . "'>" . $state_name . " </span></td>
                                <td><span><a href='customers?customer=$uid_enc'><span class='fa fa-eye text-green'></span></td>
                            </tr>                    ";
    }
} else {
    $row = "<tr><td colspan='8'><i>No Records Found</i></td></tr>";
}

echo   trim($row) . "<tr style='display: none;'><td><input type='text' id='_alltotal_' value='$alltotal'><input type='text' id='_pageno_' value='$page_no'><input type='text' id ='_dob_' value = '$dob'></td></tr>";
