<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$customer_id = intval($_POST['cid'] ?? 0);
if ($customer_id > 0) {
    $customer_id = decurl($customer_id);
} else {
    echo "<i>Customer ID is invalid</i>";
    die();
}

$customerLoanUIDs = table_to_array("o_loans", "customer_id = $customer_id AND status != 0 AND disbursed = 1", "10000", "uid");

if (count($customerLoanUIDs) == 0) {
    echo "<i>No loans found for this customer</i>";
    die();
}

// Customer Information Query
$customerDetSQL = "SELECT full_name, national_id, primary_mobile FROM o_customers WHERE uid = $customer_id LIMIT 1";
$customerDetResult = mysqli_query($con, $customerDetSQL);
$customerDet = mysqli_fetch_assoc($customerDetResult);
$customer_name = $customerDet['full_name'] ?? 'N/A';
$customer_nid = $customerDet['national_id'] ?? 'N/A';


?>
<style>
    .my-header {
        text-align: center;
    }

    .my-header img {
        width: 100px;
    }

    .my-table {
        width: 100%;
        border-collapse: collapse;
        border-color: green;
        margin-top: 20px;
    }

    .my-table th,
    .my-table td {
        padding: 4px;
        /* border: 2px solid #ddd; */
        border: 1px solid #2c3e50;
    }

    .my-table th {
        /* background-color: #f2f2f2; */
        background-color: #2c3e50;
        color: white;
    }

    .text-left {
        text-align: left;
    }

    .text-right {
        text-align: right;
    }

    .bold {
        font-weight: bold;
    }
</style>
    <table class="my-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Transaction Code</th>
                <th>Loan ID</th>
                <th>Entry</th>
                <th>Credit</th>
                <th>Debit</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total_disbursed = 0;
            $total_paid = 0;
            $starting_balance = 0;

            $addonNames = table_to_obj("o_addons", "status = 1", "1000", "uid", "name");
            $deductionNames = table_to_obj("o_deductions", "status = 1", "1000", "uid", "name");

            foreach ($customerLoanUIDs as $loan_id) {
                // Fetch loan details
                $loanSQL = "SELECT loan_amount, given_date FROM o_loans WHERE uid = $loan_id AND status != 0";
                $loan = mysqli_fetch_assoc(mysqli_query($con, $loanSQL));
                $principal = $loan['loan_amount'] ?? 0;
                $starting_balance += $principal;
                $total_disbursed += $principal;

                // Display principal row
                echo "<tr>
                <td>" . date('d-M-Y', strtotime($loan['given_date'])) . "</td>
                <td></td>
                <td>$loan_id</td>
                <td>Principal</td>
                <td>" . money($principal) . "</td>
                <td>0</td>
                <td>" . money($starting_balance) . "</td>
            </tr>";

                // Fetch add-ons
                $addonSQL = "SELECT addon_id, addon_amount, added_date FROM o_loan_addons WHERE loan_id = $loan_id AND `status` = 1 ORDER BY added_date ASC";
                $addonResult = mysqli_query($con, $addonSQL);
                while ($addon = mysqli_fetch_assoc($addonResult)) {
                    $addon_amount = $addon['addon_amount'];
                    $starting_balance += $addon_amount;
                    $addon_id = $addon['addon_id'];
                    $addon_name  = $addonNames[$addon_id] ?? '';
                    echo "<tr>
                    <td>" . date('d-M-Y', strtotime($addon['added_date'])) . "</td>
                    <td></td>
                    <td>$loan_id</td>
                    <td>$addon_name</td>
                    <td>" . money($addon_amount) . "</td>
                    <td>0</td>
                    <td>" . money($starting_balance) . "</td>
                </tr>";
                }

                // Fetch Deductions
                $deductionSQL = "SELECT deduction_id, deduction_amount, added_date FROM o_loan_deductions WHERE loan_id = $loan_id AND status = 1 ORDER BY added_date ASC";

                $deductionResult = mysqli_query($con, $deductionSQL);
                while ($deduction = mysqli_fetch_assoc($deductionResult)) {
                    $deduction_amount = $deduction['deduction_amount'];
                    $starting_balance -= $deduction_amount;
                    $deduction_id = $deduction['deduction_id'];
                    $deduction_name  = $deductionNames[$deduction_id] ?? '';
                    echo "<tr>
                    <td>" . date('d-M-Y', strtotime($deduction['added_date'])) . "</td>
                    <td></td>
                    <td>$loan_id</td>
                    <td>$deduction_name</td>
                    <td>0</td>
                    <td>" . money($deduction_amount) . "</td>
                    <td>" . money($starting_balance) . "</td>
                </tr>";
                }




                // Fetch repayments
                $paymentSQL = "SELECT amount, transaction_code, payment_date FROM o_incoming_payments WHERE loan_id = $loan_id AND status = 1 ORDER BY payment_date ASC";
                $paymentResult = mysqli_query($con, $paymentSQL);
                while ($payment = mysqli_fetch_assoc($paymentResult)) {
                    $amount = $payment['amount'];
                    $starting_balance -= $amount;
                    $total_paid += $amount;

                    echo "<tr>
                    <td>" . date('d-M-Y', strtotime($payment['payment_date'])) . "</td>
                    <td>" . $payment['transaction_code'] . "</td>
                    <td>$loan_id</td>
                    <td>Repayment</td>
                    <td>0</td>
                    <td>" . money($amount) . "</td>
                    <td>" . money($starting_balance) . "</td>
                </tr>";
                }
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-left bold">Total Disbursed </td>
                <td colspan="3" class="bold">KSH: <?php echo money($total_disbursed); ?></td>
            </tr>
            <tr>
                <td colspan="4" class="text-left bold">Total Paid Amount</td>
                <td colspan="3" class="bold">KSH: <?php echo money($total_paid); ?></td>
            </tr>
            <tr>
                <td colspan="4" class="text-left bold">Total Loan Balance </td>
                <td colspan="3" class="bold" class="text-left"> KSH:<?php echo money($starting_balance); ?></td>
            </tr>
        </tfoot>

    </table>

<?php
include_once("../../configs/close_connection.inc");
?>