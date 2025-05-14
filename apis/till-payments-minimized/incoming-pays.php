<?php
session_start();
include_once ("../");
$company = $_GET['c'];
$data  = file_get_contents('php://input');

/*
$logFile = 'log.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.'Company-'.$company."->".date('Y-m-d H:i:s')."\n");
fclose($log);
*/


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


if($TransAmount > 0) {

  
////////-------------------------Get the company Details
     

       
          include_once ("../configs/20200902.php");
           $_SESSION['db_name'] = $db_;
         include_once ("../configs/conn.inc");
         include_once ("../php_functions/functions.php");
///////-------------------------End of get company details
            ////----------Update Paybill Balance 9243937
            if ($OrgAccountBalance > 0) {
                if($mpesa_one_account == 1){ ////----For paybill that is also disbursement source code, we update both values
                    $oruid = " or uid=1";
                }
                else{
                    $oruid = "";
                }
                updatedb('o_summaries', "value_='$OrgAccountBalance', last_update='$fulldate'", "uid=2 $oruid");

                ////--------------Tie a B2B movement of Money incase it's enabled

                /// -------------End of tie B2B movement of money incase it's enabled

            }
            
                $customer_id = 0;
                $latest_loan_id = 0;
                $check_alternative = 0;
                $check_referee = 0;
                $account_number = $MSISDN;
                $current_co = 0;
                $current_agent = 0;

                $customer_det = fetchonerow('o_customers', "enc_phone='$MSISDN' AND status != 0", "uid, branch, primary_mobile, full_name, primary_product");
                 $customer_id = false_zero($customer_det['uid']);
                 $primary_mobile = $customer_det['primary_mobile'];
                 $product_id = $customer_det['primary_product'];
                 ////---------
                 if($customer_id > 0){ /////Customer exists
                     $branch_id = $customer_det['branch'];
                     $client_name = $customer_det['full_name'];
                      ///////----Check if has a Loan
                     $latest_loan = fetchmaxid('o_loans', "customer_id='$customer_id' AND disbursed=1 AND paid=0", "uid, product_id, account_number, current_co, current_agent");
                     $latest_loan_id = $latest_loan['uid'];
                     if($latest_loan_id > 1){
                          ////----You have loan details
                         $product_id = $latest_loan['product_id'];
                         $account_number = $latest_loan['account_number'];
                         $current_co = $latest_loan['current_co'];
                         $current_agent = $latest_loan['current_agent'];
                     }
                     else{
                         $check_alternative = 1;
                     }

                 }
                 else{
                     ///---Check alternative number
                       $check_alternative = 1;
                 }

               /////////************************** Alternative check
               if($check_alternative == 1){
                   $cust = fetchrow('o_customer_contacts',"enc_phone='$MSISDN' AND status = 1","customer_id");
                   if($cust > 0){
                       $customer_id = $cust;
                       $customer_det = fetchonerow('o_customers', "uid='$customer_id'", "uid, branch, full_name, primary_product");
                       $branch_id = $customer_det['branch'];
                       $client_name = $customer_det['full_name'];
                       $product_id = $customer_det['primary_product'];

                       $latest_loan = fetchmaxid('o_loans', "customer_id='$customer_id' AND disbursed=1 AND paid=0", "uid, product_id, account_number, current_co, current_agent");
                       $latest_loan_id = $latest_loan['uid'];
                       if($latest_loan_id > 1){
                           $product_id = $latest_loan['product_id'];
                           $account_number = $latest_loan['account_number'];
                           $current_co = $latest_loan['current_co'];
                           $current_agent = $latest_loan['current_agent'];

                           ////----You have loan details
                       }
                       else{
                           $check_referee = 1;
                       }
                   }
                   else{
                      $check_referee = 1;
                   }

               }

               /////******************************Referee check
               ///--------Pending check referee
    if($current_agent > 0){
        $allocated_collector = $current_agent;  /////An allocated agent rather than current CO. For notications purposes
        /// We don't want too many notifications for all LOs

    }
    else{
        $allocated_collector = 0;
        $current_agent = $current_co;
    }

            ////---------------End of new simplepay check



            $payment_method = 3;
            $fds = array('customer_id', 'branch_id', 'payment_method', 'mobile_number', 'amount', 'transaction_code', 'loan_id','loan_code', 'payment_date', 'recorded_date', 'record_method', 'added_by','collected_by', 'comments', 'status');
            $vals = array("$customer_id", "$branch_id", "$payment_method", "$primary_mobile", "$TransAmount", "$TransID", "".false_zero($latest_loan_id)."", "$BillRefNumber", "$TransTime", "$fulldate", "API", "0","$current_agent", "From API ($name)", "1");


            $save = addtodb('o_incoming_payments', $fds, $vals);
            if ($save == 1) {
                echo "Save Payment for Loan $latest_loan_id: $save";
                if ($latest_loan_id > 0) {
                    recalculate_loan($latest_loan_id);

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
                        product_notify($product_id, 0, $pay_state, 5, $latest_loan_id, $account_number);
                        if($allocated_collector > 0){
                            ///-----Send a notification to agent
                            $epay = encurl($max_pid);
                            notify('Payments',"$allocated_collector","Loan Cleared","$client_name has cleared his loan by paying $TransAmount","incoming-payments?repayment=$epay");
                        }
                    }


                }
            } else {
                echo "Save Payment for Loan $latest_loan_id:$save";
            }       
    
}
else{
    echo "Amount invalid";
}