<?php

session_start();

include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$this_year = date('Y');
$this_month = date('m');
$this_day = date('d');
$currencyUsed = currencyUsed();

$userd = session_details();
$staff_id = intval($userd["uid"]);

// ensure staff id is not 0
if($staff_id == 0){
    exit();
}

$userbranch = $userd['branch'];
$view_summary = permission($userd['uid'], 'o_summaries', "0", "read_");
$inarchive_ = $_SESSION['archives'] ?? 0;
if ($view_summary == 1 || $inarchive_ == 1) {
    $andbranch_loans = "";
    $andbranch_payments = "";
} else {

    $andbranch_loans = "AND current_branch = $userbranch";
    $andbranch_payments = "AND branch_id = $userbranch";

    //////-----Check users who view multiple branches
    $staff_branches = table_to_array('o_staff_branches', "agent=" . $userd['uid'] . " AND status=1", "1000", "branch", "uid", "asc");
    if (sizeof($staff_branches) > 0) {
        ///------Staff has been set to view multiple branches
        array_push($staff_branches, $userd['branch']);
        $staff_branches_list = implode(",", $staff_branches);

        $andbranch_loans = "AND current_branch IN ($staff_branches_list)";
        $andbranch_payments = "AND branch_id IN ($staff_branches_list)";
    }
}

$loans_today = totaltable('o_loans', "given_date='$date' AND status !=0 AND disbursed=1 $andbranch_loans", "loan_amount");
$payments_today = totaltable('o_incoming_payments', "payment_date='$date' AND status=1 $andbranch_payments", "amount");
$due_today = totaltable('o_loans', "final_due_date='$date' AND disbursed=1 AND paid=0 AND status IN (3,4) $andbranch_loans", "loan_balance");
if ($cc == 256) {
    $utility_balance = fetchrow('o_summaries', "name='MTN_UTILITY_BALANCE' $andbranch_loans", "value_");
    $inline_text = 'MTN B2C Balance:';

    $airtel_ug_utility_balance = fetchrow('o_summaries', "name='AIRTEL_UG_UTILITY_BALANCE' $andbranch_loans", "value_");
    $ug_airtel_utility_inline_text = "Airtel B2C Balance:";

    $airtel_ug_paybill_balance = fetchrow('o_summaries', "name='AIRTEL_UG_PAYBILL_BALANCE' $andbranch_loans", "value_");
    $airtel_ug_paybill_inline_text = "Airtel C2B Balance:";
} else {
    $utility_balance = fetchrow('o_summaries', "name='UTILITY_BALANCE' $andbranch_loans", "value_");
    $inline_text = 'B2C Balance:';
}

$paybill_balance = fetchrow('o_summaries', "name='PAYBILL_BALANCE' $andbranch_loans", "value_");
$sms_balance = fetchrow('o_summaries', "name='SMS_BALANCE'", "value_");

// Constructing response data
$response = array(
    'loans_today' => money($loans_today),
    'payments_today' => money($payments_today),
    'due_today' => money($due_today),
    'utility_balance' => money($utility_balance),
    'paybill_balance' => money($paybill_balance),
    'sms_balance' => money($sms_balance)
);

if($cc == 256) {
    $response['airtel_ug_utility_balance'] = money($airtel_ug_utility_balance);
    $response['airtel_ug_paybill_balance'] = money($airtel_ug_paybill_balance);
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);

function currencyUsed()
{
    global $cc;
    $currency = "";
    if ($cc == 256) {
        $currency = "UGX";
    } elseif ($cc == 254) {
        $currency = 'KES';
    } else if ($cc == 255) {
        $currency = 'TZS';
    }

    return $currency;
}

?>
