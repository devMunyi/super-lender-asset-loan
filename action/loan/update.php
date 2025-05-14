<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
$resend_loan = permission($userd['uid'], 'o_loans', "0", "update_");
if ($resend_loan != 1) {
    die(errormes("You don't have permission to update loan"));
    exit();
}

$loan_id = $_POST['lid'];
$current_lo = $_POST['current_lo'];
$current_co = $_POST['current_co'];
$current_branch = $_POST['current_branch'];
$disbursed_date = $_POST['disbursed_date'];
$next_due_date = $_POST['next_due_date'];
$final_due_date = $_POST['final_due_date'];
$income_earned = $_POST['income_earned'];
$loan_amount = $_POST['loan_amount'];
$disbursed_amount = $_POST['disbursed_amount'];
$loan_prod = $_POST['loan_prod'];
$group_id = $_POST['group_id'];






///////----------------Validation
if ($loan_id > 0) {
} else {
    die(errormes("Loan ID needed"));
    exit();
}
if ($loan_amount < 1) {
    die(errormes("Loan Amount is needed"));
    exit();
}
if ($disbursed_amount < 1) {
    die(errormes("Disbursed Amount invalid"));
    exit();
}
if ($loan_prod > 0) {
} else {
    die(errormes("Product ID is needed"));
    exit();
}

////-------------------When loan product is updated
$current_loan = fetchonerow('o_loans', "uid=" . decurl($loan_id), "product_id");
$current_product = $current_loan['product_id'];

/// -------------------
$prod = fetchonerow('o_loan_products', "uid='$current_product'", "pay_frequency");
$period = datediff3($disbursed_date, $final_due_date);
$pay_frequency = $prod['pay_frequency'];

$total_instalments = total_instalments($period, 1, $pay_frequency);

/*

$loan_id = $_POST['lid'];
$current_lo = $_POST['current_lo'];
$current_co = $_POST['current_co'];
$current_branch = $_POST['current_branch'];
$disbursed_date = $_POST['disbursed_date'];
$next_due_date = $_POST['next_due_date'];
$final_due_date = $_POST['final_due_date'];
$income_earned = $_POST['income_earned'];
$loan_amount = $_POST['loan_amount'];
$disbursed_amount = $_POST['disbursed_amount'];
$loan_prod = $_POST['loan_prod'];
$group_id = $_POST['group_id'];

*/

$loan_b4_update = fetchonerow('o_loans', "uid=" . decurl($loan_id), "current_lo, current_co, current_branch, given_date, next_due_date, final_due_date, income_earned, loan_amount, disbursed_amount, product_id, group_id");



$update_loan = updatedb('o_loans', "income_earned='$income_earned', group_id='$group_id', product_id='$loan_prod', given_date='$disbursed_date', next_due_date='$next_due_date', final_due_date='$final_due_date', current_lo=\"$current_lo\", current_co='$current_co', current_branch='$current_branch', total_instalments='$total_instalments', loan_amount='$loan_amount', disbursed_amount='$disbursed_amount'", "uid=" . decurl($loan_id));
if ($update_loan == 1) {

    // prepare event
    $event = "Loan updated by [" . $userd['name'] . "(" . $userd['email'] . ")]. Details -> ";
    $orginal_event = $event;

    $current_lo_b4_update = $loan_b4_update['current_lo'] ?? 0;
    if ($current_lo_b4_update != $current_lo) {

        $loan_officers = table_to_obj("o_users", "uid IN ($current_lo_b4_update, $current_lo)", "100", "uid", "name");

        $event .= "Loan Officer: {$loan_officers[$current_lo_b4_update]} to {$loan_officers[$current_lo]}, ";
    }

    $current_co_b4_update = $loan_b4_update['current_co'] ?? 0;
    if ($current_co_b4_update != $current_co) {

        $coNames = table_to_obj("o_users", "uid IN ($current_co_b4_update, $current_co)", "100", "uid", "name");

        $event .= "Credit Officer: {$coNames[$current_co_b4_update]} to {$coNames[$current_co]}, ";
    }

    if ($loan_b4_update['current_branch'] != $current_branch) {
        $branch_b4_update = $loan_b4_update['current_branch'] ?? 0;
        $branchNames = table_to_obj("o_branches", "uid IN ($branch_b4_update, $current_branch)", "100", "uid", "name");

        $event .= "Branch: {$branchNames[$branch_b4_update]} to {$branchNames[$current_branch]}, ";
    }

    if ($loan_b4_update['given_date'] != $disbursed_date) {
        $event .= "Disbursed Date: " . $loan_b4_update['given_date'] . " to $disbursed_date, ";
    }

    if ($loan_b4_update['next_due_date'] != $next_due_date) {
        $event .= "Next Due Date: " . $loan_b4_update['next_due_date'] . " to $next_due_date, ";
    }

    if ($loan_b4_update['final_due_date'] != $final_due_date) {
        $event .= "Final Due Date: " . $loan_b4_update['final_due_date'] . " to $final_due_date, ";
    }


    if ($loan_b4_update['income_earned'] != $income_earned) {
        $event .= "Income Earned: " . $loan_b4_update['income_earned'] . " to $income_earned, ";
    }

    if (doubleval($loan_b4_update['loan_amount']) != doubleval($loan_amount)) {
        $event .= "Loan Amount: " . $loan_b4_update['loan_amount'] . " to $loan_amount, ";
    }

    if (doubleval($loan_b4_update['disbursed_amount']) != doubleval($disbursed_amount)) {
        $event .= "Disbursed Amount: " . $loan_b4_update['disbursed_amount'] . " to $disbursed_amount, ";
    }

    if ($loan_b4_update['product_id'] != $loan_prod) {
        $product_b4_update = $loan_b4_update['product_id'] ?? 0;
        $productNames = table_to_obj("o_loan_products", "uid IN ($product_b4_update, $loan_prod)", "100", "uid", "name");

        $event .= "Product: {$productNames[$loan_b4_update['product_id']]} to {$productNames[$loan_prod]}, ";
    }

    if ($loan_b4_update['group_id'] != $group_id) {
        $event .= "Group ID: " . $loan_b4_update['group_id'] . " to $group_id, ";
    }


    if($orginal_event == $event){
        $event = "Loan update triggered by [".$userd['name']."(".$userd['email'].")]. No changes captured";
    }

    // remove trailing comma
    $event = rtrim(trim($event), ',');

    // store event
    store_event('o_loans', decurl($loan_id), "$event");

    ///----Change disbursed amount in Mpesa queues
    $disb = updatedb('o_mpesa_queues', "amount='$disbursed_amount'", "loan_id=" . decurl($loan_id) . " AND status!=2");
    ///-----Remove existing addons if loan product is different
    if ($current_product != $loan_prod) {
        $rem = updatedb('o_loan_addons', "status=0", "loan_id='" . decurl($loan_id) . "' AND status=1");
    }
    ///----Recalculate addons
    $addons = fetchtable('o_loan_addons', "loan_id='" . decurl($loan_id) . "'  AND status=1", "uid", "asc", "100", "addon_id");
    while ($a = mysqli_fetch_array($addons)) {
        $addon_id = $a['addon_id'];
        apply_loan_addon_to_Loan($addon_id, decurl($loan_id), false);
    }
    recalculate_loan(decurl($loan_id), true);
    $proceed = 1;
    echo sucmes("Success! Loan updated, please recheck addons");
} else {
    die(errormes("Oops!.An error occured. Try again"));
}



///////------------End of validation
?>
<script>
    modal_hide();
    if ('<?php echo $proceed; ?>') {
        setTimeout(function() {
            reload();
        }, 1000);
    }
</script>