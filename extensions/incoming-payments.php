<?php
session_start();

$data  = file_get_contents('php://input');

$logFile = 'log.txt';
$log = fopen($logFile, "a");
fwrite($log, $data . 'Company-' . $company . "->" . date('Y-m-d H:i:s') . "\n");
fclose($log);
$result = json_decode(trim($data), true);

$TransID = $result['TransID'];
$TransTime = $result['TransTime'];
$TransAmount = $result['TransAmount'];
$BillRefNumber = $result['BillRefNumber'];
$OrgAccountBalance = $result['OrgAccountBalance'];
$name = $result['FirstName'] . ' ' . $result['MiddleName'] . ' ' . $result['LastName'];
$MSISDN = $result['MSISDN'];

$branch_id = 0;
$latest_loan_id = 0;

echo $TransAmount;

if ($TransAmount > 0) {



    ////////-------------------------Get the company Details


    include_once("../configs/20200902.php");
    include_once("../php_functions/functions.php");
    include_once("../configs/auth.inc");


    //  $db = $company_d['db_name'];
    $_SESSION['db_name'] = $db_;
    include_once("../configs/conn.inc");
    ///////-------------------------End of get company details
    ////----------Update Paybill Balance
    if ($OrgAccountBalance > 0) {
        updatedb('o_summaries', "value_='$OrgAccountBalance', last_update='$fulldate'", "uid=2");
    }


    $orpayer = "";
    if ((input_length($BillRefNumber, 2)) == 0) {
        $orpayer = " OR primary_mobile='" . make_phone_valid($MSISDN) . "'";
    }

    ////----Group loans, check if user has specified the group
    $group_id = 0;
    $customer_det = fetchonerow('o_customers', "primary_mobile='" . make_phone_valid($BillRefNumber) . "' OR national_id='$BillRefNumber'", "uid, branch, primary_product");
    $customer_id = $customer_det['uid'];
    $primary_product = $customer_det['primary_product'];

    // optionally use $scr to handle overpayment splitting
    $primary_product = $primary_product ? $primary_product : 1;

    if ($group_loans > 0) {
        $group_id = fetchrow('o_customer_groups', "group_name='$BillRefNumber'", "uid");
        if ($group_id > 0) {
        } else {
            $group_id = fetchrow('o_group_members', "status=1 AND customer_id='$customer_id'", "group_id");
        }
    }


    $customer_group = fetchrow('o_group_members', "customer_id='$customer_id' AND status=1", "group_id");
    if ($customer_id > 0) {
        $branch_id = $customer_det['branch'];
        $latest_loan = fetchmaxid('o_loans', "customer_id='$customer_id' AND disbursed=1 AND paid=0", "uid, product_id, account_number, current_co");
        $latest_loan_id = $latest_loan['uid'];
        $product_id = $latest_loan['product_id'];
        $account_number = $latest_loan['account_number'];
        $current_co = $latest_loan['current_co'];
    } else {
        $latest_loan_id = 0;
        $current_co = 0;
    }
    if ($customer_group > 0) {
        $latest_loan_id = 0;
    }
    $payment_method = 3;
    $fds = array('customer_id', 'branch_id', 'group_id', 'payment_method', 'mobile_number', 'amount', 'transaction_code', 'loan_id', 'loan_code', 'payment_date', 'recorded_date', 'record_method', 'added_by', 'comments', 'status');
    $vals = array("$customer_id", "$branch_id", "$group_id", "$payment_method", "$MSISDN", "$TransAmount", "$TransID", "" . false_zero($latest_loan_id) . "", "$BillRefNumber", "$TransTime", "$fulldate", "API", "$current_co", "From API", "1");


    $save = addtodb('o_incoming_payments', $fds, $vals);
    if ($save == 1) {
        echo "Save Payment for Loan $latest_loan_id: $save";
        if ($latest_loan_id > 0) {
            recalculate_loan($latest_loan_id, true);

            $ld = fetchonerow("o_incoming_payments", "transaction_code = '$TransID'", "uid");
            $max_pid = $ld["uid"];

            $balance = loan_balance($latest_loan_id);
            // $balanceup = updatedb("o_incoming_payments", "loan_balance = '$balance'", "uid = $max_pid");

            /////======= optionally handle overpayment splitting
            $scr = after_script($primary_product, "SPLIT_PAYMENT");
            if ($scr !== 0) {
                // availing all expected variables
                $transaction_code = $TransID;
                $loan_id = $latest_loan_id;
                $payment_for = 1;

                if(validate_phone(make_phone_valid($MSISDN)) == 1){
                    $mobile_number = make_phone_valid($MSISDN);
                }elseif(validate_phone(make_phone_valid($BillRefNumber) == 1)){
                    $mobile_number = make_phone_valid($BillRefNumber);
                }
                $payment_date = $TransTime;
                $record_method = "API";
                $added_by = $current_co;
                $comments = "FROM API";
                $status = 1;

                include_once("../$scr");
            } else {
                updatedb("o_incoming_payments", "loan_balance = $balance", "uid = $max_pid");
            }

            ////-------End optionally handle of overpayment splitting

            ///----Notify USER
            if ($balance > 0) {
                $pay_state = 'PARTIAL_PAYMENT';
                product_notify($product_id, 0, $pay_state, 0, $latest_loan_id, $account_number);
            } else {
                $pay_state = 'FULL_PAYMENT';
                product_notify($product_id, 0, $pay_state, 5, $latest_loan_id, $account_number);
            }
        }
    } else {
        echo "Save Payment for Loan $latest_loan_id:$save";
    }
} else {
    echo "Amount invalid";
}

include_once("../configs/close_connection.inc");