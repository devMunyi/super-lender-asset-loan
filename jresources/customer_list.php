<?php
session_start();

include_once("../php_functions/functions.php");
include_once("../php_functions/secondary-functions.php");
include_once("../configs/conn.inc");

$userd = session_details();
$where_ =  $_POST['where_'] ?? '';
$offset_ =  $_POST['offset'] ?? 0;
$rpp_ =  $_POST['rpp'] ?? 10;
$page_no = $_POST['page_no'] ?? 1;
$orderby =  $_POST['orderby'] ?? "uid";
$dir =  $_POST['dir'] ?? "DESC";
$search_ = sanitizeAndEscape($_POST['search_'] ?? '', $con);
$type = $_POST['type'] ?? '';

$limit = "$offset_, $rpp_";
$rows = "";
$alltotal = 0;
$noRows = noRowSpan(9);

function customerSearchCondition($search_)
{

    global $cc;
    $searchWhereCondition = "";
    if (is_numeric($search_)) {
        // check if is a phone number & validate it
        $validated_phone = make_phone_valid($search_);
        if (validate_phone($validated_phone) == 1) {
            $searchWhereCondition = "(primary_mobile = '$validated_phone')";
        } else {
            if ($cc == 256) {
                $searchWhereCondition = "(uid = '$search_')";
            } else {
                $searchWhereCondition = "(uid = '$search_' OR national_id = '$search_')";
            }
        }
    } else {

        // check if contains mix of letters and numbers (uganda national_id case)
        if (preg_match('/[A-Za-z].*[0-9]|[0-9].*[A-Za-z]/', $search_) && $cc == 256) {
            $searchWhereCondition = " (national_id = '$search_')";
        }
        // check if search has @ symbol to search  by email
        elseif (strpos($search_, '@') !== false) {
            $searchWhereCondition = " (email_address = '%$search_%')";
        } else {
            // search by full_name
            $searchWhereCondition = " (full_name LIKE '%$search_%')";
        }
    }


    return $searchWhereCondition;
}

// $branchCondition = getBranchCondition($userd, 'o_customers');
// if ($type == 'LEAD' && $branchCondition != "") {
//     $branchCondition = getBranchCondition($userd, 'o_customer_statuses', 'branch', 'customerBranches', 3, 'read_');
// }

if ($type == 'LEAD') {
    // show all leads to all users
    $branchCondition = "";
} else {
    // show only leads from the user's branch
    $branchCondition = getBranchCondition($userd, 'o_customers');
}

$branchUserCondition = $branchCondition['branchUserCondition'] ?? $branchCondition['customerBranches'] ?? '';
$whereCondition = "$where_ $branchUserCondition";

// if there is search input
if ((input_available($search_)) == 1) {
    // echo "searching...<br>";
    $searchCondition = customerSearchCondition($search_);
    $whereCondition = "$whereCondition AND $searchCondition";
} else {
    // echo "no search input<br>";
    $searchCondition = "";
}

if (empty($searchCondition) && !empty($search_)) {
    $rows = "$noRows";
} else {
    $agent_names = table_to_obj2('o_users', "uid > 0", 10000, "uid", array('name', 'tag'));
    $flagsDetails = table_to_obj2('o_flags', "uid>0", "100", "uid", array('name', 'color_code'));
    //---badges names =
    $badgesDetails = table_to_obj2('o_badges', "uid>0", "30", "uid", array('title', 'icon'));

    $towns_array = table_to_obj('o_towns', "uid > 0", "1000", "uid", "name");
    $branches_array = table_to_obj('o_branches', "uid>0", "1000", "uid", "name");
    $products_array = table_to_obj('o_loan_products', "uid > 0", "100", "uid", "name");

    $customerStatusDetails = table_to_obj2("o_customer_statuses", "uid>0", 100, "code", array('name', 'color'));


    $customers_list = implode(',', table_to_array('o_customers', "$whereCondition AND status >= 1", "$limit", "uid", "$orderby", "$dir"));

    $status_det = table_to_obj2('o_loan_statuses', "uid>0", "100", "uid", ["name", "color_code"]);

    $latest_loan = array();

    $profile_photos = table_to_obj('o_documents', "rec IN ($customers_list) AND category=1 AND tbl='o_customers' AND status=1", "1000", "rec", "stored_address");


    //echo $customers_list."kdkdkd";
    $latest_loans = fetchtable('o_loans', "customer_id in ($customers_list) AND disbursed=1 AND paid=0 AND status!=0", "uid", "desc", "100", "customer_id, loan_amount, loan_balance, final_due_date, status");
    while ($ll = mysqli_fetch_array($latest_loans)) {
        $customer_id = $ll['customer_id'];
        $loan_amount = $ll['loan_amount'];
        $loan_balance = $ll['loan_balance'];
        $final_due_date = $ll['final_due_date'];
        $loan_status = $ll['status'];
        $status_name = $status_det[$loan_status]['name'];
        $state_color = $status_det[$loan_status]['color_code'];
        //echo $loan_amount.',';
        if ($loan_balance > 1) {
            $latest_loan[$customer_id] = "<span>Bal: <b>" . number_format($loan_balance) . "<span class='label' style='background-color: $state_color;'>$status_name</span></b></span><br/><span>DD: <b>" . $final_due_date . "</b></span>";
            //echo $latest_loan[$customer_id];
        } else {
            // $latest_loan = "";
        }
    }

    //-----------------------------Reused Query
    $o_customers_ = fetchtable('o_customers', "$whereCondition AND status >= 1", "$orderby", "$dir", "$limit", "uid ,full_name ,primary_mobile ,email_address ,physical_address, town ,passport_photo ,national_id ,gender ,dob ,added_by ,added_date ,branch ,primary_product ,loan_limit ,events, flag, badge_id,total_loans ,status, current_agent");
    ///----------Paging Option
    $alltotal = countotal_withlimit("o_customers", "$whereCondition AND status > 0", "uid", "10000");
    ///==========Paging Option

    if ($alltotal > 0) {
        while ($l = mysqli_fetch_array($o_customers_)) {
            $uid = $l['uid'];
            $uid_enc = encurl($uid);
            $full_name = $l['full_name'];
            $primary_mobile = $l['primary_mobile'];
            $email_address = $l['email_address'];
            $physical_address = $l['physical_address'];
            $town = $l['town'];
            $town_name = $towns_array[$town] ?? '';
            $passport_photo = $l['passport_photo'] ?? '';
            $national_id = $l['national_id'];
            $gender = $l['gender'];
            $dob = $l['dob'];
            $added_by = $l['added_by'];
            $current_agent = $l['current_agent'];
            $current_agent_arr = $agent_names[$current_agent] ?? [];
            $agent_name = $current_agent_arr['name'] ?? "Unspecified";
            $agent_role = $current_agent_arr['tag'] ?? "Unspecified";
            $added_date = $l['added_date'];
            $branch = $l['branch'];
            $branch_name = $branches_array[$branch] ?? '';
            $primary_product = $l['primary_product'];
            $primary_product_name = $products_array[$primary_product] ?? '';
            $loan_limit = $l['loan_limit'];
            $events = $l['events'];
            $flag = $l['flag'];
            $badge_id = $l['badge_id'];
            $total_loans = $l['total_loans'];
            $status = $l['status'];
            $customerStatusColor = $customerStatusDetails[$status]['color'] ?? '';
            $customerStatusName = $customerStatusDetails[$status]['name'] ?? '';

            if ($total_loans == 1) {
                $total_loans_taken = "<span class='label bg-info text-navy font-bold font-13'>$total_loans loan</span> ";
            } elseif ($total_loans > 1) {
                $total_loans_taken = "<span class='label bg-info text-navy font-bold font-13'>$total_loans Loans</span>";
            } else {
                $total_loans_taken = "";
            }

            if ($flag > 0) {
                $flag_n = $flagsDetails[$flag]['name'] ?? '';
                $flag_c = $flagsDetails[$flag]['color_code'] ?? '';
                $flag_d = "<span><i class='fa fa-flag' style='color: $flag_c;'></i> $flag_n</span>";
            } else {
                $flag_d = "";
            }

            if ($badge_id > 0) {
                $badge_tile = $badgesDetails[$badge_id]['title'] ?? '';
                $badge_icon = $badgesDetails[$badge_id]['icon'] ?? '';
                $badge = "<a title='$badge_tile'><img src=\"badges/$badge_icon\" height='18px'/></a>";
            } else {
                $badge = "";
            }

            $img = "";
            $profile = ""; // Default empty profile
            if (!empty(trim($passport_photo))) {
                $img = "uploads_/thumb_$passport_photo";
            } elseif (isset($profile_photos[$uid])) {
                $img = 'thumb_' . $profile_photos[$uid];

                if ($OBJECT_STORAGE_BUCKET == 1) {
                    $img = $profile_photos[$uid];
                }

                $img = locateImageServer($img);
            }

            if (!empty($img)) {
                $profile = "<img class='thumbnailx' src='$img'>";
            }


            ////-------Lastest Loans Query
            if ($balance == 0.00) {
                $final_due_date = "None";
            }
            $latest_l =  $latest_loan[$uid];
            /////-------End of latest loans query
            $row .= "<tr><td>$uid</td>
                                <td style='padding: 0;'><span>$profile</span></td>
                                <td><span class='font-400'>$badge $full_name</span><br/> <span class='text-muted font-13 font-bold'>$email_address</span>
                                </td>
                                <td><span> <small class='text-muted font-13 font-bold'>Name: </small> $agent_name <br/> <small class='text-muted font-13 font-bold'>Role: </small> $agent_role </span></td>
                                <td><span>$primary_mobile </span><br/> $total_loans_taken</td>
                                <td><span>$branch_name</span><br/> <span class='text-muted font-13 font-bold'>Prod: $primary_product_name</span></td>
                                <td>" . $latest_l . "
                                </td>
                                <td><span>$physical_address</span><br/> <span class='text-muted font-13 font-bold'>$town_name</span></td>
                                <td><span class='label " . $customerStatusColor . "'>" . $customerStatusName . " </span><br/> <span class='text-muted font-13 font-bold'></span> <br/> $flag_d</td>
                                <td><span><a href='?customer=$uid_enc'><span class='fa fa-eye text-green'></span></a></span><h4><a href=\"#\" title='Popup' onclick=\"interactions_popup('" . encurl($uid) . "')\"><i class=\"fa fa-comments-o text-orange\"></i></a></h4> </td>
                            </tr>
                    ";
            $profile = ""; // Make it empty
        }
    } else {
        $row = $noRows;
    }
}

echo   trim($row) . "<tr style='display: none;'><td><input type='hidden' id='_alltotal_' value='$alltotal'><input type='hidden' id='_pageno_' value='$page_no'></td></tr>";

include_once("../configs/close_connection.inc");
