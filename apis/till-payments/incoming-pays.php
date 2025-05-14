<?php
session_start();

$company = 6;
$data  = file_get_contents('php://input');

/* $logFile = 'log.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.'Company-'.$company."->".date('Y-m-d H:i:s')."\n");
fclose($log); */
$result = json_decode(trim($data), true);

$TransID = $result['TransID'];
$TransTime = $result['TransTime'];
$TransAmount = $result['TransAmount'];
$BillRefNumber = $result['BillRefNumber'];
$OrgAccountBalance = $result['OrgAccountBalance'];
$name = addslashes($result['FirstName'].' '.$result['MiddleName'].' '.$result['LastName']);
$MSISDN = $result['MSISDN'];

$branch_id = 0;
$latest_loan_id = 0;
$current_co = 0;
$current_agent = 0;

//echo $BillRefNumber;

if($TransAmount > 0) {



////////-------------------------Get the company Details
    {

        include_once("../php_functions/functions.php");
        include_once("../configs/auth.inc");
        include_once '../configs/20200902.php';
        $_SESSION['db_name'] = $db_;
        include_once("../configs/conn.inc");
        {

///////-------------------------End of get company details
            ////----------Update Paybill Balance
            if ($OrgAccountBalance > 0) {
                if($mpesa_one_account == 1){ ////----For paybill that is also disbursement source code, we update both values
                    $oruid = " or uid=1";
                }
                else{
                    $oruid = "";
                }

                updatedb('o_summaries', "value_='$OrgAccountBalance', last_update='$fulldate'", "uid=2 $oruid");
            }

            $customer_det = fetchonerow('o_customers', "primary_mobile='" . make_phone_valid($MSISDN) . "'", "uid, branch, full_name");
            $customer_id = $customer_det['uid'];
            if ($customer_id > 0) {
                $branch_id = $customer_det['branch'];
                $latest_loan = fetchmaxid('o_loans', "customer_id='$customer_id' AND disbursed=1 AND paid=0", "uid, product_id, account_number, current_co,current_agent, product_id");
                $latest_loan_id = $latest_loan['uid'];
                $product_id = $latest_loan['product_id'];
                $account_number = $latest_loan['account_number'];
                $current_co = $latest_loan['current_co'];
                $current_agent = $latest_loan['current_agent'];
                $client_name = $customer_det['full_name'];


            } else {
                $latest_loan_id = 0;
            }
            $payment_method = 3;
            if($current_agent > 0){
                $allocated_collector = $current_agent;  /////An allocated agent rather than current CO. For notications purposes
                /// We don't want too many notifications for all LOs
            }
            else{
                $allocated_collector = 0;
                $current_agent = $current_co;
            }

            $fds = array('customer_id', 'branch_id', 'payment_method', 'mobile_number', 'amount', 'transaction_code', 'loan_id','loan_code', 'payment_date', 'recorded_date', 'record_method', 'added_by', 'collected_by', 'comments', 'status');
            $vals = array("$customer_id", "$branch_id", "$payment_method", "$MSISDN", "$TransAmount", "$TransID", "".false_zero($latest_loan_id)."", "$BillRefNumber", "$TransTime", "$fulldate", "API", "0", "$current_agent","From API ($name)", "1");


            $save = addtodb('o_incoming_payments', $fds, $vals);
            if ($save == 1) {
                echo "Save Payment for Loan $latest_loan_id: $save";
                if ($latest_loan_id > 0) {
                    recalculate_loan($latest_loan_id);
                    $product_id = $latest_loan['product_id'];

                    $ld = fetchonerow("o_incoming_payments", "transaction_code = '$TransID'", "uid");
                    $max_pid = $ld["uid"];

                    $balance = loan_balance($latest_loan_id);

                    ////////////////////////////
                    // $balanceup = updatedb("o_incoming_payments", "loan_balance = '$balance'", "uid = $max_pid");

                    // hook overpayment split handler if specified from the product settings
                    $scr = after_script($product_id, "SPLIT_OVERPAYMENT");
                    if ($scr != '0' && $balance < 0) {
                        include_once "../$scr";
                    } else {
                        $balanceup = updatedb("o_incoming_payments", "loan_balance = '$balance'", "uid = $max_pid");
                    }

                    ///////////////////////////////



                    $balanceup = updatedb("o_incoming_payments", "loan_balance = '$balance'", "uid = $max_pid");
                    echo "Update Balance: $balance, Payment Id:$max_pid, $ Result: $balanceup, Loan: $latest_loan_id ";
                    ///----Notify USER
                    if ($balance > 0) {
                        $pay_state = 'PARTIAL_PAYMENT';
                        product_notify($product_id, 0, $pay_state, 0, $latest_loan_id, $account_number);
                        if($allocated_collector > 0){
                            ///-----Send a notification to agent
                            $epay = encurl($max_pid);
                            notify('PAYMENTS',"$allocated_collector","Partial Payment","$client_name has made a partial payment of $TransAmount, Loan balance is $balance","incoming-payments?repayment=$epay");
                        }
                    } else {
                        $pay_state = 'FULL_PAYMENT';
                        if($allocated_collector > 0){
                            ///-----Send a notification to agent
                            $epay = encurl($max_pid);
                            notify('PAYMENTS',"$allocated_collector","Loan Cleared","$client_name has cleared his loan by paying $TransAmount","incoming-payments?repayment=$epay");
                        }
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