<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();

$where_ = $_POST['where_'] ?? '';
$offset_ = (int) ($_POST['offset'] ?? 0);
$rpp_ = (int) ($_POST['rpp'] ?? 10);
$page_no = (int) ($_POST['page_no'] ?? 1);
$orderby = "final_due_date";
$dir = "DESC";
$search_ = trim($_POST['search_'] ?? '');

$read_all = permission($userd['uid'], 'o_loans', "0", "read_");

$anduserbranch = "";
$andloanbranch = "";
if ($read_all != 1) {
    $user_branch = $userd['branch'];
    $anduserbranch = " AND branch='$user_branch'";
    $andloanbranch = " AND current_branch='$user_branch'";

    // Check users who view multiple branches
    $staff_branches = table_to_array('o_staff_branches', "agent=" . $userd['uid'] . " AND status=1", "1000", "branch", "uid", "asc");
    if (!empty($staff_branches)) {
        $staff_branches[] = $user_branch; // Add user's branch to the list
        $staff_branches_list = implode(",", array_unique($staff_branches)); // Ensure unique values
        $anduserbranch = " AND branch IN ($staff_branches_list)";
        $andloanbranch = " AND current_branch IN ($staff_branches_list)";
    }
}

$limit = "$offset_, $rpp_";
$row = "";

// Search customers if a search term is provided
$andsearch = "";
if (input_available($search_) == 1) {
    $customers = fetchtable('o_customers', "full_name LIKE '%$search_%' OR primary_mobile = '$search_' OR national_id = '$search_'", "uid", "asc", "$limit", "uid");
    $customer_list = [];
    
    while ($cu = mysqli_fetch_array($customers)) {
        $customer_list[] = $cu['uid'];
    }
    
    if (!empty($customer_list)) {
        $orcustomer = " OR customer_id IN (" . implode(",", $customer_list) . ")";
        $andsearch = " AND (uid = '$search_' OR loan_amount = '$search_' $orcustomer)";
    }
}

// Construct the main query
$query = "$where_ $andloanbranch AND status IN (3, 4) AND final_due_date >= '$date' $andsearch";

$o_loans_ = fetchtable('o_loans', $query, $orderby, $dir, $limit, "uid, customer_id, account_number, product_id, loan_amount, disbursed_amount, period, period_units, payment_frequency, payment_breakdown, total_addons, total_deductions, total_instalments, total_instalments_paid, current_instalment, given_date, next_due_date, final_due_date, added_by, added_date, loan_stage, current_branch, transaction_code, transaction_date, application_mode, total_repaid, loan_balance, current_lo, current_co");

// Paging options
$alltotal = countotal_withlimit("o_loans", $query, "uid", "1000");

$customers_list_array = fetchtable('o_loans', $query, $orderby, $dir, $limit, "customer_id");
$customers_list = [];
while ($cu = mysqli_fetch_array($customers_list_array)) {
    $customers_list[] = $cu['customer_id'];
}

// Prepare necessary data
$customer_names = [];
$customer_badge_det = [];
$customer_flag_det = [];

$cust_ = fetchtable('o_customers', "uid IN (" . implode(',', $customers_list) . ")", 'uid', 'asc', 100, "uid,full_name,badge_id,flag");
while ($cus = mysqli_fetch_array($cust_)) {
    $customer_names[$cus['uid']] = $cus['full_name'];
    $customer_badge_det[$cus['uid']] = $cus['badge_id'];
    $customer_flag_det[$cus['uid']] = $cus['flag'];
}

// Load other related data
$loan_statuses = table_to_obj2('o_loan_statuses', "uid > 0", 50, "uid", ['name', 'color_code']);
$branche_names = table_to_obj('o_branches', "uid > 0", "1000", "uid", "name");
$staff_obj = table_to_obj('o_users', "uid > 0", "100000", "uid", "name");
$flagsDetails = table_to_obj2('o_flags', "uid > 0", "100", "uid", ['name', 'color_code']);
$badgesDetails = table_to_obj2('o_badges', "uid > 0", "30", "uid", ['title', 'icon']);

// Generate table rows
if ($alltotal > 0) {
    while ($n = mysqli_fetch_array($o_loans_)) {
        $customer_id = $n['customer_id'];
        $full_name = $customer_names[$customer_id] ?? "";
        $primary_mobile = $n['account_number'] ?? "";
        $loan_branch = $branche_names[$n['current_branch']] ?? "";
        
        // Prepare flags and badges
        $flag = $customer_flag_det[$customer_id] ?? 0;
        $badge_id = $customer_badge_det[$customer_id] ?? 0;

        $flag_d = $flag > 0 ? "<span><i class='fa fa-flag' style='color: {$flagsDetails[$flag]['color_code']};'></i> {$flagsDetails[$flag]['name']}</span>" : "";
        $badge = $badge_id > 0 ? "<a title='{$badgesDetails[$badge_id]['title']}'><img src=\"badges/{$badgesDetails[$badge_id]['icon']}\" height='18px'/></a>" : "";

        // Construct row
        $row .= "<tr>
                    <td class='font-14 font-bold'>" . encurl($n['uid']) . "</td>
                    <td><span class=\"font-14\">$badge $full_name <a title='View Customer' href=\"customers?customer=" . encurl($customer_id) . "\"><i class=\"fa fa-external-link\"></i></a></span><br/> 
                        <span class=\"text-muted font-13 font-bold\">$primary_mobile</span></td>
                    <td><span class=\"text-bold text-blue font-14\">{$n['loan_amount']}</span><br/> <span class='font-13'>$loan_branch</span></td>
                    <td><span>{$n['total_addons']}</span><br/> <label class=\"label label-default font-13 font-bold\">{$n['count_addons']}</label></td>
                    <td><span>{$n['total_deductions']}</span><br/> <label class=\"label label-default font-13 font-bold\">{$n['count_deductions']}</label></td>
                    <td><span class='text-green'>" . money($n['total_repaid']) . "</span></td>
                    <td><span class=\"font-bold text-red font-16\">" . money($n['loan_balance']) . "</span><br/> 
                        <span class=\"text-muted font-13 font-italic\">Next: {$n['next_due_date']}</span></td>
                    <td><span>{$n['given_date']}</span><br/> <span class=\"text-orange font-13 font-bold\">" . fancydate($n['given_date']) . "</span></td>
                    <td><span>{$n['final_due_date']}</span><br/> <span class=\"text-orange font-13 font-bold\">" . fancydate($n['final_due_date']) . "</span></td>
                    <td><span class=\"text-black\"> <i class='fa text-red fa-user-circle'></i> LO: <b>{$staff_obj[$n['current_lo']]}</b> <br/> <i class='fa text-blue fa-user-circle'></i> CO: <b>{$staff_obj[$n['current_co']]}</b> </span></td>
                    <td><span class='label custom-color' style='background-color: {$loan_statuses[$n['status']]['color_code']};'>{$loan_statuses[$n['status']]['name']}</span><br/> $flag_d</td>
                    <td><span><a href=\"loans?loan=" . encurl($n['uid']) . "\"><span class=\"fa fa-eye text-green\"></span></a>
                        <h4><a href=\"#\" title='Popup' onclick=\"interactions_popup('" . encurl($customer_id) . "')\"><i class=\"fa fa-comments-o text-orange\"></i></a></h4></span></td>
                </tr>";
    }
} else {
    echo "<tr><td colspan='14'><i>No Records Found</i></td></tr>";
}

echo trim($row) . "<tr style='display: none;'><td><input type='hidden' id='_alltotal_' value='$alltotal'><input type='hidden' id='_pageno_' value='$page_no'></td></tr>";
include_once("../../configs/close_connection.inc");
