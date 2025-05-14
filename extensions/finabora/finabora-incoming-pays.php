<?php
session_start();

$company = 6;
$data  = file_get_contents('php://input');

$logFile = 'log.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.'Company-'.$company."->".date('Y-m-d H:i:s')."\n");
fclose($log);
$result = json_decode(trim($data), true);

$TransID = $result['TransID'];
$TransTime = $result['TransTime'];
$TransAmount = $result['TransAmount'];
$BillRefNumber = $result['BillRefNumber'];
$OrgAccountBalance = $result['OrgAccountBalance'];
$name = $result['FirstName'].' '.$result['MiddleName'].' '.$result['LastName'];
$MSISDN = $result['MSISDN'];

$branch_id = 0;
$latest_loan_id = 0;

echo $BillRefNumber;

if($TransAmount > 0) {



////////-------------------------Get the company Details
     {

        include_once("../php_functions/functions.php");
        include_once("../configs/auth.inc");
         include_once '../configs/20200902.php';
         $_SESSION['db_name'] = $db_;
         include_once("../php_functions/functions.php");
         include_once("../configs/conn.inc");
          {



            include_once("../configs/conn.inc");
///////-------------------------End of get company details
            ////----------Update Paybill Balance
            if ($OrgAccountBalance > 0) {
                updatedb('o_summaries', "value_='$OrgAccountBalance', last_update='$fulldate'", "uid=2");
            }

            $customer_det = fetchonerow('o_customers', "primary_mobile='" . make_phone_valid($BillRefNumber) . "' OR national_id='$BillRefNumber'", "uid, branch");
            $customer_id = $customer_det['uid'];
            if ($customer_id > 0) {
                $branch_id = $customer_det['branch'];
                $latest_loan = fetchmaxid('o_loans', "customer_id='$customer_id' AND disbursed=1 AND paid=0", "uid, product_id, account_number");
                $latest_loan_id = $latest_loan['uid'];
                $product_id = $latest_loan['product_id'];
                $account_number = $latest_loan['account_number'];
            } else {
                $latest_loan_id = 0;
            }
            $payment_method = 3;
            $fds = array('customer_id', 'branch_id', 'payment_method', 'mobile_number', 'amount', 'transaction_code', 'loan_id','loan_code', 'payment_date', 'recorded_date', 'record_method', 'added_by', 'comments', 'status');
            $vals = array("$customer_id", "$branch_id", "$payment_method", "$MSISDN", "$TransAmount", "$TransID", "".false_zero($latest_loan_id)."", "$BillRefNumber", "$TransTime", "$fulldate", "API", "0", "From API", "1");


            $save = addtodb('o_incoming_payments', $fds, $vals);
            if ($save == 1) {
                echo "Save Payment for Loan $latest_loan_id: $save";
                if ($latest_loan_id > 0) {
                    recalculate_loan($latest_loan_id);

                    $ld = fetchonerow("o_incoming_payments", "transaction_code = '$TransID'", "uid");
                    $max_pid = $ld["uid"];

                    $balance = loan_balance($latest_loan_id);
                    $balanceup = updatedb("o_incoming_payments", "loan_balance = '$balance'", "uid = $max_pid");
                    echo "Update Balance: $balance, Payment Id:$max_pid, $ Result: $balanceup, Loan: $latest_loan_id ";
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


        }
    }
}
else{
    echo "Amount invalid";
}