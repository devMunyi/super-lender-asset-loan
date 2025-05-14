<?php
declare(strict_types=1);

$expected_http_method = 'POST';
include_once("../../../vendor/autoload.php");
include_once ("../../../configs/allowed-ips-or-origins.php");
include_once("../../../configs/conn.inc");
include_once("../../../configs/jwt.php");
include_once("../../../php_functions/jwtAuthUtils.php");
include_once("../../../php_functions/jwtAuthenticator.php");
include_once("../../../php_functions/functions.php");
include_once("../../../php_functions/tenakata.php");


$data = json_decode(file_get_contents('php://input'), true);
$customer_id = intval($data["customer_id"]);
$loan_amount = doubleval($data["amount"]);
$application_mode = strtoupper($data['platform'] ?? 'app');
$loan_type = 2; // default business loan  or 1 for personal loan

$info = "Started";





if (input_available($loan_amount) == 0) {
    sendApiResponse(400, "Amount is required!");
}

if ($loan_amount < 1) {
    sendApiResponse(400, "Please enter a Valid amount");
}

if ($customer_id > 0) {
} else {
    sendApiResponse(400, "Customer ID Missing!");
}


// get customer details
$cust_det = fetchonerow('o_customers', "uid = $customer_id", "uid, primary_product, loan_limit, status, primary_mobile, branch, full_name, national_id");
$product_id = $primary_product = $cust_det['primary_product'];
$loan_limit = $cust_det['loan_limit'];
$status = $cust_det['status'];
$cust_id = $cust_det['uid'];
$primary_mobile = $cust_det['primary_mobile'];
$cust_branch = $cust_det['branch'];
// $week_ago = datesub($date, 0, 0, 2000);
$week_ago = datesub($date, 0, 0, $autopicked_payment_days ? $autopicked_payment_days : 90);

// check if branch is frozen
$branch_det = fetchonerow('o_branches', "uid='$cust_branch'", "freeze, name");
$branch_freeze = $branch_det['freeze'];
$branch_name = trim($branch_det['name']);
if (in_array($branch_freeze, ['API', 'BOTH'])) {
    $message = "$branch_name branch repeat loans disabled temporarily!";
    $http_status_code = 400;
    sendApiResponse($http_status_code, "$message");
}

// $logged_in_agent = $added_by;
$real_agents = real_loan_agent($customer_id);
$current_lo = $real_agents['LO'];
$current_co = $real_agents['CO'];

if (empty($cust_det)) {
    $http_status_code = 404;
    $message = "Customer Not Found";

    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse($http_status_code, "$message");
}
if($primary_product == 2){
    // $supplier = intval($data["supplier_id"]); ///-----Picked from API
    ///----Supplier, picked from account
    $supplier = fetchrow('o_group_members',"customer_id='$customer_id' AND status=1","group_id");
    $info.= "[Supplier_id: $supplier]";
    if($supplier < 1){
        sendApiResponse(400, "Customer has not been added to any supplier group $customer_id");
    }
    else{
        $sup = fetchonerow('o_customer_groups', "uid='$supplier'", "till, status, group_phone");
        if($sup['status'] != 1){
            sendApiResponse(400, "The supplier is disabled in the system");
        }
        if(validate_phone($sup['group_phone']) != 1){
            sendApiResponse(400, "The supplier phone number is not valid in the system");
        }
    }
    $info.= "[Supplier_details: ".$sup['group_phone']."]";
}

// limit salary advance loan product application to only happen between 15 and 25 of the month
if($primary_product == 4){
    function getDayFromDate(string $dateString): ?int {
        $timestamp = strtotime($dateString);
        
        if ($timestamp === false) {
            // Invalid date format
            return null;
        }
    
        return (int)date('j', $timestamp); // 'j' = day of month without leading zeros
    }

    $dayFromDate = getDayFromDate($date);
    if($dayFromDate >= 15 && $dayFromDate <= 25) {
    } else {
        $message = "Salary Advance Loan can only be applied between 15th and 25th of the month.";
        $http_status_code = 400;

        store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
        sendApiResponse($http_status_code, "$message");
    }
}


// check if its a new customer to restrict maximum limit to 20K
$loans_borrowed_count = totaltable("o_loans", "customer_id  = $customer_id AND disbursed = 1", "disbursed");

if ($loans_borrowed_count > 0) {
} else {
    if ($loan_amount > 30000) {
        $http_status_code = 400;
        $message = "Allowed Maximum Limit for a New Customer is KES30,000";

        store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
        sendApiResponse($http_status_code, "$message");
    }
}

// check for pending loan limit approval
$has_pending_approval = fetchmaxid("o_customer_limits", "customer_uid = $customer_id", "amount, status");
$pending_approval_amt = $has_pending_approval['amount'] ?? 0;
$limit_status = $has_pending_approval['status'] ?? 0;

if (doubleval($pending_approval_amt > 0 && $limit_status === 2)) {
    $str_approval_amt = money($pending_approval_amt);
    $http_status_code = 400;
    $message = "You have a new pending loan limit of KES$str_approval_amt. Please contact us!";

    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse($http_status_code, "$message");
}


/////------Check if customer has an active loan
$has_loan = checkrowexists('o_loans', "customer_id = $customer_id AND disbursed=0 AND paid=0 AND status!=0 AND status in (1,2)");
if ($has_loan == 1) {
    $message = 'You have a pending loan, please wait while we review it.';
    $http_status_code = 409;

    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse($http_status_code, "$message");
}

$has_loan = checkrowexists('o_loans', "customer_id= $customer_id AND disbursed = 1 AND paid = 0");
if ($has_loan == 1) {
    $message = "You have an existing loan. Please repay it to get a new one";
    $http_status_code = 409;

    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse($http_status_code, "$message");
}
////-----Check if customer account is active
if ($status != 1) {
    $message = "Your Account is inactive, please contact support";
    $http_status_code = 403;


    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse($http_status_code, "$message");
}

if ($loan_amount > $loan_limit) {
    $message = '"Your Allowed limit is ' . $loan_limit . '"';
    $http_status_code = 400;

    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse($http_status_code, "$message");
}


$y = fetchonerow("o_loan_products", "uid='$product_id'", "*");
$period = $y['period'];
$period_units = $y['period_units'];
$min_amount = $y['min_amount'];
$max_amount = $y['max_amount'];
$pay_frequency = $y['pay_frequency'];
$percent_breakdown = $y['percent_breakdown'];
$automatic_disburse = $y['automatic_disburse'];
$added_date = $y['added_date'];

$status = $y['status'];
if ($status != 1) {
    $message = "Product is disabled";
    $http_status_code = 400;

    // store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse($http_status_code, "$message");
}


/// -----Check if amount is allowed
if ($loan_amount < $min_amount || $loan_amount > $max_amount) {
    $message = "The product allows amounts between $min_amount AND $max_amount";
    $http_status_code = 400;

    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse($http_status_code, "$message");
}

///////// Check if customer has account repayable and repaid reconciled
$customer_loan_uids = table_to_array('o_loans', "customer_id = $customer_id", "1000000", "uid");
// convert them to comman separated values
$loan_uids = implode(',', $customer_loan_uids);

$loan_addons = fetchtable2('o_loan_addons', "status = 1 AND loan_id IN ($loan_uids)", 'uid', 'asc', 'loan_id, addon_amount');
$l_addon = [];
while ($lad = mysqli_fetch_assoc($loan_addons)) {
    $lid = $lad['loan_id'];
    $laddon_amt = $lad['addon_amount'];

    $l_addon = obj_add($l_addon, $lid, $laddon_amt);
}

// prepare loan repaid totals associative array
$all_payments = fetchtable2("o_incoming_payments", "status = 1 AND (customer_id=$customer_id OR mobile_number='$primary_mobile')", "uid", "DESC", "amount, loan_id");
$loan_payment_totals = [];
while ($p = mysqli_fetch_assoc($all_payments)) {
    $paid_amount = $p['amount'] ?? 0;
    $loan_uid = $p['loan_id'] ?? 0;

    if (!in_array($loan_uid, array(0, 1, 2))) {
        $loan_payment_totals = obj_add($loan_payment_totals, $loan_uid, $paid_amount);
    }
}

// prepare loans to check associative array
$loans_to_check = fetchtable2('o_loans', "customer_id = $customer_id AND status in (1,2,3,4,5,7,8)", "uid", "ASC", "uid, total_repayable_amount, loan_amount, total_repaid");
$unreconciled_loans = [];
while ($ltc = mysqli_fetch_array($loans_to_check)) {
    $ltc_uid = $ltc['uid'];
    $ltc_given_amount = doubleval($ltc['loan_amount']);
    $ltc_addons_total = doubleval($l_addon[$ltc_uid]);

    $ltc_repayable_amount = $ltc_given_amount + $ltc_addons_total;
    $ltc_repaid_amount = doubleval($loan_payment_totals[$ltc_uid]);

    if (doubleval($ltc_repaid_amount) >= doubleval($ltc_repayable_amount)) {
    } else {
        $unreconciled_loans[] = $ltc_uid;
    }
}

$loans_to_check_count = count($unreconciled_loans);
if ($loans_to_check_count > 0) {
    // $stringfied_loan_ids = implode(',', $unreconciled_loans);
    // $loan_word_pluralized = $loans_to_check_count === 1 ? 'loan id' : 'loan ids';
    // $pronoun_pluralized = $loans_to_check_count === 1 ? 'it' : 'them';

    $unreconciled_loans_str = implode(',', $unreconciled_loans);
    $message = "Account Reconciliation Required for loan ids: $unreconciled_loans_str";
    $http_status_code = 409;

    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse($http_status_code, "$message");
}
// }
///////// End Check if customer has account repayable and repaid reconciled


//////-------------Check if customer is required to pay any amount before getting a loan
/// --------------Check all upfront fees
$upfronts = fetchtable('o_addons', "paid_upfront = 1 AND status=1", "uid", "asc", 10, "uid, amount, amount_type, applicable_loan");
$total_upfront = 0;

while ($up = mysqli_fetch_array($upfronts)) {
    $aid = $up['uid'];
    $product_addon = fetchrow('o_product_addons', "addon_id='$aid' AND status=1 AND product_id='$product_id'", "uid");
    if ($product_addon > 0) {
        $upfront_addon = $up['uid'];
        $applicable_loan = $up['applicable_loan'];
        $amount = $up['amount'];
        $amount_type = $up['amount_type'];

        if ($amount_type == 'FIXED_VALUE') {
            $a_amount = $amount;
        } else {
            $a_amount = $loan_amount * ($amount / 100);
        }


        if ($applicable_loan == 0) {
            $total_upfront += $a_amount;
        } else {
            $total_loans_taken = countotal_withlimit('o_loans', "customer_id = $customer_id AND disbursed = 1", "uid", "1000");
            if ($total_loans_taken < $applicable_loan) {
                $total_upfront += $a_amount;
            }
        }
    }
}


$paid = totaltable('o_incoming_payments', "(mobile_number='$primary_mobile' OR customer_id = $cust_id) AND loan_id=0 AND payment_category IN (0, 1, 2, 4) AND status=1 AND payment_date >= '$week_ago'", "amount");
$total_repaid = totaltable('o_loans', "disbursed=1 AND paid=1 AND status!=0 AND customer_id='$customer_id'", "total_repaid");
$total_repayable = totaltable('o_loans', "disbursed=1 AND paid=1 AND status!=0 AND customer_id='$customer_id'", "total_repayable_amount");
$overpayments = false_zero($total_repaid - $total_repayable);

$paid_plus_overpayments = $paid + $overpayments;
if ($paid_plus_overpayments < $total_upfront) {
    $balance = $total_upfront - $paid_plus_overpayments;

    ////-----Tenakata Temporaly solution
    $total_temp = totaltable('o_incoming_payments', "(mobile_number='$primary_mobile' OR customer_id = $cust_id) AND loan_id = 1 AND payment_category IN (0, 1, 2, 4) AND status = 1 AND payment_date >= '$week_ago'", "amount");

    if ($total_temp >= 500) {
        $upd = updatedb('o_incoming_payments', "loan_id = 2", "(mobile_number='$primary_mobile' OR customer_id = $cust_id) AND loan_id = 1 AND payment_category IN (0, 1, 2, 4) AND status = 1 AND payment_date >= '$week_ago'");
    } else {
        ///
        ///------End of Tenakata Temporaly
        $message = "You have not paid an upfront fee of KES $balance";
        $http_status_code = 402;

        store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
        sendApiResponse($http_status_code, "$message");
    }
} else {
    $update_repayment = 1;
}

////--------Check if loan is in right denomination
$deno = denomination_okey($product_id, $loan_amount);

if ($deno[0] == 0) {
    $message = "Denomination not valid. Use multiples of " . $deno[1];
    $http_status_code = 400;

    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse($http_status_code, "$message");
}

/// -----Create Loan
$given_date = $date;         ////Initialization
////Calculated from product
$final_due_date = final_due_date($given_date, $period, $period_units);         ////Calculated from product
$transaction_date = $fulldate;         ////Initialization
$added_date = $fulldate;
$loan_stage_d = fetchminid('o_product_stages', "product_id = '$product_id' AND status = 1 AND is_final_stage = 1", "stage_order, uid");
$loan_stage = $loan_stage_d['stage_id'];

$total_instalments = total_instalments($period, $period_units, $pay_frequency);         //////Calculated from product
$total_instalments_paid = 0.00;  /////Initialization
$current_instalment = 1;         ////Initialization

$next_due_date = next_due_date($given_date, $period, $period_units, $pay_frequency);
////------Create a Loan

$next_due_date = move_to_monday($next_due_date);
$final_due_date = move_to_monday($final_due_date);
$fds = array('customer_id', 'account_number', 'loan_type', 'product_id', 'loan_amount', 'disbursed_amount', 'total_repayable_amount', 'total_repaid', 'loan_balance', 'period', 'period_units', 'payment_frequency', 'payment_breakdown', 'total_instalments', 'total_instalments_paid', 'current_instalment', 'given_date', 'next_due_date', 'final_due_date', 'added_by', 'current_lo', 'current_co', 'current_branch', 'added_date', 'loan_stage', 'application_mode', 'status');
$vals = array("$cust_id", "$primary_mobile", $loan_type, "$product_id", "$loan_amount", "$loan_amount", "$loan_amount", 0, "$loan_amount", "$period", "$period_units", "$pay_frequency", "$pay_frequency", "$total_instalments", "$total_instalments_paid", "$current_instalment", "$given_date", "$next_due_date", "$final_due_date", "1", $current_lo, $current_co, "$cust_branch", "$added_date", "$loan_stage", "$application_mode", "1");


$create = addtodb('o_loans', $fds, $vals);
if ($create == 1) {
    ////----Send money
    $latest_loan = fetchmaxid('o_loans', "customer_id = '$cust_id'", "uid");
    $loan_id = $latest_loan['uid'];
    ////////-----------Add Automatic AddOns
    $addons = fetchtable('o_product_addons', "product_id = '$product_id' AND status=1", "addon_id", "asc", "20", "addon_id");
    while ($addon = mysqli_fetch_array($addons)) {
        $addon_id = $addon['addon_id'];
        $automatic = fetchrow('o_addons', "uid = '$addon_id' AND from_day = 0 AND status=1", "automatic");
        if ($automatic == 1) {
            apply_loan_addon_to_Loan($addon_id, $loan_id, false);
        }
    }

    if ($update_repayment == 1) {
        ///-------Update the upfront code with latest loan ID
        updatedb('o_incoming_payments', "loan_id='$loan_id'", "(mobile_number='$primary_mobile' OR customer_id = $cust_id) AND loan_id = 0 AND payment_category IN (0, 1, 2, 4) AND status=1 AND payment_date >= '$week_ago'");
        // echo $updaterep.'Update repayment';
    }

    // mpesa_addon(4, $latest_loan['uid'], false);
    recalculate_loan($loan_id, true);
    $loan_balance = loan_balance($loan_id);
    if ($update_repayment == 1) {
        ///-------Update the upfront code with latest loan ID
        updatedb('o_incoming_payments', "loan_balance = $loan_balance", "(mobile_number='$primary_mobile' OR customer_id = $cust_id) AND loan_id = $loan_id AND payment_category IN (0, 1, 2, 4) AND status=1 AND payment_date >= '$week_ago'");
        // echo $updaterep.'Update repayment';
    }


    if ($loan_amount >= 50000) {
        $automatic_disburse = 0;
    }
    $latest_lid = $latest_loan['uid'];
    if ($automatic_disburse == 1) {
        // send_money($primary_mobile, $amount, $latest_loan['uid']);
        ///-------Send money if its qualified

        $total_loans = countotal('o_loans', "disbursed='1' AND paid='1' AND customer_id='" . $cust_id . "' AND status!=0 AND given_date >= '2000-04-01' AND uid != '$latest_lid'");   /////---Has cleared at least one loan
        $days_3 = datesub($date, 0, 0, 3);
        $recent_loan = checkrowexists("o_loans", "customer_id='" . $cust_id . "'  AND given_date >= '$days_3' AND uid != '$latest_lid'");  ///Customer has not taken a loan last 3 days. Fraud prevention
        if ($total_loans >= 1 ) {
            ///----Customer qualifies for auto disbursement
            ////----Update loan status to pending disbursement
            $res = $latest_lid;
            $update_ = updatedb('o_loans', "status=2", "uid='$res'");
            $fds = array('loan_id', 'amount', 'added_date', 'trials', 'status');
            $vals = array("$latest_lid", "$loan_amount", "$fulldate", '0', '1');
            $queue = addtodb('o_mpesa_queues', $fds, $vals);
            store_event_return_void('o_loans', $res, "Queued for automatic processing Result $res");
        } else {
            store_event_return_void('o_loans', $latest_lid, "Not sent automatically because (total loans: $total_loans and recent loan: $recent_loan)");
        }


        ///------End of send money if its qualified
    }


    ////-----------------------------------------Process B2B Loans
    $b2b_loans = array(2,6);
    if($supplier > 0 && (in_array($product_id, $b2b_loans))) {
        $info.="[Supplier=$supplier,Product_id=2]";
        $sup = fetchonerow('o_customer_groups', "uid='$supplier'", "uid, group_phone, till, group_name");
        $loan_id = $latest_lid;
        $customer_id = $cust_id;
        $customer_name = $cust_det['full_name'];
        $customer_group_id = $supplier;
        $disbursed_amount = $loan_amount;
        $national_id = $cust_det['national_id'] ;
        $id_protected = hideMiddleDigits($national_id);
        $distributor_phone = $sup['group_phone'] ;
        $customer_phone = $primary_mobile;
///----Loan has been created but won't be disbursed

///--------Queue the B2B request

        $till = $sup['till'];

        $info.="[Till: $till]";

        if($till > 1000) {
            $fds = array('loan_id', 'amount', 'added_date', 'trials', 'short_code', 'status');
            $vals = array("$loan_id", "$disbursed_amount", "$fulldate", "1", "$till", '2');
            $queue = addtodb('o_b2b_queues', $fds, $vals);
        }
        else{
            $event_details = "B2B transaction not scheduled because the Till number is unavailable";
            $fds = array('tbl','fld','event_details','event_date','event_by','status');
            $vals = array("o_loans", $loan_id,"$event_details","$added_date", 0, 1);
            $event_logged = addtodb('o_events',$fds,$vals);
        }
///
        $b2b = b2b(3033631, $till, round($disbursed_amount, 0));
/// -------Send Messages
        $distributor_message = "Kindly Provide $customer_name ID $id_protected with stock worth $loan_amount. OrderNo. $loan_id Thank you";
        $customer_message = "Good news! Cash worth $loan_amount disbursed to distributor. Your goods will be delivered. OrderNo. $loan_id Thank you";

       $res = sendSMS($distributor_phone, $distributor_message)."<br/>";
       // $res = sendSMS($customer_phone, $customer_message)."<br/>";

        $q = queue_message($distributor_message, $distributor_phone);
        $q = queue_message($customer_message, $customer_phone);

       $info.="$b2b";
///
/// -------Mark Loan as disbursed
        $mark = updatedb('o_loans',"disbursed=1, status=3","uid='$loan_id'");
        if($mark == 1){
            $event_details2 = "Loan marked as disbursed by system awaiting B2B process. ($b2b, $till, $disbursed_amount)";
            $fds = array('tbl','fld','event_details','event_date','event_by','status');
            $vals = array("o_loans", $loan_id,"$event_details2","$fulldate", 0, 1);
            $event_logged = addtodb('o_events',$fds,$vals);
        }
        $info.="[Update loan $mark]";


    }
    ///
    /// ------------------------------------------End of process B2B loans

    $info.="[Here]";
    $message = "Your request has been submitted successfully. Please wait";
    $http_status_code = 200;


    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse($http_status_code, "$message", "OK");
} else {
    $message = "An internal error occurred. Please try again or contact us $create";
    $http_status_code = 500;

    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse($http_status_code, "$message");
}




mysqli_close($con);
