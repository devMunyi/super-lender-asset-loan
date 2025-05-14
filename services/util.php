<?php

function splitOverpayment($data)
{

    global $fulldate;

    $random = generateRandomNumber(3);
    $trans_code_one = "SP-$random-" . $data['transaction_code'];
    $random = generateRandomNumber(3);
    $trans_code_two = "SP-$random-" . $data['transaction_code'];
    $parent_pid = intval($data['parent_pid']);

    // update parent payment entry to unallocated and split status
    updatedb("o_incoming_payments", "status = 2, loan_id = 0, loan_balance = 0", "uid = $parent_pid");


    // ===== Begin Record of first payment split to be retained by initial loan
    $first_split_loan_id = intval($data['loan_id']);
    $loan_code = trim($data['loan_code']);
    $amount_paid = intval($data['amount']);
    $balance = abs($data['balance']);
    $first_split_amount = $amount_paid - $balance;
    $second_split_amount = $balance;
    $customer_id = intval($data['customer_id']);
    $branch_id = intval($data['branch_id']);
    $group_id = intval($data['group_id']);
    $payment_method = intval($data['payment_method']) > 0 ? intval($data['payment_method']) : 2; // defaults to 3 if not specified, implying "Safaricom M-Pesa"
    $payment_for = intval($data['payment_for']) > 0 ? intval($data['payment_for']) : 1; // defaults to 1 if not specified, implying "Loan Repayment"
    $mobile_number = trim($data['mobile_number']);
    $payment_date = trim($data['payment_date']);
    $record_method = $data['record_method'] ? $data['record_method'] : 'MANUAL';
    $recorded_date = $fulldate;
    $added_by = intval($data['added_by']);
    $collected_by = intval($data['collected_by']);
    $comments = trim($data['comments']) . " , Overpayment Split";
    $status = intval($data['status']);


    if ($first_split_loan_id > 0 && $first_split_amount > 0) {
        $first_split_loan_bal = 0;
        $fds = array('customer_id', 'branch_id', 'group_id', 'payment_method', 'payment_category', 'mobile_number', 'amount', 'split_from', 'transaction_code', 'loan_id', 'loan_code', 'loan_balance', 'payment_date', 'record_method', 'recorded_date', 'added_by', 'collected_by', 'comments', 'status');
        $vals = array(
            $customer_id,
            $branch_id,
            $group_id,
            $payment_method,
            $payment_for,
            "$mobile_number",
            $first_split_amount,
            $parent_pid,
            "$trans_code_one",
            $first_split_loan_id,
            "$loan_code",
            $first_split_loan_bal,
            "$payment_date",
            "$record_method",
            "$recorded_date",
            $added_by,
            $collected_by,
            "$comments",
            $status
        );

        // Create a new record in the o_incoming_payments table
        $split1 = addtodb('o_incoming_payments', $fds, $vals);
        echo "split1 => $split1, first_split_loan_id => $first_split_loan_id </br>";
        recalculate_loan($first_split_loan_id, true);

        // ===== End Record of first payment split to be retained by initial loan

    }

    if ($second_split_amount > 0) {
        // ========== Begin Record of second payment split to be allocated to a different loan (defaults to loan_id = 0)
        $second_split_loan_id = 0;
        $second_split_loan_bal = 0;
        $fds = array('customer_id', 'branch_id', 'group_id', 'payment_method', 'payment_category', 'mobile_number', 'amount', 'split_from', 'transaction_code', 'loan_id', 'loan_code', 'loan_balance', 'payment_date', 'record_method', 'recorded_date', 'added_by', 'collected_by', 'comments', 'status');
        $vals = array(
            $customer_id,
            $branch_id,
            $group_id,
            $payment_method,
            $payment_for,
            "$mobile_number",
            $second_split_amount,
            $parent_pid,
            "$trans_code_two",
            $second_split_loan_id,
            "$loan_code",
            $second_split_loan_bal,
            "$payment_date",
            "$record_method",
            "$recorded_date",
            $added_by,
            $collected_by,
            "$comments",
            $status
        );

        // Create a new record in the o_incoming_payments table
        $split2 = addtodb('o_incoming_payments', $fds, $vals);
        echo "split2 => $split2, second_split_loan_id => $second_split_loan_id </br>";

        // ========== End Record of second payment split to be allocated to a different loan
    }
    //===== End overpayment split handler
}

function splitOverpayment_archive($data)
{

    global $fulldate;
    global $con2;

    $random = generateRandomNumber(3);
    $trans_code_one = "SP-$random-" . $data['transaction_code'];
    $random = generateRandomNumber(3);
    $trans_code_two = "SP-$random-" . $data['transaction_code'];
    $parent_pid = intval($data['parent_pid']);

    // update parent payment entry to unallocated and split status
    $sql = "UPDATE o_incoming_payments SET status = 2, loan_id = 0, loan_balance = 0 WHERE uid = $parent_pid";
    $update = mysqli_query($con2, $sql);
    // updatedb("o_incoming_payments", "status = 2, loan_id = 0, loan_balance = 0", "uid = $parent_pid");


    // ===== Begin Record of first payment split to be retained by initial loan
    $first_split_loan_id = intval($data['loan_id']);
    $loan_code = trim($data['loan_code']);
    $amount_paid = intval($data['amount']);
    $balance = abs($data['balance']);
    $first_split_amount = $amount_paid - $balance;
    $second_split_amount = $balance;
    $customer_id = intval($data['customer_id']);
    $branch_id = intval($data['branch_id']);
    $group_id = intval($data['group_id']);
    $payment_method = intval($data['payment_method']) > 0 ? intval($data['payment_method']) : 2; // defaults to 3 if not specified, implying "Safaricom M-Pesa"
    $payment_for = intval($data['payment_for']) > 0 ? intval($data['payment_for']) : 1; // defaults to 1 if not specified, implying "Loan Repayment"
    $mobile_number = trim($data['mobile_number']);
    $payment_date = trim($data['payment_date']);
    $record_method = $data['record_method'] ? $data['record_method'] : 'MANUAL';
    $recorded_date = $fulldate;
    $added_by = intval($data['added_by']);
    $collected_by = intval($data['collected_by']);
    $comments = trim($data['comments']) . " , Overpayment Split";
    $status = intval($data['status']);


    if ($first_split_loan_id > 0 && $first_split_amount > 0) {
        $first_split_loan_bal = 0;
        $fds = array('customer_id', 'branch_id', 'group_id', 'payment_method', 'payment_category', 'mobile_number', 'amount', 'split_from', 'transaction_code', 'loan_id', 'loan_code', 'loan_balance', 'payment_date', 'record_method', 'recorded_date', 'added_by', 'collected_by', 'comments', 'status');
        $vals = array(
            $customer_id,
            $branch_id,
            $group_id,
            $payment_method,
            $payment_for,
            "$mobile_number",
            $first_split_amount,
            $parent_pid,
            "$trans_code_one",
            $first_split_loan_id,
            "$loan_code",
            $first_split_loan_bal,
            "$payment_date",
            "$record_method",
            "$recorded_date",
            $added_by,
            $collected_by,
            "$comments",
            $status
        );

        // Create a new record in the o_incoming_payments table
        $sql = "INSERT INTO o_incoming_payments $fds VALUES $vals";
        $split1 = mysqli_query($con2, $sql);
        // $split1 = addtodb('o_incoming_payments', $fds, $vals);
        echo "split1 => $split1, first_split_loan_id => $first_split_loan_id </br>";
        recalculate_loan_archive($first_split_loan_id, true);

        // ===== End Record of first payment split to be retained by initial loan

    }

    if ($second_split_amount > 0) {
        // ========== Begin Record of second payment split to be allocated to a different loan (defaults to loan_id = 0)
        $second_split_loan_id = 0;
        $second_split_loan_bal = 0;
        $fds = array('customer_id', 'branch_id', 'group_id', 'payment_method', 'payment_category', 'mobile_number', 'amount', 'split_from', 'transaction_code', 'loan_id', 'loan_code', 'loan_balance', 'payment_date', 'record_method', 'recorded_date', 'added_by', 'comments', 'status');
        $vals = array(
            $customer_id,
            $branch_id,
            $group_id,
            $payment_method,
            $payment_for,
            "$mobile_number",
            $second_split_amount,
            $parent_pid,
            "$trans_code_two",
            $second_split_loan_id,
            "$loan_code",
            $second_split_loan_bal,
            "$payment_date",
            "$record_method",
            "$recorded_date",
            $added_by,
            $collected_by,
            "$comments",
            6
        );

        // Create a new record in the o_incoming_payments table
        $sql = "INSERT INTO o_incoming_payments $fds VALUES $vals";
        $split2 = mysqli_query($con2, $sql);
        echo "split2 => $split2, second_split_loan_id => $second_split_loan_id </br>";

        // ========== End Record of second payment split to be allocated to a different loan
    }
    //===== End overpayment split handler
}

function recalculate_loan_archive($loan_id, $force_recalc = false)
{
    global $date;
    global $con2;
    if (intval($loan_id) === 0) {
        return 1;
    }

    $sql = "SELECT loan_amount, disbursed_amount, final_due_date, disbursed, paid, `status` FROM o_loans WHERE uid = $loan_id";
    $result = mysqli_query($con2, $sql);
    $l = mysqli_fetch_assoc($result);

    /////////------------deductions
    /// ///---Check if loan is cleared and dont recalculate
    $cleared = $l['paid'];
    $disbursed_amount = $l['disbursed_amount'];
    $disbursed = $l['disbursed'];
    if ($cleared == 1 && $force_recalc == false) {
        return  0;
    } else {
        $deduction_total = loan_deductions_archive($loan_id);
        /////////------------AddOn total
        $addon_total = loan_addons_archive($loan_id);

        /////////------------Total Repaid
        $repaid_total = total_repaid_archive($loan_id);

        $total_repayable_amount = $l['loan_amount'] + $addon_total - $deduction_total;
        $loan_balance = $total_repayable_amount - $repaid_total;
        $final_due_date = $l['final_due_date'];
        $status = $l['status'];
        if ($loan_balance < 1) {
            $and_clear = " ,paid=1, status=5";
            $cleared_event = 1;
            // $loan_balance = 0;
        } else {
            // mark loan as overdue if final due date is passed & but not marked overdue
            if (new DateTime($final_due_date) < new DateTime($date) && $status == 3 && $disbursed == 1) {
                $and_clear = " ,paid=0, status=7";
                $cleared_event = 0;
            } else {
                // retain existing status
                $and_clear = "";
                $cleared_event = 0;
            }
        }

        if($cleared_event == 1){
           $cleared_date =", cleared_date='$date'";
        }
        else {
          $cleared_date = "";
        }

        $income_earned = false_zero($repaid_total - $disbursed_amount);

        $fds = "total_addons='$addon_total', total_deductions='$deduction_total', total_repaid='$repaid_total',  total_repayable_amount='$total_repayable_amount', income_earned='$income_earned',loan_balance='$loan_balance' $and_clear $cleared_date";

        $sql = "UPDATE o_loans SET $fds WHERE uid = $loan_id";
        $update = mysqli_query($con2, $sql);

        if ($cleared_event == 1 && $cleared == 0) {
            store_event('o_loans', $loan_id, "Loan cleared via loan recalculation");
        }

        ////////----------------Update total loans


        return $update;
    }
}
