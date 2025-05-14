<?php

session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();
$customer_id = $_POST['customer_id'] ?? 0;

if ($customer_id == 0) {
    echo "<tr><td colspan='9'><i>No Records Found</i></td></tr>";
    exit();
} else {
    $customer_id = decurl($customer_id);
}

//-----------------------------Reused Query
$o_pays_ = fetchtable('o_incoming_payments', "customer_id=$customer_id AND status > 0", "uid", "desc", "100", "*");
$methods_array = table_to_obj('o_payment_methods', "uid>0", "1000", "uid", "name");
if (mysqli_num_rows($o_pays_) > 0) {
    while ($q = mysqli_fetch_array($o_pays_)) {
        $uid = $q['uid'];
        $payment_method = $q['payment_method'];
        $pay_meth = $methods_array[$payment_method];
        $mobile_number = $q['mobile_number'];
        $amount = money($q['amount']);
        $transaction_code = $q['transaction_code'];
        $payment_date = $q['payment_date'];
        $record_method = $q['record_method'];


        $loan_id = $q['loan_id'];
        if ($loan_id > 0) {
            $loan_balance_ = $q['loan_balance'];
            $loan_balance = money($loan_balance_);
        } else {
            $loan_balance = "<i>Unspecified</i>";
        }

        $row .= "<tr>
                <td>$uid</td>
                <td><span class=\"text-bold text-blue font-16\">$amount</span></td>
                <td><span>$pay_meth</span>
                </td>
                <td>$record_method</td>
                <td>$transaction_code</td>
                <td><span>$loan_balance</span><br/></td>
                <td><span>$payment_date</span><br/> <span class=\"text-orange font-13 font-bold\">" . fancydate($payment_date) . "</span></td>
                <td><span class=\"text-green\"><i class=\"fa fa-check\"></i> Successful</span></td>
                <td><span><a href=\"?repayment=" . encurl($uid) . "\"><span class=\"fa fa-eye text-green\"></span></a></span><h4></h4></td>
            </tr>";
    }
} else {
    $row = "<tr><td colspan='13'><i>No Records Found</i></td></tr>";
}
echo $row;

// include close connection
include_once("../../configs/close_connection.inc");
