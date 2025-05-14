<?php
session_start();
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include_once '../../configs/20200902.php';
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
if ($has_archive == 1) {
    include_once("../../configs/archive_conn.php");
}

$userd = session_details();
$userid = $userd["uid"];
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}

$create_loan = permission($userd['uid'], 'o_loans', "0", "create_");
if ($create_loan != 1) {

    /////--------This new code block will check if this user is a CO without a pair and give
    ///  them temporary rights to create a loan
    $create_loan = 0;
    if ($userd['user_group'] == 8) {
        $lo_pair = fetchrow('o_pairing', "co='$userid' AND status=1", "lo");
        //---Check if LO is still enabled
        $lo_status =  fetchrow('o_users', "uid='$lo_pair'", "status");
        if (in_array($lo_status, [1, 3])) { // active or disabled
            ////----LO pair is active, don't grant rights
        } else {
            ////----LO pair is inactive, grant the rights to CO
            $create_loan = permission($lo_pair, 'o_loans', "0", "create_");
            $co_is_also_lo = 1;
        }
    }
    ///--------End of new code block to give CO temporary rights of LO
    if ($create_loan != 1) {
        exit(errormes("You don't have permission to create loan"));
    }
}

$customer_id = intval($_POST['customer_id']);
$product_id = $_POST['product_id'];
$loan_amount = $_POST['loan_amount'];
$application_mode = $_POST['application_mode'];
$loan_type = $_POST['loan_type'];
$added_by = $userd['uid'];
$week_ago = datesub($date, 0, 0, $autopicked_payment_days ? $autopicked_payment_days : 90);


$logged_in_agent = $userd['uid'];

$real_agents = real_loan_agent($customer_id, $logged_in_agent);


$current_lo = $real_agents['LO'];
$current_co = $real_agents['CO'];
if ($co_is_also_lo == 1) {
    $current_lo = $userid;
    $current_co = $userid;
}


//die(errormes($current_lo.','.$current_co));

//$status = $_POST['status'];

////////////////////////
if ($customer_id > 0) {
    $user_ = fetchonerow('o_customers', "uid=$customer_id", "primary_product, primary_mobile, loan_limit, branch, status");
    $loan_limit = $user_['loan_limit'];
    $cust_branch = $user_['branch'];

    // check if branch is frozen
    $branch_det = fetchonerow('o_branches', "uid='$cust_branch'", "freeze, name");
    $branch_freeze = $branch_det['freeze'];
    $branch_name = trim($branch_det['name']);
    if (in_array($branch_freeze, ['MANUAL', 'BOTH'])) {
        exit(errormes("$branch_name branch manual loans disabled temporarily!"));
    }

    $primary_mobile = $user_['primary_mobile'];
    $primary_product = $user_['primary_product'];

    if ($lock_product == 1 && $primary_product != $product_id) {
        $product_name = fetchrow('o_loan_products', "uid=$primary_product AND status = 1", "name");
        exit(errormes("Please select allowed product: $product_name"));
    }

    $status = $user_['status'];
    if ($status != 1) {
        exit(errormes("Customer status is not Active"));
    }
    if ($loan_amount > $loan_limit) {
        exit(errormes("The customer's Loan Limit is. $loan_limit"));
    }

    // check for pending loan limit approval 
    $has_pending_approval = fetchmaxid("o_customer_limits", "customer_uid = $customer_id", "amount, status");
    $pending_approval_amt = $has_pending_approval['amount'] ?? 0;
    $limit_status = $has_pending_approval['status'] ?? 0;

    if (doubleval($pending_approval_amt > 0 && $limit_status === 2)) {
        $str_approval_amt = money($pending_approval_amt);
        exit(errormes("The customer has a pending loan limit of Ksh$str_approval_amt"));
    }
} else {
    //////------Invalid user ID
    exit(errormes("Please select a customer"));
}

///---------------------Check for custom scripts
$primary_product = $primary_product ? $primary_product : 1;
$scr = after_script($primary_product, "LOAN_CREATE");
if ($scr != '0') {
    include_once "../../$scr";
}

////------------------End of checking for custom scripts

if ($loan_amount > 0) {
    if ($product_id > 0) {
        $prod = fetchonerow('o_loan_products', "uid=$product_id AND status = 1", "period, period_units, min_amount, max_amount, pay_frequency, percent_breakdown, status");
        $prod_period = $prod['period'];
        $prod_period_units = $prod['period_units'];
        $min_amount = $prod['min_amount'];
        $max_amount = $prod['max_amount'];
        $prod_pay_frequency = $prod['pay_frequency'];
        $prod_percent_breakdown = $prod['percent_breakdown'];
        $status = $prod['status'];
        if ($status != 1) {
            exit(errormes("Loan Product is Invalid"));
        }
        if (($min_amount > $loan_amount) || ($max_amount < $loan_amount)) {
            exit(errormes("The Product allows loan amounts between $min_amount and $max_amount"));
        }
    } else {
        exit(errormes("Please select a Product"));
    }
} else {
    exit(errormes("Please enter a Valid Amount"));
}


$has_loan = checkrowexists('o_loans', "customer_id = $customer_id AND status in (1,2,3,4,7,8,9,10)");
//$has_loan = checkrowexists('o_loans', "customer_id= $customer_id AND disbursed = 1 AND paid = 0");
if ($has_loan == 1) {
    exit(errormes("The customer has existing Loan"));
}

$total_loans_taken = countotal_withlimit('o_loans', "customer_id = $customer_id AND disbursed = 1", "uid", "1000");
if ($has_archive == 1) {
    $total_loans_archive = countotal_archive('o_loans', "customer_id = $customer_id AND disbursed = 1", "uid", "1000");
    $total_loans_taken = $total_loans_archive + $total_loans_taken;
}


///////// Check if customer has account repayable and repaid reconciled
$scr2 = after_script($primary_product, "CHECK_ACCOUNT_RECONCILIATION");
if ($scr2 != '0') {
    include_once "../../$scr2";
}
///////// End Check if customer has account repayable and repaid reconciled 


//////-------------Check if customer is required to pay any amount before getting a loan
///
///
/// --------------Check all upfront fees

$upfronts = fetchtable('o_addons', "paid_upfront=1", "uid", "asc", "10", "uid, amount, amount_type, applicable_loan");
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
            if ($total_loans_taken < $applicable_loan) {
                $total_upfront += $a_amount;
            }
        }
    }
}
//die(errormes(give_loan($customer_id, $primary_product, $loan_amount, 'MANUAL')));
//////--------Check all upfront deduction fees
$upfront_deducts = fetchtable('o_addons', "deducted_upfront=1", "uid", "asc", "10", "uid, amount, amount_type, applicable_loan");
$total_upfront_deducts = 0;
while ($upd = mysqli_fetch_array($upfront_deducts)) {
    $aid = $upd['uid'];
    $product_addon_ = fetchrow('o_product_addons', "addon_id='$aid' AND status=2 AND product_id='$product_id'", "uid");
    if ($product_addon_ > 0) {
        $upfront_addon = $upd['uid'];
        $applicable_loan = $upd['applicable_loan'];
        $amount = $upd['amount'];
        $amount_type = $upd['amount_type'];

        if ($amount_type == 'FIXED_VALUE') {
            $a_amount = $amount;
        } else {
            $a_amount = $loan_amount * ($amount / 100);
        }


        if ($applicable_loan == 0) {
            $total_upfront_deducts += $a_amount;
        } else {
            if ($total_loans_taken < $applicable_loan) {
                $total_upfront_deducts += $a_amount;
            }
        }
    }
}



///////////////------------------------------

//==== Hook for insurance fee check before loan creation
//==== script expect $total_upfront variable
$check_insurance_fee_src = after_script($primary_product, "CHECK_INSURANCE_FEE");
if ($check_insurance_fee_src != '0') {
    //== avail required variable(s) before including the script
    $customerID = $customer_id;
    include_once "../../$check_insurance_fee_src";
}

//=== End of insurance fee check

$paid = totaltable('o_incoming_payments', "(mobile_number='$primary_mobile' OR customer_id = $customer_id) AND loan_id=0 AND payment_category in (0, 1, 2, 4) AND status=1 AND payment_date >= '$week_ago'", "amount");
$total_repaid = totaltable('o_loans', "disbursed=1 AND paid=1 AND status!=0 AND customer_id='$customer_id'", "total_repaid");
$total_repayable = totaltable('o_loans', "disbursed=1 AND paid=1 AND status!=0 AND customer_id='$customer_id'", "total_repayable_amount");
$overpayments = false_zero($total_repaid - $total_repayable);

if (($paid + $overpayments) < $total_upfront) {
    $balance = money($total_upfront - $paid);
    $resp_mess = $charge_insurance_fee ? "An upfront of $balance needs to be paid to cover insurance fee" : "An upfront fee of $balance needs to be paid";
    die(errormes($resp_mess));
} else {
    $update_repayment = 1;
}


/*
/// -------------End of check all upfront fees
$upfront = fetchonerow('o_addons',"paid_upfront=1","uid, amount, amount_type, applicable_loan");
$upfront_addon = $upfront['uid'];
$applicable_loan = $upfront['applicable_loan'];
if($upfront_addon > 0){
    $product_addon = fetchrow('o_product_addons',"addon_id='$upfront_addon' AND status=1 AND product_id='$product_id'","uid");
    if($product_addon > 0){
        ////////-----Upfront exists check if its paid
        $needs_upfront = 0;
        if($applicable_loan == 1)
        {
            ////////////-----Just for first loan
            if($total_loans_taken < 1){
                $needs_upfront = 1;
            }
        }
        else if($applicable_loan == 0){
            //////-----All loans need upfront payment
                 $needs_upfront = 1;
        }
        if($needs_upfront == 1) {

            $addon_amount = $upfront['amount'];
            $paid = totaltable('o_incoming_payments', "(mobile_number='$primary_mobile' OR customer_id = $customer_id) AND loan_id=0 AND status=1", "amount");
            ///-----Check if there are older loans in archive
            if($has_archive == 1){
                $total_archive_loans = total_archive_loans($customer_id);
                if($total_archive_loans > 0){
                    ////----Has an earlier loan cleared
                    $paid = $addon_amount;
                }
            }
            ///----------------------
            if ($paid < $addon_amount) {
                die(errormes("An upfront fee of $addon_amount needs to be paid before loan is created"));
                exit();
            } else {
                $update_repayment = 1; ////Later update the fee with current loan
            }

            if($has_archive == 1){
                $total_archive_loans = total_archive_loans($customer_id);
                if($total_archive_loans > 0){
                    ////----Has an earlier loan cleared
                   $update_repayment = 0;
                }
            }
        }
    }
}
*/
////--------Check if loan is in right denomination
$deno = denomination_okey($product_id, $loan_amount);

if ($deno[0] == 0) {
    die(errormes("Denomination not valid. Use multiples of " . $deno[1]));
}




$disbursed_amount = $loan_amount - $total_upfront_deducts;      /////Calculated from product
$period = $prod_period;                /////From Product
$period_units = $prod_period_units;         //////From product
$payment_frequency = $prod_pay_frequency;    ///////From product
$payment_breakdown = $prod_percent_breakdown;    //////From Product
$total_instalments = total_instalments($period, $period_units, $payment_frequency);         //////Calculated from product
$total_instalments_paid = 0.00;  /////Initialization
$current_instalment = 1;         ////Initialization
$given_date = $date;         ////Initialization
$next_due_date = next_due_date($given_date, $period, $period_units, $payment_frequency);         ////Calculated from product
$final_due_date = final_due_date($given_date, $period, $period_units);         ////Calculated from product
$transaction_date = $fulldate;         ////Initialization
$added_date = $fulldate;
$loan_stage_d = fetchminid('o_product_stages', "product_id='$product_id' AND status=1", "stage_order, uid");
$loan_stage = $loan_stage_d['stage_id'];

////////////////////////
$group = 0;
if ($loan_type == 0) {
    exit(errormes("Please select loan type"));
}
if ($loan_type == 3) {
    $group = fetchrow('o_group_members', "customer_id='$customer_id' AND status=1", "group_id");
    if ($group > 0) {
    } else {
        die(errormes("Customer is not in any group"));
    }
}

$enc_phone = hash('sha256', $primary_mobile);
$fds = array('customer_id', 'group_id', 'account_number', 'enc_phone', 'product_id', 'loan_type', 'loan_amount', 'disbursed_amount', 'period', 'period_units', 'payment_frequency', 'payment_breakdown', 'total_instalments', 'total_instalments_paid', 'current_instalment', 'given_date', 'next_due_date', 'final_due_date', 'added_by', 'current_lo', 'current_co', 'current_branch', 'added_date', 'loan_stage', 'application_mode', 'status');
$vals = array("$customer_id", "$group", "$primary_mobile", "$enc_phone", "$product_id", "$loan_type", "$loan_amount", "$disbursed_amount", "$period", "$period_units", "$payment_frequency", "$payment_breakdown", "$total_instalments", "$total_instalments_paid", "$current_instalment", "$given_date", "" . move_to_monday($next_due_date) . "", "" . move_to_monday($final_due_date) . "", "$added_by", "$current_lo", "$current_co", "$cust_branch", "$added_date", "$loan_stage", "$application_mode", "1");
$create = addtodb('o_loans', $fds, $vals);
//$total_loans = customer_loans($customer_id);
updatedb("o_customers", "primary_product = $product_id", "uid = $customer_id");
if ($create == 1) {
    echo sucmes('Loan Created Successfully');
    $proceed = 1;
    $created_loan = fetchmax('o_loans', "customer_id='$customer_id' AND product_id='$product_id'", "uid", "uid");
    $loan_id = $created_loan['uid'];
    ////////-----------Add Automatic AddOns
    $addons = fetchtable('o_product_addons', "product_id='$product_id' AND status in (1,2)", "addon_id", "asc", "20", "addon_id");
    while ($addon = mysqli_fetch_array($addons)) {
        $addon_id = $addon['addon_id'];
        $automatic = fetchrow('o_addons', "uid='$addon_id' AND from_day = 0", "automatic");
        if ($automatic == 1) {
            apply_loan_addon_to_Loan($addon_id, $loan_id, false);
        }
    }

     ////=== $charge_insurance_fee and $insurance_addon_id are availed by check insurance hook
    if (!empty($charge_insurance_fee)) {
        // add insurance fee to the loan
        apply_loan_addon_to_Loan($insurance_addon_id, $loan_id, false);
    }

    if ($update_repayment == 1 && $skip_auto_allocate != 1) {
        ///-------Update the upfront code with latest loan ID
        $balance = loan_balance($loan_id);
        $updaterep = updatedb('o_incoming_payments', "loan_id = $loan_id, loan_balance = $balance", "(mobile_number='$primary_mobile' OR customer_id = $customer_id) AND loan_id=0 AND payment_category in (0, 1, 2, 4) AND status=1 AND payment_date >= '$week_ago'");
        // echo $updaterep.'Update repayment';
    }
    recalculate_loan($loan_id);

    ///---------------------Check for after loan creation script
    $primary_product = $primary_product ? $primary_product : 1;
    $scr = after_script($primary_product, "LOAN_CREATE_SUCCESS");
    if ($scr != '0') {
        include_once "../../$scr";
    }

    //== Hook Check if Loan Should be marked as Platinum
    $check_platinum_loan_src = after_script($primary_product, "CHECK_PLATINUM_LOAN");
    if ($check_platinum_loan_src != '0') {
        //== avail required variable(s) before including the script
        $customerID = $customer_id;
        $loanID = $loan_id;
        $currentLoanAmount = $loan_amount;
        include_once "../../$check_platinum_loan_src";
    }

    //== Hook Check if Customer Should be Unmarked as Dormant
    $check_unset_dormant_src = after_script($primary_product, "DORMANT_REACTIVATION");
    if ($check_unset_dormant_src != '0') {
        //== avail required variable(s) before including the script
        $customerID = $customer_id;
        include_once "../../$check_unset_dormant_src";
    }
    ////------------------End of after loan creation script

} else {
    echo errormes('Unable to Create Loan' . $create);
}
?>

<script>
    if ('<?php echo $proceed; ?>' === "1") {
        setTimeout(function() {
            gotourl('loans?loan=<?php echo encurl($loan_id); ?>&just-created');
        }, 1500);
    }
</script>