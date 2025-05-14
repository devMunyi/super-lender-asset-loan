<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}

$permi = permission($userd['uid'],'o_incoming_payments',"0","update_");
if($permi != 1){
    exit(errormes("You don't have permission to update payment"));
}

$pid = decurl($_POST['pid']);
$payment_method = $_POST['payment_method'];
$mobile_number = make_phone_valid($_POST['mobile_number']);
$payment_for = $_POST['payment_for'];
$amount = trim($_POST['amount']);
$transaction_code = trim($_POST['transaction_code']);
$loan_id = ($_POST['loan_id']);
$payment_date = $_POST['payment_date'];
$record_method = $_POST['record_method'] ?? "MANUAL";
$comments = sanitizeAndEscape($_POST['comments'], $con);
$status = $_POST['status'];
$group_id = $_POST['group_id'];
$branch_id = 0;
$collector = 0;


////////////////////////
if($pid > 0){}
else{
    exit(errormes("Payment ID Invalid"));
}

if($payment_method == 4){
    /*if(input_length($transaction_code,3) == 0){
        
    }*/
    $transaction_code = "N/A";
}else{
    if (input_length($transaction_code, 3) == 1) {
        $exists = checkrowexists('o_incoming_payments', "transaction_code=\"$transaction_code\" AND uid != $pid");
       
        if ($exists == 1) {
            exit(errormes("Transaction code exists"));
        }
    }else {
        //////------Invalid user ID
        exit(errormes("Please enter transaction code"));
    }
}



if($amount > 0){}
else{
    exit(errormes("Amount is required"));
}


///----Customer ID from mobile number
$customer_det = fetchonerow('o_customers',"primary_mobile='$mobile_number'","uid, primary_product");

$customer_id = $customer_det["uid"] ?? 0;
$primary_product = $customer_det["primary_product"] ?? 1;


if($loan_id > 0) {
    $collector = intval((fetchrow('o_loans',"uid = '$loan_id'","current_agent")));
    $exists = checkrowexists('o_loans', "uid = $loan_id AND status != 0");
    if ($exists == 0) {
      //  exit(errormes("The loan code doesn't exist"));
      //  exit();
    }
    else{
        $customer_id = fetchrow('o_loans',"uid= $loan_id","customer_id");
        $branch_id = fetchrow("o_customers", "uid=$customer_id", "branch");
    }
}
else{
  //  exit(errormes("Please enter loan code"));
   // exit();
}

if((input_length($payment_date, 10)) == 0)
{
    exit(errormes("Payment date required"));
}

if($payment_method == 0){
    exit(errormes("Payment method required"));
}

$original_pay = fetchonerow("o_incoming_payments", "uid = $pid", "loan_id, transaction_code, amount, mobile_number, payment_method, payment_category, payment_date, comments, status");
$original_loan = intval($original_pay['loan_id'] ?? 0);
$original_amount = doubleval($original_pay['amount'] ?? 0);
$original_mobile = $original_pay['mobile_number'] ?? 0;
$original_transaction = $original_pay['transaction_code'] ?? 0;
$original_payment_method = intval($original_pay['payment_method'] ?? 0);
$original_payment_category = intval($original_pay['payment_category'] ?? 0);
$original_payment_date = $original_pay['payment_date'] ?? 0;
$original_comments = $original_pay['comments'] ?? 0;
$original_status = intval($original_pay['status'] ?? 0);

$update_flds = "customer_id= '$customer_id', amount = $amount, branch_id = '$branch_id', collected_by='$collector', group_id='$group_id', payment_method= $payment_method, payment_category='$payment_for', mobile_number=\"$mobile_number\", loan_id = '$loan_id', payment_date=\"$payment_date\",  comments=\"$comments\", status=\"$status\"";
$update = updatedb('o_incoming_payments',$update_flds,"uid = $pid");
if ($update == 1) {
    $event = "Payment updated by [".$userd['name']."(".$userd['email'].")]. Details -> ";
    $orginal_event = $event;
    if($original_loan != $loan_id){
        $event .= "Loan ID: $original_loan to $loan_id, ";
    }
    if ($original_transaction != $transaction_code) {
        $event .= "Transaction code: $original_transaction to $transaction_code, ";
    }
    if ($original_amount != $amount) {
        $event .= "Amount: $original_amount to $amount, ";
    }
    if ($original_mobile != $mobile_number) {
        $event .= "Mobile number: $original_mobile to $mobile_number, ";
    }
    if ($original_payment_method != $payment_method) {
        $payment_methods = table_to_obj("o_payment_methods", "uid IN ($original_payment_method, $payment_method)", "100", "uid", "name");

        $event .= "Payment method: {$payment_methods[$original_payment_method]} to {$payment_methods[$payment_method]}, ";
    }
    
    if ($original_payment_category != $payment_for) {
        $payment_catgories = table_to_obj("o_payment_categories", "uid IN ($original_payment_category, $payment_for)", "100", "uid", "name");
        
        $event .= "Payment category: {$payment_catgories[$original_payment_category]} to {$payment_catgories[$payment_for]}, ";
    }

    if ($original_payment_date != $payment_date) {
        $event .= "Payment date: $original_payment_date to $payment_date, ";
    }
    if ($original_comments != $comments) {
        $event .= "Comments: $original_comments to $comments, ";
    }

    if ($original_status != $status) {
        $status_names = table_to_obj("o_payment_statuses", "uid IN ($original_status, $status)", "100", "uid", "name");
        $event .= "Status: {$status_names[$original_status]} to {$status_names[$status]}";
    }

    if($orginal_event == $event){
        $event = "Payment update triggered by [".$userd['name']."(".$userd['email'].")]. No changes captured";
    }

    // remove possible trailing comma from event replace with fullstop
    $event = rtrim(trim($event), ',');

   
    echo sucmes('Payment Updated Successfully');
    if($loan_id > 0) {
        store_event('o_incoming_payments', $pid,"$event");
        recalculate_loan($loan_id);

        // update newly allocated loan
        $ld = fetchmaxid("o_incoming_payments", "status > 0 AND loan_id = $loan_id", "uid, added_by");
        $max_pid = $ld["uid"];

        $balance = loan_balance($loan_id);

        /////-------Check the after save script
        $primary_product = $primary_product ? $primary_product : 1;
        $scr = after_script($primary_product, "SPLIT_PAYMENT");

        // optionally handle payment splitting
        if ($scr !== 0) {
            $added_by = $ld["added_by"] ?? 0;
            include_once("../../$scr");
        }else {
            updatedb("o_incoming_payments", "loan_balance = $balance", "uid = $max_pid");
        }
        ////-------End of check after save script

        // updatedb("o_loans", "loan_balance = $balance", "uid = $loan_id");
        recalculate_loan($loan_id, true);
    }else{
        // store event as hidden
        store_event('o_incoming_payments', $pid,"$event", 0);
    }

    if($original_loan > 0){
        $loan_details = fetchonerow('o_loans',"uid='$original_loan'","final_due_date");
        $final_due_date = $loan_details['final_due_date'];
        $per = datecompare($final_due_date, $date);

        if($per == 1){

            ///--Due /Not due
            $upd = updatedb('o_loans',"paid=0, status=3","uid='$original_loan' AND status!=3 AND disbursed=1");
            if($upd == 1){
                $event = "Loan marked as disbursed because a payment was unallocated by [".$userd['name']."(".$userd['email'].")]. The payment ID is ($pid)";
                store_event('o_loans', $original_loan,"$event");
            }

        }
        else{
            ///Overdue
            $upd = updatedb('o_loans',"paid=0, status=7","uid='$original_loan' AND status!=7 AND disbursed=1");
            if($upd == 1){
                $event = "Loan marked as overdue  because a payment was unallocated. The payment ID is ($pid)";
                store_event('o_loans', $original_loan,"$event");
            }
        }

        recalculate_loan($original_loan, true);

        ///----Check if original loan is same as current loan and send a payment received message
        if($original_loan != $loan_id){
            //----New loan allocation check for reminder settings
            ///----Notify USER
            $loan = fetchonerow('o_loans',"uid='$loan_id'","loan_balance, final_due_date, product_id, account_number");
            $balance = $loan['loan_balance'];
            $product_id = $loan['product_id'];
            $account_number = $loan['account_number'];

            if ($balance > 0) {
                $pay_state = 'PARTIAL_PAYMENT';
                product_notify($product_id, 0, $pay_state, 0, $loan_id, $account_number);
            } else {
                $pay_state = 'FULL_PAYMENT';
                product_notify($product_id, 0, $pay_state, 5, $loan_id, $account_number);
            }
            ///-----End of reminder settings
        }

    }
    $proceed = 1;
} else {
    echo errormes('Error Updating Payment'.$update);
}
?>

<script>
    if('<?php echo $proceed; ?>'){
        setTimeout(function () {
            gotourl("incoming-payments?repayment=<?php echo encurl($pid); ?>")
        },1500);
    }
</script>
