<?php
session_start();
include_once("php_functions/functions.php");
include_once("configs/conn.inc");

$customer_id = intval($_GET['cid'] ?? 0);
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
$company_name = fetchrow('platform_settings', 'uid=1', 'name');
$brand_name = $brand_name ? $brand_name : $company_name;
$statement_color = $statement_color ? $statement_color : '#2c3e50';
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Customer Statment</title>
    <!-- Tell the browser to be responsive to screen width -->
    <?php
    include_once('header_includes.php');
    ?>
</head>
<style>
    .my-header {
        text-align: center;
    }

    .statement_logo {
        width: 100px;
    }

    .my-table {
        width: 100%;
        border-collapse: collapse;
        border-color: <?php echo $statement_color; ?>;
        margin-bottom: 2.5rem;
        margin-bottom: 2.5rem;
    }

    .my-table th,
    .my-table td {
        padding: 4px;
        /* border: 2px solid #ddd; */
        border: 1px solid <?php echo $statement_color; ?>;
    }

    .bg-color,
    .my-table th {
        background-color: <?php echo $statement_color; ?> !important;
        color: white !important;
    }

    /* Print-specific styles */
    @media print {

        /* Force background colors and white font for print */
        .bg-color,
        .my-table th {
            background-color: <?php echo $statement_color; ?> !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Optional: Hide unnecessary elements */
        .pointer {
            display: none;
        }
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

    .pointer {
        cursor: pointer;
    }
</style>

<body class="container">
    <div class="scroll-hor">

        <div style="display: flex; justify-content: center; gap: 2.5rem;">
            <div>
                <img src="dist/img/icon.png" alt="Company Logo" class="statement_logo">
            </div>

            <div>
                <h2>Customer Statement</h2>
                <p>
                    Name: <?php echo $customer_name; ?><br>
                    ID Number: <?php echo $customer_nid; ?><br>
                    Statement Date: <?php echo date('d/m/Y'); ?>
                </p>
            </div>

            <div class="pointer" onclick="window.print()" style="align-self: flex-end;">
                <i class="fa fa-print"></i> Print</a>
            </div>
        </div>
        <div class="col-md-8 col-md-offset-2">
            <table class="my-table">
                <thead>
                    <tr>
                        <th style="width: 100px;">Date</th>
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

                    foreach ($customerLoanUIDs as $loan_id) {
                        $loan_components = array(
                            'Principal' => array(),
                            'Registration Fee' => array(),
                            'Processing Fee' => array(),
                            'Base Interest' => array(),
                            'Daily Interest' => array(),
                            'Penalty' => array(),
                            'Block Addon' => array(),
                        );

                        $loan_repayments = array();

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


                        $addonNames = table_to_obj("o_addons", "status = 1", "1000", "uid", "name");

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

                    <tr>
                        <td colspan="7" class="text-center bg-color">
                            Disclaimer: This statement is produced for your personal use and is not transferable. If you have any questions, please contact <?php echo $brand_name; ?>.
                        </td>
                    </tr>
                </tfoot>

            </table>

        </div>

    </div>

    <?php
    include_once("footer_includes.php");
    ?>
</body>