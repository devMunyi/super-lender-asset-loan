<?php

///==== Expected: $primary_product, $customer_id, $primary_mobile to be available within the calling script
if ($customer_id > 0) {

    $customer_loan_uids = table_to_array('o_loans', "customer_id = $customer_id", "1000000", "uid");
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
        exit(errormes("Account Reconciliation Required for loan ids: $unreconciled_loans_str"));
    }
}
