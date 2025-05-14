<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$loan_id = intval($_GET['loan_id'] ?? 0);
if($loan_id > 0){
    $loan_id = decurl($loan_id);
}else{
    echo "<i>Loan ID is invalid</i>";
    die();
}


$loan_components = array(
    'Principal' => array(), // will store amount and applied_date(same as give_date)
    'Registration Fee' => array(), // will store amount and applied_date(same as give_date)
    'Base Interest' => array(), // will store amount and applied_date(same as give_date)
    'Daily Interest' => array(), // will store amount and applied_date
    'Penalty 1' => array(), // will store amount and applied_date
    'Penalty 2' => array(), // will store amount and applied_date
    'Penalty 3' => array(), // will store amount, transaction code and payment_date
);

$loan_repayments = array(); // store amount, transaction code and payment_date

try {
    $loansql = "SELECT loan_amount, customer_id, account_number, given_date FROM o_loans WHERE uid = $loan_id AND disbursed = 1 AND status != 0";

    $loanresult = mysqli_query($con, $loansql);

    if (!$loanresult) {
        throw new Exception("Query failed: " . mysqli_error($con));
    }


    // Fetch the loan details
    $loan = mysqli_fetch_assoc($loanresult);
    $principal = $loan['loan_amount'] ?? '';
    $given_date = $loan['given_date'] ?? '';
    $customer_id = $loan['customer_id'] ?? '';
    $primary_mobile = $loan['account_number'] ?? '';


    // push it to accordingly to loan_components
    $loan_components['Principal'][] = array('amount' => $principal, 'applied_date' => $given_date);


    // fetch loan interests
    $addonSQL = "SELECT addon_id, addon_amount, DATE(added_date) AS added_date FROM o_loan_addons WHERE loan_id = $loan_id AND `status` = 1 ORDER BY added_date ASC";

    $addonResult = mysqli_query($con, $addonSQL);
    while ($addon = mysqli_fetch_assoc($addonResult)) {
        $addon_id = $addon['addon_id'];

        // base interest
        if ($addon_id == 1) {
            $loan_components['Base Interest'][] = array('amount' => $addon['addon_amount'] ?? '', 'applied_date' => $addon['added_date'] ?? '');
        }

        // daily interest
        if ($addon_id == 7) {
            $loan_components['Daily Interest'][] = array('amount' => $addon['addon_amount'] ?? '', 'applied_date' => $addon['added_date'] ?? '');
        }


        if ($addon_id == 2) {
            $loan_components['Registration Fee'][] = array('amount' => $addon['addon_amount'] ?? '', 'applied_date' => $addon['added_date'] ?? '');
        }

        if ($addon_id == 4) {
            $loan_components['Penalty 1'][] = array('amount' => $addon['addon_amount'] ?? '', 'applied_date' => $addon['added_date'] ?? '');
        }

        if ($addon_id == 5) {
            $loan_components['Penalty 2'][] = array('amount' => $addon['addon_amount'] ?? '', 'applied_date' => $addon['added_date'] ?? '');
        }

        if ($addon_id == 6) {
            $loan_components['Penalty 3'][] = array('amount' => $addon['addon_amount'] ?? '', 'applied_date' => $addon['added_date'] ?? '');
        }
    }



    // fetch loan payments
    $paymentSQL = "SELECT amount, transaction_code, payment_date FROM o_incoming_payments WHERE loan_id = $loan_id AND status = 1 ORDER BY payment_date ASC";

    $paymentResult = mysqli_query($con, $paymentSQL);

    while ($payment = mysqli_fetch_assoc($paymentResult)) {
        $loan_repayments[] = array('amount' => $payment['amount'], 'transaction_code' => $payment['transaction_code'], 'payment_date' => $payment['payment_date']);
    }

    // to select full_name and national_id
    $customerDetSQL = "SELECT full_name, national_id FROM o_customers WHERE uid = $customer_id OR primary_mobile = '$primary_mobile' LIMIT 1";

    $customerDetResult = mysqli_query($con, $customerDetSQL);
    $customerDetResult = mysqli_fetch_assoc($customerDetResult);
    $customer_name = $customerDetResult['full_name'] ?? '';
    $customer_nid = $customerDetResult['national_id'] ?? '';
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
} 


function formatDate($dateString, $inputFormat, $outputFormat) {
    $date = DateTime::createFromFormat($inputFormat, $dateString);
    if ($date === false) {
        return "Invalid date format";
    }
    return $date->format($outputFormat);
}

$title = $customer_name . " " . $customer_nid . " Loan Statement";
?>



<!-- <h3><?php // echo $title ?></h3> -->

<table class="table table-responsive table-bordered font-14 table-hover">
    <thead>
        <tr>
            <th>Date</th>
            <th>Transaction Code</th>
            <th>Entries</th>
            <th>Amount</th>
            <th>Balance</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $starting_balance = $principal;
        foreach ($loan_components as $component => $details) {
            foreach ($details as $detail) {
                // skip increment if $component is principal
                if ($component != 'Principal') {
                    $starting_balance += $detail['amount'];
                }

                $applied_date = $detail['applied_date'] ?? '';
                if($applied_date != ''){
                    $applied_date = formatDate($applied_date, 'Y-m-d', 'd-M-Y');
                }
                echo "<tr>";
                echo "<td>" . $applied_date . "</td>";
                echo "<td></td>";
                echo "<td>" . $component . "</td>";
                echo "<td>" . money($detail['amount'] ?? 0). "</td>";
                echo "<td>" . money($starting_balance ?? 0) . "</td>";
                echo "</tr>";
            }
        }
        ?>

        <?php
        foreach ($loan_repayments as $repayment) {
            $starting_balance -= $repayment['amount'] ?? 0;
            $repayment_date = $repayment['payment_date'] ?? '';
            if($repayment_date != ''){
                $repayment_date = formatDate($repayment_date, 'Y-m-d', 'd-M-Y');
            }

            echo "<tr>";
            echo "<td>" . $repayment_date . "</td>";
            echo "<td>" . $repayment['transaction_code'] ?? '' . "</td>";
            echo "<td>Repayment</td>";
            echo "<td>" . money($repayment['amount'] ?? 0) . "</td>";
            echo "<td>".money($starting_balance)."</td>";
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

<?php
// include close connection
include_once("../configs/close_connection.inc");
?>