<?php

include_once("../php_functions/functions.php");
include_once("../configs/20200902.php");
include_once("../configs/conn.inc");
// include_once("../vendor/autoload.php");
include_once("../dompdf/autoload.inc");
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

$loan_id = intval($_GET['loan_id'] ?? 0);
if ($loan_id > 0) {
    $loan_id = decurl($loan_id);
} else {
    echo "<i>Loan ID is invalid</i>";
    die();
}


$loan_components = array(
    'Principal' => array(), // will store amount and applied_date(same as give_date)
    'Registration Fee' => array(), // will store amount and applied_date(same as give_date)
    'Processing Fee' => array(), // will store amount and applied_date(same as give_date)
    'Base Interest' => array(), // will store amount and applied_date(same as give_date)
    'Daily Interest' => array(), // will store amount and applied_date
    'Penalty' => array(), // will store amount and applied_date
    'Block Addon' => array(), // will store amount and applied_date
);

$loan_repayments = array(); // store amount, transaction code and payment_date

try {
    // select loan with uid = 5211210 and disbursed = 1 and status != 0
    $loansql = "SELECT loan_amount, customer_id, account_number, given_date FROM o_loans WHERE uid = $loan_id AND status != 0";

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
        $addon_amount = $addon['addon_amount'] ?? '';
        $added_date = $addon['added_date'] ?? '';

        // Registration Fee
        if ($addon_id == 1) {
            $loan_components['Registration Fee'][] = array('amount' => $addon_amount, 'applied_date' => $added_date);
        }

       
        // Processing Fee
        if ($addon_id == 2) {
            $loan_components['Processing Fee'][] = array('amount' => $addon_amount, 'applied_date' => $added_date);
        }

        // Base Interest
        if ($addon_id == 3) {
            $loan_components['Base Interest'][] = array('amount' => $addon_amount, 'applied_date' => $added_date);
        }

        // Daily Interest
        if ($addon_id == 4) {
            $loan_components['Daily Interest'][] = array('amount' => $addon_amount, 'applied_date' => $added_date);
        }

        // Penalty
        if ($addon_id == 5) {
            $loan_components['Penalty'][] = array('amount' => $addon_amount, 'applied_date' => $added_date);
        }

        // Block Addon
        if ($addon_id == 6) {
            $loan_components['Block Addon'][] = array('amount' => $addon_amount, 'applied_date' => $added_date);
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
    exit($e->getMessage());
}


function formatDate($dateString, $inputFormat, $outputFormat)
{
    $date = DateTime::createFromFormat($inputFormat, $dateString);
    if ($date === false) {
        return "Invalid date format";
    }
    return $date->format($outputFormat);
}

// for a htlm title like: Margaret Wambui Muigai 254724916362 Loan Statement
$title = $customer_name . " " . $load_id . " Loan Statement";


$html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Statement</title>
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="../bower_components/bootstrap/dist/css/bootstrap.min.css">

    <style>
        .container {
            background-color: #fff !important;
        }
    </style>
</head>

<body class="jumbotron">

    <div class="">
        <div class="container">
            <div class="row">
                <div class="col-md-2">

                </div>
                <div class="col-md-8">
                    <img height="80" src="../dist/img/icon.png" alt="Logo" class="img-responsive center-block">
                </div>

                <div class="col-md-2"></div>
            </div>

            <div class="row">
                <div class="col-md-12">


                    <div class="text-center">
                        <h3>Loan ' .$loan_id. ' Statement</h3>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <table class="table table-responsive table-bordered table-hover table-condensed">
                        <tr>
                            <th>Customer Name:</th>
                            <td>'.$customer_name.'</td>
                        </tr>
                        <tr>
                            <th>Primary Mobile:</th>
                            <td>'.$primary_mobile.'</td>
                        </tr>
                        <tr>
                            <th>National ID:</th>
                            <td>'.$customer_nid.'</td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-2"></div>
            </div>

            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <table class="table table-responsive table-bordered table-hover table-condensed">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Transaction Code</th>
                                <th>Entries</th>
                                <th>Amount</th>
                                <th>Balance</th>
                            </tr>
                        </thead>
                        <tbody>';
                            $starting_balance = $principal;
                            foreach ($loan_components as $component => $details) {
                                foreach ($details as $detail) {
                                    // skip increment if $component is principal
                                    if ($component != 'Principal') {
                                        $starting_balance += $detail['amount'];
                                    }

                                    $applied_date = $detail['applied_date'] ?? '';
                                    if ($applied_date != '') {
                                        $applied_date = formatDate($applied_date, 'Y-m-d', 'd-M-Y');
                                    }
                                    $html .= "<tr>
                                    <td>".$applied_date ."</td>
                                    <td></td>
                                    <td>" . $component . "</td>
                                    <td>" . money($detail['amount'] ?? 0) . "</td>
                                    <td>" . money($starting_balance ?? 0) . "</td>
                                    </tr>";
                                }
                            }

                            foreach ($loan_repayments as $repayment) {
                                $starting_balance -= $repayment['amount'] ?? 0;
                                $repayment_date = $repayment['payment_date'] ?? '';
                                if ($repayment_date != '') {
                                    $repayment_date = formatDate($repayment_date, 'Y-m-d', 'd-M-Y');
                                }

                                $html .=  "<tr>
                                <td>" . $repayment_date . "</td>
                                <td>" . $repayment['transaction_code']. "</td>
                                <td>Repayment</td>
                                <td>" . money($repayment['amount'] ?? 0) . "</td>
                                <td>" . money($starting_balance) . "</td>
                                </tr>";
                            }
                            $html .= '</tbody>
                    </table>
                </div>
                <div class="col-md-2"></div>
            </div>

            <div class="row">
                <div class="col-md-2"></div>
                <div class="col-md-8">
                    <div class="text-center">
                        <p>Powered By Collection Department</p>
                        <p><a href="javascript:void(0)">Jaza Capital</a></p>
                    </div>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>

        <script src="../bower_components/jquery/dist/jquery.min.js"></script>
</body>
</html>';

echo $html;

// reference the Dompdf namespace
// use Dompdf\Dompdf;

// // instantiate and use the dompdf class
// $dompdf = new Dompdf();
// $dompdf->loadHtml($html);
// $dompdf->setPaper('A4', 'landscape');
// $dompdf->render();
// $title = $title ?? "Loan_Statement";
// $dompdf->stream($title . ".pdf", array("Attachment" => 0));