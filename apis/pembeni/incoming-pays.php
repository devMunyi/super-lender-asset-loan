<?php
session_start();
$company = $_GET['c'] ?? 0;
$data = file_get_contents('php://input');

$logFile = 'payments_log.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.'Company-'.$company."->".date('Y-m-d H:i:s')."\n");
fclose($log);

$result = json_decode(trim($data), true);

$TransID = $result['TransID'];
$TransTime = $result['TransTime'];
$TransAmount = $result['TransAmount'];
$BillRefNumber = $result['BillRefNumber'];
$OrgAccountBalance = $result['OrgAccountBalance'];
$name = addslashes($result['FirstName'] . ' ' . $result['MiddleName'] . ' ' . $result['LastName']);
$MSISDN = $result['MSISDN'];

$branch_id = 0;
$latest_loan_id = 0;


if ($TransAmount > 0) {


    ////////-------------------------Get the company Details



    include_once("../configs/20200902.php");
    // $_SESSION['db_name'] = $db_;
    include_once("../configs/conn.inc");
    include_once("../php_functions/functions.php");
    ///////-------------------------End of get company details
    ////----------Update Paybill Balance 9243937
    if ($OrgAccountBalance > 0) {
        updatedb('o_summaries', "value_='$OrgAccountBalance', last_update='$fulldate'", "uid=2");
    }

    $customer_id = 0;
    $latest_loan_id = 0;
    $check_alternative = 0;
    $check_referee = 0;
    $account_number = $MSISDN;
    $current_co = 0;
    $current_agent = 0;


    function msisdnHashHasConsecutiveAsterisks(string $input): bool
    {
        return preg_match('/\*{3,}/', $input) === 1;
    }

    function cleanMsisdnHashIsolatedCase(string $input): string
    {
        // Step 1: Replace 3 or more consecutive asterisks with a % for sql purpose
        $input = preg_replace('/\*{3,}/', '%', $input);

        // Step 2: Remove all spaces (any whitespace)
        $input = preg_replace('/\s+/', '', $input);

        // Step 3: Optionally trim (if you want to be very safe)
        return trim($input);
    }

    $msisdnHasConsecutiveAsterisks = msisdnHashHasConsecutiveAsterisks($MSISDN);
    $orMSISDN = "";
    $andName = "";
    if ($msisdnHasConsecutiveAsterisks) {
        $MSISDN_ = cleanMsisdnHashIsolatedCase($MSISDN);
        $orMSISDN = " OR enc_phone LIKE '$MSISDN_' ";
        $andName = " AND full_name LIKE '%$name%' ";
    }


    $customer_det = fetchonerow('o_customers', "(enc_phone='$MSISDN' $orMSISDN) AND status != 0 $andName", "uid, branch, primary_mobile");
    $customer_id = intval($customer_det['uid']);
    $primary_mobile = $customer_det['primary_mobile'];

    if ($customer_id == 0) {

        $customer_det_query = "SELECT customer_id as uid, current_branch as branch, account_number as primary_mobile FROM o_loans WHERE (enc_phone = '$MSISDN' $orMSISDN) AND disbursed = 1 order by uid desc limit 1";

            $customer_query_result = mysqli_query($con, $customer_det_query);
            $customer_det = mysqli_fetch_assoc($customer_query_result);

            $customer_id = intval($customer_det['uid']);
            $primary_mobile = $customer_det['primary_mobile'];
    }

    ////---------
    if ($customer_id > 0) { /////Customer exists
        $branch_id = $customer_det['branch'];
        ///////----Check if has a Loan
        $latest_loan = fetchmaxid('o_loans', "customer_id='$customer_id' AND disbursed=1 AND paid=0", "uid, product_id, account_number, current_co, current_agent");
        $latest_loan_id = $latest_loan['uid'];
        if ($latest_loan_id > 1) {
            ////----You have loan details
            $product_id = $latest_loan['product_id'];
            $account_number = $latest_loan['account_number'];
            $current_co = $latest_loan['current_co'];
            $current_agent = $latest_loan['current_agent'];
        } else {
            $check_alternative = 1;
        }
    } else {
        ///---Check alternative number
        $check_alternative = 1;
    }

    /////////************************** Alternative check
    if ($check_alternative == 1) {
        $cust = fetchrow('o_customer_contacts', "(enc_phone='$MSISDN' $orMSISDN) AND status = 1", "customer_id");
        if ($cust > 0) {
            $customer_id = $cust;
            $customer_det = fetchonerow('o_customers', "uid='$customer_id'", "uid, branch");
            $branch_id = $customer_det['branch'];

            $latest_loan = fetchmaxid('o_loans', "customer_id='$customer_id' AND disbursed=1 AND paid=0", "uid, product_id, account_number, current_co, current_agent");
            $latest_loan_id = $latest_loan['uid'];
            if ($latest_loan_id > 1) {
                $product_id = $latest_loan['product_id'];
                $account_number = $latest_loan['account_number'];
                $current_co = $latest_loan['current_co'];
                $current_agent = $latest_loan['current_agent'];
                ////----You have loan details
            } else {
                $check_referee = 1;
            }
        } else {
            $check_referee = 1;
        }
    }

    /////******************************Referee check
    ///--------Pending check referee
    if ($current_agent > 0) {
    } else {
        $current_agent = $current_co;
    }

    ////---------------End of new simplepay check



    $payment_method = 3;
    $fds = array('customer_id', 'branch_id', 'payment_method', 'mobile_number', 'amount', 'transaction_code', 'loan_id', 'loan_code', 'payment_date', 'recorded_date', 'record_method', 'added_by', 'collected_by', 'comments', 'status');
    $vals = array("$customer_id", "$branch_id", "$payment_method", "$primary_mobile", "$TransAmount", "$TransID", "" . false_zero($latest_loan_id) . "", "$BillRefNumber", "$TransTime", "$fulldate", "API", "0", "$current_agent", "From API ($name)", "1");


    $save = addtodb('o_incoming_payments', $fds, $vals);
    if ($save == 1) {
        echo "Save Payment for Loan $latest_loan_id: $save";
        if ($latest_loan_id > 0) {
            recalculate_loan($latest_loan_id);

            $ld = fetchonerow("o_incoming_payments", "transaction_code = '$TransID'", "uid");
            $max_pid = $ld["uid"];

            $balance = loan_balance($latest_loan_id);
            $scr = after_script($product_id, "SPLIT_OVERPAYMENT");
            if ($scr != '0' && $balance < 0) {
                include_once "../$scr";
            } else {
                $balanceup = updatedb("o_incoming_payments", "loan_balance = '$balance'", "uid = $max_pid");
            }
            // echo "Update Balance: $balance, Payment Id:$max_pid, $ Result: $balanceup, Loan: $latest_loan_id ";
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
