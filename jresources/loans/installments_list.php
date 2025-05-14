<?php
// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session and include necessary files
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

// Fetch user details
$userDetails = session_details();

// Validate and sanitize POST inputs
$where = isset($_POST['where_']) ? $_POST['where_'] : '';
$offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
$rpp = isset($_POST['rpp']) ? intval($_POST['rpp']) : 10;
$page_no = isset($_POST['page_no']) ? intval($_POST['page_no']) : 1;
$orderby = "final_due_date";
$direction = "DESC";
$search = isset($_POST['search_']) ? sanitizeAndEscape($_POST['search_'], $con) : '';
$sort_option = isset($_POST['sort_option']) ? $_POST['sort_option'] : 'today';

// Calculate limit for SQL queries
$limit = "$offset, $rpp";

// Permission check
$readAll = permission($userDetails['uid'], 'o_loans', "0", "read_");

$andUserBranch = "";
$andLoanBranch = "";
$staffBranches = array();

if ($readAll != 1) {
    $userBranch = mysqli_real_escape_string($con, $userDetails['branch']);
    $andUserBranch = " AND branch='$userBranch'";
    $andLoanBranch = " AND current_branch='$userBranch'";

    // Check if user can view multiple branches
    $staff_branches_query = "SELECT branch FROM o_staff_branches WHERE agent={$userDetails['uid']} AND status=1 LIMIT 1000";
    $staff_branches_result = mysqli_query($con, $staff_branches_query);

    if ($staff_branches_result && mysqli_num_rows($staff_branches_result) > 0) {
        while ($row = mysqli_fetch_assoc($staff_branches_result)) {
            $staffBranches[] = intval($row['branch']);
        }
        mysqli_free_result($staff_branches_result);

        if (!empty($staffBranches)) {
            $branchesList = implode(",", $staffBranches);
            $andUserBranch = " AND branch IN ($branchesList)";
            $andLoanBranch = " AND current_branch IN ($branchesList)";
        }
    }
}

// Build search condition
$andSearch = "";
$orCustomer = "";

if (!empty($search)) {
    $custArray = array();
    $searchEscaped = mysqli_real_escape_string($con, $search);
    $searchLike = "%$searchEscaped%";

    $customers_query = "SELECT uid FROM o_customers WHERE (full_name LIKE '$searchLike' OR primary_mobile = '$searchEscaped' OR national_id = '$searchEscaped') $andUserBranch LIMIT 1000";
    $customers_result = mysqli_query($con, $customers_query);

    if ($customers_result && mysqli_num_rows($customers_result) > 0) {
        while ($cu = mysqli_fetch_assoc($customers_result)) {
            $custArray[] = intval($cu['uid']);
        }
        mysqli_free_result($customers_result);

        if (!empty($custArray)) {
            $customerList = implode(",", $custArray);
            $orCustomer = " OR customer_id IN ($customerList)";
        }
    }

    $searchEscaped = mysqli_real_escape_string($con, $search);
    $loanAmountEscaped = mysqli_real_escape_string($con, $search);
    $andSearch = " AND (uid = '$searchEscaped' OR loan_amount = '$loanAmountEscaped' $orCustomer)";
}

// Determine date-based filters
$date = date('Y-m-d'); // Assuming $date is today's date. Adjust as needed.

switch ($sort_option) {
    case "past":
        $past = datesub($date, 0, 0, 1);
        $filterCondition = " AND (next_due_date <= '$past' OR final_due_date <= '$past')";
        break;
    case "today":
        $filterCondition = " AND (next_due_date = '$date' OR final_due_date = '$date')";
        break;
    case "tomorrow":
        $tomorrow = dateadd($date, 0, 0, 1);
        $filterCondition = " AND (next_due_date = '$tomorrow' OR final_due_date = '$tomorrow')";
        break;
    case "upcoming":
        $upcoming = dateadd($date, 0, 0, 1);
        $filterCondition = " AND (next_due_date >= '$upcoming' OR final_due_date >= '$upcoming')";
        break;
    case "yesterday":
        $yesterday = datesub($date, 0, 0, 1);
        $filterCondition = " AND (next_due_date = '$yesterday' OR final_due_date = '$yesterday')";
        break;
    default:
        // Defaults to today
        $filterCondition = " AND (next_due_date = '$date' OR final_due_date = '$date')";
        break;
}

// Fetch loans based on sort option and filters
$loans_query = "SELECT `uid`, customer_id, account_number, current_branch, given_date, next_due_date, final_due_date, `period`, period_units, payment_frequency, loan_amount, total_repayable_amount, total_repaid, loan_balance, `status` FROM o_loans WHERE $where $andLoanBranch AND disbursed = 1 AND paid = 0 $filterCondition $andSearch ORDER BY $orderby $direction LIMIT $limit";
$loans_result = mysqli_query($con, $loans_query);

if (!$loans_result) {
    // Handle query error
    echo "<tr><td colspan='13'><i>Error fetching loans: " . mysqli_error($con) . "</i></td></tr>";
    include_once("../../configs/close_connection.inc");
    exit();
}

// Fetch total count for pagination
$count_query = "SELECT uid FROM o_loans WHERE $where $andLoanBranch AND status != 0 AND disbursed = 1 AND paid = 0 $filterCondition $andSearch LIMIT 1000";
$count_result = mysqli_query($con, $count_query);
$allTotal = intval(mysqli_num_rows($count_result));
mysqli_free_result($count_result);

// if ($count_result && mysqli_num_rows($count_result) > 0) {
//     $count_row = mysqli_fetch_assoc($count_result);
//     $allTotal = intval($count_row['total']);
//     mysqli_free_result($count_result);
// }

// Collect loan IDs and customer IDs for bulk fetching
$loanIds = array();
$customerIds = array();

while ($loan = mysqli_fetch_assoc($loans_result)) {
    $loanIds[] = intval($loan['uid']);
    $customerIds[] = intval($loan['customer_id']);
}

mysqli_data_seek($loans_result, 0); // Reset result pointer

// Convert arrays to comma-separated strings
$loansList = !empty($loanIds) ? implode(',', $loanIds) : '0';
$customersList = !empty($customerIds) ? implode(',', $customerIds) : '0';

// Fetch related data in bulk
$addonsData = nested_kv("o_loan_addons", "status = 1 AND loan_id IN ($loansList)", "loan_id", "addon_id", "addon_amount");
$loanStatuses = table_to_obj2('o_loan_statuses', "uid > 0", 30, "uid", array('name', 'color_code'));
$branchNames = table_to_obj('o_branches', "uid > 0", "100", "uid", "name");
$flagsDetails = table_to_obj2('o_flags', "uid>0", "100", "uid", array('name', 'color_code'));
$badgesDetails = table_to_obj2('o_badges', "uid>0", "30", "uid", array('title', 'icon'));

// Fetch customer details
$customerDetails = array();
$customerFlags = array();
$customerBadges = array();

if ($customersList != '0') {
    $customers_query = "SELECT uid, full_name, flag, badge_id FROM o_customers WHERE uid IN ($customersList) LIMIT 1000";
    $customers_result = mysqli_query($con, $customers_query);

    if ($customers_result && mysqli_num_rows($customers_result) > 0) {
        while ($cus = mysqli_fetch_assoc($customers_result)) {
            $cuid = intval($cus['uid']);
            $customerDetails[$cuid] = htmlspecialchars($cus['full_name']);
            $customerFlags[$cuid] = intval($cus['flag']);
            $customerBadges[$cuid] = intval($cus['badge_id']);
        }
        mysqli_free_result($customers_result);
    }
}

// Initialize HTML rows
$rows = "";

// Prepare additional details
$all_addons_kv = $addonsData; // Assuming nested_kv returns the required structure

if ($allTotal > 0) {
    while ($loan = mysqli_fetch_assoc($loans_result)) {
        $uid = intval($loan['uid']);
        $customer_id = intval($loan['customer_id']);
        $full_name = isset($customerDetails[$customer_id]) ? $customerDetails[$customer_id] : '';
        $primary_mobile = isset($loan['account_number']) ? htmlspecialchars($loan['account_number']) : '';
        $badge_id = isset($customerBadges[$customer_id]) ? $customerBadges[$customer_id] : 0;
        $flag = isset($customerFlags[$customer_id]) ? $customerFlags[$customer_id] : 0;
        $loan_branch = isset($branchNames[$loan['current_branch']]) ? htmlspecialchars($branchNames[$loan['current_branch']]) : '';
        $status = intval($loan['status']);
        $status_d = isset($loanStatuses[$status]) ? $loanStatuses[$status] : array('name' => '', 'color_code' => '');
        $given_date = htmlspecialchars($loan['given_date']);
        $next_due_date = htmlspecialchars($loan['next_due_date']);
        $final_due_date = htmlspecialchars($loan['final_due_date']);
        $payment_frequency = intval($loan['payment_frequency']);
        $period = intval($loan['period']);
        $period_units = intval($loan['period_units']);
        $total_repayable = floatval($loan['total_repayable_amount']);
        $total_repaid = floatval($loan['total_repaid']);
        $balance = floatval($loan['loan_balance']);
        $current_instalment = ceil(datediff($loan['given_date'], $loan['next_due_date']) / $payment_frequency);

        // Calculate instalments
        $period_days = $period * $period_units;
        $freq_days = $payment_frequency;

        // Calculate fees
        $loan_addons_det = isset($all_addons_kv[$uid]) ? $all_addons_kv[$uid] : array();
        $registration_fee = (isset($loan_addons_det[$registration_fee_addon_id])) ? floatval($loan_addons_det[$registration_fee_addon_id]) : 0;
        $processing_fee = (isset($loan_addons_det[$processing_fee_addon_id])) ? floatval($loan_addons_det[$processing_fee_addon_id]) : 0;
        $total_repayable -= ($registration_fee + $processing_fee);

        // Calculate instalment amounts and balance
        if ($freq_days > 0) {
            $instalments = floor($period_days / $freq_days);
            $instalment_amount = ceil($total_repayable / $instalments);
            $expected_repaid_sofar = $instalment_amount * $current_instalment;

            if ($total_repaid >= $expected_repaid_sofar) {
                $total_repaid = $instalment_amount;
                $balance = $total_repaid - $expected_repaid_sofar;
            } elseif ($total_repaid < $expected_repaid_sofar && $final_due_date <= $date) {
                $instalment_amount = $expected_repaid_sofar;
                $total_repaid = $expected_repaid_sofar - $balance;
            } elseif ($total_repaid < $expected_repaid_sofar) {
                $balance = $expected_repaid_sofar - $total_repaid;
                $due_instalment_multiplier = $current_instalment - 1;
                $amount_due_sofar = $instalment_amount * $due_instalment_multiplier;

                if ($total_repaid >= $amount_due_sofar) {
                    $total_repaid -= $amount_due_sofar;
                    $balance = $instalment_amount - $total_repaid;
                } else {
                    $total_repaid = 0;
                    $balance = $instalment_amount;
                }
            }
        }

        // Badge and Flag
        $flag_d = "";
        if ($flag > 0 && isset($flagsDetails[$flag])) {
            $flag_n = htmlspecialchars($flagsDetails[$flag]['name']);
            $flag_c = htmlspecialchars($flagsDetails[$flag]['color_code']);
            $flag_d = "<span><i class='fa fa-flag' style='color: $flag_c;'></i> $flag_n</span>";
        }

        $badge = "";
        if ($badge_id > 0 && isset($badgesDetails[$badge_id])) {
            $badge_title = htmlspecialchars($badgesDetails[$badge_id]['title']);
            $badge_icon = htmlspecialchars($badgesDetails[$badge_id]['icon']);
            $badge = "<a title='$badge_title'><img src='badges/$badge_icon' height='18px'/></a>";
        }

        // Construct table row
        $rows .= "<tr>
                    <td class='font-14 font-bold'>" . $uid . "</td>
                    <td>
                        <span class='font-14'>$badge $full_name 
                            <a title='View Customer' href='customers?customer=" . encurl($customer_id) . "'>
                                <i class='fa fa-external-link'></i>
                            </a>
                        </span><br/> 
                        <span class='text-muted font-13 font-bold'>$primary_mobile</span>
                    </td>
                    <td>
                        <span class='text-bold text-blue font-14'>{$loan['loan_amount']}</span><br/> 
                        <span class='font-13'>$loan_branch</span>
                    </td>
                    <td><span>$instalment_amount</span></td>
                    <td><span class='text-green'>" . money($total_repaid) . "</span></td>
                    <td><span class='font-bold text-red font-16'>" . money($balance) . "</span></td>
                    <td>
                        <span>$given_date</span><br/> 
                        <span class='text-orange font-13 font-bold'>" . fancydate($given_date) . "</span>
                    </td>
                    <td>
                        <span>$next_due_date</span><br/> 
                        <span class='text-orange font-13 font-bold'>" . fancydate($next_due_date) . "</span>
                    </td>
                    <td>
                        <span class='label custom-color' style='background-color: {$status_d['color_code']};'>{$status_d['name']}</span><br/> 
                        $flag_d
                    </td>
                    <td>
                        <span>
                            <a href='loans?loan=" . encurl($uid) . "'>
                                <span class='fa fa-eye text-green'></span>
                            </a> 
                            <a href='#' title='Popup' onclick=\"interactions_popup('" . encurl($customer_id) . "')\">
                                <i class='fa fa-comments-o text-orange'></i>
                            </a>
                        </span>
                    </td>
                </tr>";
    }
} else {
    $rows .= "<tr><td colspan='13'><i>No Records Found</i></td></tr>";
}

// Output the results
echo trim($rows) . "<tr style='display: none;'>
                    <td>
                        <input type='hidden' id='_alltotal_' value='$allTotal'>
                        <input type='hidden' id='_pageno_' value='$page_no'>
                    </td>
                </tr>";

// Close the database connection
include_once("../../configs/close_connection.inc");
?>
