<?php
session_start();
header('Content-Type: application/json');

// // include external files
include_once("../configs/20200902.php");
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

// set variables
$start_date = "2023-12-01";
$end_date = "2024-01-31";
// print start and end date
// echo "Start Date: $start_date, End Date: $end_date <br>";

////==== fetch loans id aligning with passed payment date interval
$fetch_loans_within = table_to_array('o_incoming_payments', "payment_date BETWEEN '$start_date' AND '$end_date' AND loan_id > 0 $andbranch_pay", "1000000", "loan_id");

////==== filter out duplicate loan ids before imploading
$fetch_loans_within = array_unique($fetch_loans_within);

////==== implode array to string
$loans_ids_list = implode(',', $fetch_loans_within);

////==== fetch all loans given at specific date interval: $start_date
// and $end_date
// store loan details
$loan_tracking_repayment_obj = [];

///==== fetch loans
$loans_given = fetchtable('o_loans', "uid IN ($loans_ids_list)", "uid", "asc", "10000000", "uid, loan_amount, total_addons, status");

///==== loop through loans to form array
while ($l = mysqli_fetch_array($loans_given)) {
    $loan_id = $l['uid'];
    $loan_amount = $l['loan_amount'];
    $total_addons = $l['total_addons'];
    $status = $l['status'];
    

    // check if loan id exists in loan tracking repayment array
    if ($loan_id > 0 && !array_key_exists($loan_id, $loan_tracking_repayment_obj)) {
        // add loan id to loan tracking repayment array
        $loan_tracking_repayment_obj[$loan_id] = [];
    }

    // prepare addon 6 array
    $addon_array_six = [
        "6" => [
            "amount" => $loan_amount,
            "paid" => 0,
            "balance" => $loan_amount
        ],
    ];

    // push addon 6 array to loan tracking repayment array
    array_push($loan_tracking_repayment_obj[$loan_id], $addon_array_six);
}


// echo json_encode($loan_tracking_repayment_obj, JSON_PRETTY_PRINT);
// return;


// now loop throw loans given at specific date interval: $start_date and $end_date to form array above
// fetching associated loan addons

$fetch_loans_addons = fetchtable('o_loan_addons', "loan_id IN ($loans_ids_list) AND status = 1", "uid", "asc", "10000000", "uid, addon_id, loan_id, addon_amount");

// loop through loan addons now updating $loan_tracking_repayment_obj key 'addons' => [] array with addons id and amount, and set paid = 0
while ($la = mysqli_fetch_array($fetch_loans_addons)) {

    // ======= initialize variables to use
    $loan_id = $la['loan_id'];
    $addon_id = $la['addon_id'];
    $amount = $la['addon_amount'];

    // ========= handle the actual addon id key using cases
    // 1) '1' => 'Membership Fee', <=> [2] => (Membership Fee)
    if (in_array($addon_id, $membership_or_registration_fee_ids)) {
        $addon_key = "1";
    }
    // 2) '2' => 'Processing Fee', <=> [5] => (Processing Fee)
    else if (in_array($addon_id, $processing_fee_ids)) {
        $addon_key = "2";
    }
    // 3) '3' => Penalties, <=> [3, 7, 11, 12] => (DD+1, DD+46, DD+90?, All Penalties (Temporary)?)
    else if (in_array($addon_id, $penalty_ids)) {
        $addon_key =  "3";
    }
    // 4) '4' => 'AfterDue Interest', <=> [4, 9] => (Daily Interest(0.08%), Daily Penalty (Day 31-45)?)
    else if (in_array($addon_id, $afterdue_interest_ids)) {
        $addon_key = "4";
    }
    // 5) '5' => 'Base Interest', <=> [1, 6, 8] => (Loan Interest, Weekly Interest?, Base Interest (24%)?)
    else if (in_array($addon_id, $base_interest_ids)) { 
        $addon_key = "5";
    }
    // 6) '6' => 6 <=> [] => (disbursed_amount)
    else if ($addon_id == 6) {
        // $addon_id = 6;
    } else {
        // continue to the next iteration
        continue;
    }


    //////// ==== injecting addons to $loan_tracking_repayment_obj
    if (array_key_exists($loan_id, $loan_tracking_repayment_obj)) {

        // check if addon id exists in loan tracking repayment array
        if (array_key_exists($addon_key, $loan_tracking_repayment_obj[$loan_id])) {
            // do amount update
            $loan_tracking_repayment_obj[$loan_id][$addon_key]['amount'] += $amount;
        } else {
            // prepare addon array with a dynamic key
            $addon_array = [
                $addon_key => [
                    "amount" => $amount,
                    "paid" => 0,
                    "balance" => $amount
                ],
            ];
            // push it to loan tracking repayment array
            array_push($loan_tracking_repayment_obj[$loan_id], $addon_array);
        }                  
    }
}

// sort addons using key in ascending order
foreach ($loan_tracking_repayment_obj as $loan_id => $loan) {
    ksort($loan_tracking_repayment_obj[$loan_id]);
}


echo json_encode($loan_tracking_repayment_obj, JSON_PRETTY_PRINT);
return;


////==== fetch all payments made at specific date interval: $start_date and $end_date where loan_id > 0
$period_payments = fetchtable('o_incoming_payments', "payment_date BETWEEN '$start_date' AND '$end_date' AND loan_id > 0 $andbranch_pay", "uid", "asc", "10000000", "loan_id, payment_date, amount");

//// payments category details
$payments_cat_det = [];

////===== loop through payments
while ($p = mysqli_fetch_array($period_payments)) {
    $payment_date = $p['payment_date'];
    $loan_id = intval($p['loan_id'] ?? 0);
    $payment_amount = $p['amount'] ?? 0;

    if (!$payment_amount > 0 || !$loan_id > 0) {
        continue;
    }

    //// get addons from loan_tracking_repayment_obj
    $addons = $loan_tracking_repayment_obj[$loan_id];

    //// write foreach loop to iterate the $addons array
    foreach ($addons as $addon_key2 => $addon_arr) {
        $addon_amount = $addon_arr['amount'];
        $paid = $addon_arr['paid'];
        $addon_bal = $addon_arr['balance'];
        $payment_category = $addon_key2;


        if ($payment_amount > 0) {
            // filter to determine when all adons have been paid
            $addons = array_filter($addons, function ($addon) {
                return $addon['balance'] > 0;
            });

            // initialize amount to map with payment category as 0
            $amount = 0;
            if (count($addons) == 0) {
                // allocate the remaining payment to category principal
                $payment_category = 6;

                //==== update payment details for disbursed amount
                $amount = $payment_amount;

                // target category principal and update the payment details
                $addons[$payment_category]['paid'] += $payment_amount;


                // reset payment amount to 0
                $payment_amount = 0;
            } else {

                // full repayment
                if ($payment_amount >= $addon_bal) {
                    $paid += $addon_bal;

                    // set amount to map with payment category
                    $amount = $addon_bal;
                    $addon['paid'] = $paid;

                    $payment_amount -= $addon_bal;
                    $addon['balance'] = 0;
                }
                // addon to be paid partially
                else {
                    $paid += $payment_amount;
                    $addon['paid'] = $paid;

                    $addon_bal -= $payment_amount;
                    $addon['balance'] = $addon_bal;

                    // set amount to map with payment category
                    $amount = $payment_amount;

                    // means all money has been used up
                    $payment_amount = 0;
                }


                if ($amount > 0) {
                    //==== update $payments_cat_det accordingly
                    if (array_key_exists($payment_date, $payments_cat_det)) {
                        // check if payment category exists in payments details array
                        if (array_key_exists($payment_category, $payments_cat_det[$payment_date])) {
                            // add amount to payment category
                            $payments_cat_det[$payment_date][$payment_category] += $amount;
                        } else {
                            // add payment category to payments details array
                            $payments_cat_det[$payment_date][$payment_category] = $amount;
                        }
                    } else {
                        // add payment date to payments details array
                        $payments_cat_det[$payment_date] = [$payment_category => $amount];
                    }
                }
            }
        } else {
            break;
        }
    }
}

// sort date in ascending order
ksort($payments_cat_det);


// loop through payments_cat_det to display the information on a table:

?>

<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Membership Fee</th>
                <th>Processing Fee</th>
                <th>Penalty</th>
                <th>After-Due Interest</th>
                <th>Base Interest</th>
                <th>Principal</th>
                <th>Totals</th>
            </tr>
        </thead>
        <tbody>
            <?php

            // initializing variables
            $total_membership_fee = $total_processing_fee = $total_penalty = $total_after_due_interest = $total_base_interest = $total_principal = $total_all = 0;

            foreach ($payments_cat_det as $date => $payment) {
                $membership_fee = $payment[1] ?? 0;
                $processing_fee = $payment[2] ?? 0;
                $penalty = $payment[3] ?? 0;
                $after_due_interest = $payment[4] ?? 0;
                $base_interest = $payment[5] ?? 0;
                $principal = $payment[6] ?? 0;

                $total = $membership_fee + $processing_fee + $penalty + $after_due_interest + $base_interest + $principal;

                $total_membership_fee += $membership_fee;
                $total_processing_fee += $processing_fee;
                $total_penalty += $penalty;
                $total_after_due_interest += $after_due_interest;
                $total_base_interest += $base_interest;
                $total_principal += $principal;
                $total_all += $total;

                echo "<tr>
                    <td>$date</td>
                    <td>" . money($membership_fee) . "</td>
                    <td>" . money($processing_fee) . "</td>
                    <td>" . money($penalty) . "</td>
                    <td>" . money($after_due_interest) . "</td>
                    <td>" . money($base_interest) . "</td>
                    <td>" . money($principal) . "</td>
                    <td>" . money($total) . "</td>
                </tr>";
            }

            ?>
        </tbody>

        <tfoot>
            <tr>
                <th>Total</th>
                <th><?php echo money($total_membership_fee); ?></th>
                <th><?php echo money($total_processing_fee); ?></th>
                <th><?php echo money($total_penalty); ?></th>
                <th><?php echo money($total_after_due_interest); ?></th>
                <th><?php echo money($total_base_interest); ?></th>
                <th><?php echo money($total_principal); ?></th>
                <th><?php echo money($total_all); ?></th>
            </tr>
        </tfoot>
    </table>
</div>