<?php
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
$orderby = isset($_POST['orderby']) ? $_POST['orderby'] : 'uid';
$dir = isset($_POST['dir']) ? $_POST['dir'] : 'DESC';
$search = isset($_POST['search_']) ? trim(mysqli_real_escape_string($con, $_POST['search_'])) : '';
$sort_option = isset($_POST['sort_option']) ? mysqli_real_escape_string($con, $_POST['sort_option']) : '';

// Initialize variables
$limit = "$offset, $rpp";
$rows = "";
$allTotal = 0;
$noRows = noRowSpan(12);
$date = date('Y-m-d'); // Assuming $date is today's date. Adjust as needed.

// Function to get the search condition
function getSearchCondition($search, $branchCondition, $con)
{
    $andsearch = "";
    $branchUserCondition = isset($branchCondition['branchUserCondition']) ? $branchCondition['branchUserCondition'] : "";
    $custArray = array();

    if (preg_match("/\d{4}-\d{2}-\d{2}/", $search)) {
        // Search by date
        $searchEscaped = mysqli_real_escape_string($con, $search);
        $andsearch = " AND (final_due_date = '$searchEscaped')";
        return $andsearch;
    } else {
        // Determine search type
        if (is_numeric($search)) {
            // Check if it's a phone number
            $validated_phone = make_phone_valid($search);
            if (validate_phone($validated_phone) == 1) {
                $customerWhereCondition = " (primary_mobile = '$validated_phone')";
            } else {
                // Assume it's a national ID
                $customerWhereCondition = " (national_id = '$search')";
            }
        } else {
            // Search by full name
            $customerWhereCondition = " (full_name LIKE '%$search%')";
        }

        // Fetch matching customer IDs
        $customers_query = "SELECT uid FROM o_customers WHERE $customerWhereCondition $branchUserCondition LIMIT 1000";
        $customers_result = mysqli_query($con, $customers_query);

        if ($customers_result && mysqli_num_rows($customers_result) > 0) {
            while ($cu = mysqli_fetch_assoc($customers_result)) {
                $custArray[] = intval($cu['uid']);
            }
            mysqli_free_result($customers_result);

            if (!empty($custArray)) {
                $customerList = implode(",", $custArray);
                $andsearch = " AND customer_id IN ($customerList)";
            }
        }

        // If no customers found, search by UID or loan amount
        if (empty($andsearch)) {
            $searchEscaped = mysqli_real_escape_string($con, $search);
            $andsearch = " AND (uid = '$searchEscaped' OR loan_amount = '$searchEscaped')";
        }

        return $andsearch;
    }
}

// Get branch conditions based on user permissions
$branchCondition = getBranchCondition($userDetails, 'o_loans');
$branchLoanCondition = isset($branchCondition['branchLoanCondition']) ? $branchCondition['branchLoanCondition'] : "";
$whereCondition = "AND $where $branchLoanCondition";

// Build search condition if search input is available
if (input_available($search) == 1) {
    $searchCondition = getSearchCondition($search, $branchCondition, $con);
} else {
    $searchCondition = "";
}

// If search condition is empty but search input is provided, display no rows
if ($searchCondition == "" && input_available($search) == 1) {
    echo "$noRows";
    include_once("../../configs/close_connection.inc");
    exit();
}

// Fetch maximum and minimum loan balances
$max_bal_query = "SELECT MAX(loan_balance) AS max_bal FROM o_loans WHERE uid > 0 AND status IN (7) AND loan_balance > 0 $whereCondition";
$max_bal_result = mysqli_query($con, $max_bal_query);
$max_bal = floatval(mysqli_fetch_assoc($max_bal_result)['max_bal']);
mysqli_free_result($max_bal_result);

$min_bal_query = "SELECT MIN(loan_balance) AS min_bal FROM o_loans WHERE uid > 0 AND status IN (7) AND loan_balance > 0 $whereCondition";
$min_bal_result = mysqli_query($con, $min_bal_query);
$min_bal = floatval(mysqli_fetch_assoc($min_bal_result)['min_bal']);
mysqli_free_result($min_bal_result);

// Build the base query
$baseQuery = "uid > 0 AND status IN (7) AND loan_balance > 0
              $searchCondition $whereCondition";

// Modify base query based on sort option
switch ($sort_option) {
    case "newest":
        $orderDir = "DESC";
        break;
    case "oldest":
        $orderDir = "ASC";
        break;
    case "max":
        $baseQuery .= " AND loan_balance = $max_bal";
        $orderDir = $dir;
        break;
    case "min":
        $baseQuery .= " AND loan_balance = $min_bal";
        $orderDir = $dir;
        break;
    case "uncommitted":
        $baseQuery .= " AND total_repaid = 0.00";
        $orderDir = $dir;
        break;
    default:
        $orderDir = $dir;
        break;
}

// Fetch loans based on the final query
$loans_query = "SELECT uid, customer_id, account_number, product_id, loan_amount, disbursed_amount, period, period_units, payment_frequency, payment_breakdown, total_addons, total_deductions, total_instalments, total_instalments_paid, current_instalment, given_date, next_due_date, final_due_date, added_by, added_date, loan_stage, current_branch, transaction_code, transaction_date, application_mode, total_repaid, loan_balance, current_lo, current_co, current_agent, `status` FROM o_loans WHERE $baseQuery ORDER BY $orderby $orderDir LIMIT $limit";

$loans_result = mysqli_query($con, $loans_query);

if (!$loans_result) {
    // Handle query error
    echo "<tr><td colspan='12'><i>Error fetching loans: " . mysqli_error($con) . "</i></td></tr>";
    include_once("../../configs/close_connection.inc");
    exit();
}

// Fetch total count for pagination
$count_query = "SELECT uid FROM o_loans WHERE $baseQuery LIMIT 1000";
$count_result = mysqli_query($con, $count_query);
$allTotal = intval(mysqli_num_rows($count_result));
if ($count_result) mysqli_free_result($count_result);

// Collect customer IDs for bulk fetching
$custListArray = array();
$customer_ids_query = "SELECT customer_id FROM o_loans WHERE $baseQuery ORDER BY $orderby $orderDir LIMIT $limit";
$customer_ids_result = mysqli_query($con, $customer_ids_query);
if ($customer_ids_result && mysqli_num_rows($customer_ids_result) > 0) {
    while ($cu = mysqli_fetch_assoc($customer_ids_result)) {
        $custListArray[] = intval($cu['customer_id']);
    }
    mysqli_free_result($customer_ids_result);
}

$customers_list = !empty($custListArray) ? implode(',', $custListArray) : '0';

// Fetch related data in bulk
$statusDet = table_to_obj2('o_loan_statuses', "uid > 0", 100, "uid", array('name', 'color_code'));
$branches_array = table_to_obj('o_branches', "uid > 0", "1000", "uid", "name");
$products_array = table_to_obj('o_loan_products', "uid > 0", "100", "uid", "name");
$stages_array = table_to_obj('o_loan_stages', "uid > 0", "100", "uid", "name");
$agent_names = table_to_obj2('o_users', "uid > 0", 10000, "uid", array('name', 'tag'));

// Fetch customer details
$customer_det = array();
$customer_flag_det = array();
$customer_badge_det = array();

if ($customers_list != '0') {
    $cust_query = "SELECT uid, full_name, badge_id, flag FROM o_customers WHERE uid IN ($customers_list) LIMIT 1000";
    $cust_result = mysqli_query($con, $cust_query);
    if ($cust_result && mysqli_num_rows($cust_result) > 0) {
        while ($cus = mysqli_fetch_assoc($cust_result)) {
            $cuid = intval($cus['uid']);
            $customer_det[$cuid] = htmlspecialchars($cus['full_name']);
            $customer_badge_det[$cuid] = intval($cus['badge_id']);
            $customer_flag_det[$cuid] = intval($cus['flag']);
        }
        mysqli_free_result($cust_result);
    }
}

// Fetch flags and badges details
$flagsDetails = table_to_obj2('o_flags', "uid > 0", "100", "uid", array('name', 'color_code'));
$badgesDetails = table_to_obj2('o_badges', "uid > 0", "30", "uid", array('title', 'icon'));

// Initialize HTML rows
$row = "";

// Generate HTML table rows
if ($allTotal > 0) {
    while ($n = mysqli_fetch_assoc($loans_result)) {
        $uid = intval($n['uid']);
        $customer_id = intval($n['customer_id']);
        $full_name = isset($customer_det[$customer_id]) ? $customer_det[$customer_id] : "";
        $primary_mobile = isset($n['account_number']) ? htmlspecialchars($n['account_number']) : "";
        $product_id = intval($n['product_id']);
        $loan_amount = htmlspecialchars($n['loan_amount']);
        $disbursed_amount = htmlspecialchars($n['disbursed_amount']);
        $period = intval($n['period']);
        $period_units = intval($n['period_units']);
        $payment_frequency = intval($n['payment_frequency']);
        $payment_breakdown = htmlspecialchars($n['payment_breakdown']);
        $total_addons = htmlspecialchars($n['total_addons']);
        $total_deductions = htmlspecialchars($n['total_deductions']);
        $total_instalments = intval($n['total_instalments']);
        $total_instalments_paid = intval($n['total_instalments_paid']);
        $current_instalment = intval($n['current_instalment']);
        $given_date = htmlspecialchars($n['given_date']);
        $next_due_date = htmlspecialchars($n['next_due_date']);
        $final_due_date = htmlspecialchars($n['final_due_date']);
        $added_by = intval($n['added_by']);
        $added_date = htmlspecialchars($n['added_date']);
        $loan_stage = intval($n['loan_stage']);
        $current_branch = intval($n['current_branch']);
        $transaction_code = htmlspecialchars($n['transaction_code']);
        $transaction_date = htmlspecialchars($n['transaction_date']);
        $application_mode = htmlspecialchars($n['application_mode']);
        $status = intval($n['status']);
        $repaid = htmlspecialchars($n['total_repaid']);
        $balance = htmlspecialchars($n['loan_balance']);
        $LO = intval($n['current_lo']);
        $CO = intval($n['current_co']);
        $current_agent = intval($n['current_agent']);

        // Fetch agent names
        $agent_info = "";
        if ($current_agent > 0 && isset($agent_names[$current_agent])) {
            $agent_name = htmlspecialchars($agent_names[$current_agent]['name']);
            $agent_tag = !empty($agent_names[$current_agent]['tag']) ? "(" . htmlspecialchars($agent_names[$current_agent]['tag']) . ")" : "";
            $agent_info .= "$agent_name $agent_tag<br/>";
        }

        if ($LO > 0 && isset($agent_names[$LO])) {
            $lo_name = htmlspecialchars($agent_names[$LO]['name']);
            $lo_tag = !empty($agent_names[$LO]['tag']) ? "(" . htmlspecialchars($agent_names[$LO]['tag']) . ")" : "";
            $agent_info .= "$lo_name $lo_tag<br/>";
        }

        if ($CO > 0 && isset($agent_names[$CO])) {
            $co_name = htmlspecialchars($agent_names[$CO]['name']);
            $co_tag = !empty($agent_names[$CO]['tag']) ? "(" . htmlspecialchars($agent_names[$CO]['tag']) . ")" : "";
            $agent_info .= "$co_name $co_tag";
        }

        // Fetch product name, branch name, and status details
        $product_name = isset($products_array[$product_id]) ? htmlspecialchars($products_array[$product_id]) : 'N/A';
        $loan_branch = isset($branches_array[$current_branch]) ? htmlspecialchars($branches_array[$current_branch]) : 'N/A';
        $statusName = isset($statusDet[$status]['name']) ? htmlspecialchars($statusDet[$status]['name']) : 'Unknown';
        $statusColor = isset($statusDet[$status]['color_code']) ? htmlspecialchars($statusDet[$status]['color_code']) : '#000';

        // Fetch flag details
        $flag_d = "";
        if (isset($customer_flag_det[$customer_id]) && $customer_flag_det[$customer_id] > 0 && isset($flagsDetails[$customer_flag_det[$customer_id]])) {
            $flag_name = htmlspecialchars($flagsDetails[$customer_flag_det[$customer_id]]['name']);
            $flag_color = htmlspecialchars($flagsDetails[$customer_flag_det[$customer_id]]['color_code']);
            $flag_d = "<span><i class='fa fa-flag' style='color: $flag_color;'></i> $flag_name</span>";
        }

        // Fetch badge details
        $badge = "";
        if (isset($customer_badge_det[$customer_id]) && $customer_badge_det[$customer_id] > 0 && isset($badgesDetails[$customer_badge_det[$customer_id]])) {
            $badge_title = htmlspecialchars($badgesDetails[$customer_badge_det[$customer_id]]['title']);
            $badge_icon = htmlspecialchars($badgesDetails[$customer_badge_det[$customer_id]]['icon']);
            $badge = "<a title='$badge_title'><img src='badges/$badge_icon' height='18px'/></a>";
        }

        // Construct HTML table row
        $row .= "<tr>
                    <td class='font-14 font-bold'>{$uid}</td>
                    <td>
                        <span class='font-14'>{$badge} {$full_name}
                            <a title='View Customer' href='customers?customer=" . encurl($customer_id) . "'>
                                <i class='fa fa-external-link'></i>
                            </a>
                        </span><br/>
                        <span class='text-muted font-13 font-bold'>{$primary_mobile}</span>
                    </td>
                    <td>{$agent_info}</td>
                    <td>
                        <span class='text-bold text-blue font-14'>{$loan_amount}</span><br/>
                        <span class='font-13'>{$loan_branch}</span>
                    </td>
                    <td><span>{$total_addons}</span></td>
                    <td><span>{$total_deductions}</span></td>
                    <td><span class='text-green'>" . money($repaid) . "</span></td>
                    <td>
                        <span class='font-bold text-red font-16'>" . money($balance) . "</span><br/>
                        <span class='text-muted font-13 font-italic'>Next: {$next_due_date}</span>
                    </td>
                    <td>
                        <span>{$given_date}</span><br/>
                        <span class='text-orange font-13 font-bold'>" . fancydate($given_date) . "</span>
                    </td>
                    <td>
                        <span>{$final_due_date}</span><br/>
                        <span class='text-orange font-13 font-bold'>" . fancydate($final_due_date) . "</span>
                    </td>
                    <td>
                        <span class='label custom-color' style='background-color: {$statusColor};'>{$statusName}</span><br/> 
                        {$flag_d}
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
    echo "$noRows";
}

// Output the results
echo trim($row) . "<tr style='display: none;'>
                    <td>
                        <input type='hidden' id='_alltotal_' value='$allTotal'>
                        <input type='hidden' id='pageno' value='$page_no'>
                    </td>
                </tr>";

// Close the database connection
include_once("../../configs/close_connection.inc");
?>
