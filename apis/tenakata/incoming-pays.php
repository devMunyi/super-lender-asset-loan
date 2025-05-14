<?php
session_start();

$company = $_GET['c'] ?? 0;
$data  = file_get_contents('php://input');

$company = 7;

/*$logFile = 'log.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.'Company-'.$company."->".date('Y-m-d H:i:s')."\n");
fclose($log); */
$result = json_decode(trim($data), true);

$TransID =  $result['TransID']; // transaction code
$TransTime = $result['TransTime']; // payment date
$TransAmount = $result['TransAmount'];
$BillRefNumber = $result['BillRefNumber'];
$OrgAccountBalance = $result['OrgAccountBalance'];
$name = $result['FirstName'] . ' ' . $result['MiddleName'] . ' ' . $result['LastName'];
$MSISDN = $result['MSISDN']; // mobile phone number

$branch_id = 0;
$latest_loan_id = 0;

echo $BillRefNumber;

if ($TransAmount > 0) {

    if ($company == 2) {
        ////-----For simplepay, send payments to new API, register a new callback url pointing to the new server


        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://simplepay.co.ke/lender/extensions/simplepay-incoming-pays',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '' . $data . '',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Cookie: PHPSESSID=sqiuiucom3r6rrs1boq4rkhl4v'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        // echo $response;


        die();
    }

    ////////-------------------------Get the company Details
    if ($company > 0) {
        if ($company == 4) {
            $db = 'tenova_db';
        }

        include_once("../php_functions/functions.php");
        // include_once("../configs/auth.inc");
        $company_d = company_details($company);
        if ($company_d['uid'] > 0) {

            //  $db = $company_d['db_name'];
            $_SESSION['db_name'] = $db;
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

            $phone_ = make_phone_valid($BillRefNumber);
            $customer_det = fetchonerow('o_customers', "national_id = '$BillRefNumber' OR primary_mobile = '$phone_'", "uid, branch, primary_mobile, primary_product");
            $customer_id = $customer_det['uid'];
            $primary_product = $customer_det['primary_product'];
            if ($customer_id > 0) {
                $branch_id = $customer_det['branch'];
                $MSISDN = $customer_det['primary_mobile'];
                $latest_loan = fetchmaxid('o_loans', "customer_id='$customer_id' AND disbursed=1 AND paid=0", "uid, product_id, account_number, current_co, current_agent");
                $latest_loan_id = $latest_loan['uid'];
                $product_id = $latest_loan['product_id'];
                $account_number = $latest_loan['account_number'];
                $current_co = $latest_loan['current_co'];
                $current_agent = $latest_loan['current_agent'];
            } else {
                $latest_loan_id = 0;
                $current_co = 0;
            }
            $payment_method = 3;
            if($current_agent > 0){}
            else{
                $current_agent = $current_co;
            }

            $fds = array('customer_id', 'branch_id', 'payment_method', 'mobile_number', 'amount', 'transaction_code', 'loan_id', 'loan_code', 'payment_date', 'recorded_date', 'record_method', 'added_by','collected_by', 'comments', 'status');
            $vals = array("$customer_id", "$branch_id", "$payment_method", "$MSISDN", "$TransAmount", "$TransID", "" . false_zero($latest_loan_id) . "", "$BillRefNumber", "$TransTime", "$fulldate", "API", "0","$current_agent", "From API", "1");


            $save = addtodb('o_incoming_payments', $fds, $vals);
            if ($save == 1) {
                echo "Save Payment for Loan $latest_loan_id: $save";
                if ($latest_loan_id > 0) {
                    recalculate_loan($latest_loan_id, true);

                    $ld = fetchonerow("o_incoming_payments", "transaction_code = '$TransID'", "uid");
                    $max_pid = $ld["uid"];
                    $balance = loan_balance($latest_loan_id);

                    // hook overpayment split handler if specified from the product settings
                    $scr = after_script($primary_product, "SPLIT_OVERPAYMENT");
                    if ($scr != '0' && $balance < 0) {
                        include_once "../$scr";
                    } else {
                        $balanceup = updatedb("o_incoming_payments", "loan_balance = '$balance'", "uid = $max_pid");
                    }
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
} else {
    echo "Amount invalid";
}
