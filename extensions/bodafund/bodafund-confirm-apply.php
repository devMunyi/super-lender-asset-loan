<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);
session_start();
$_SESSION['db_name'] = 'bodafund_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

////------APPLY LOAN


$session_code = $data['session_id'];
$device_id = $data['device_id'];
$amount = $data['amount'];
$product_id = $data['product_id'];


if((input_length($device_id, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid device Id"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    die();
    exit();
}
if((input_length($session_code, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid Session Code"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    die();
    exit();
}
if($amount < 50){
    $result_ = 0;
    $details_ = '"Please enter a Valid amount"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    die();
    exit();
}
if($product_id < 1){
    $result_ = 0;
    $details_ = '"Product not selected. Please contact us"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    die();
    exit();
}



$session_d = fetchonerow('o_customer_sessions',"device_id='$device_id' AND session_code='$session_code' AND ending_date >= '$fulldate' AND status=1","uid, customer_id");
if($session_d['uid'] < 1){
    $result_ = 0;
    $details_ = '"Session Invalid"';
    $result_code = 107;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    die();
    exit();
}
else{
    $cust_det = fetchonerow('o_customers',"uid=".$session_d['customer_id']."","primary_product, loan_limit, status, primary_mobile, branch, total_loans");
    $primary_product = $cust_det['primary_product'];
    $loan_limit = $cust_det['loan_limit'];
    $status = $cust_det['status'];
    $cust_id = $session_d['customer_id'];
    $primary_mobile = $cust_det['primary_mobile'];
    $cust_branch = $cust_det['branch'];
    $total_loans = $cust_det['total_loans'];


    /////------Check if customer has an active loan
    $has_loan = checkrowexists('o_loans',"customer_id='".$session_d['customer_id']."' AND disbursed=0 AND paid=0 AND status!=0 AND status in (1,2,6)");
    if($has_loan == 1){
        $result_ = 0;
        $details_ = '"You have a pending loan, please wait while we review it."';
        $result_code = 107;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        store_event('o_customers', $session_d['customer_id'],"$result_, $details_, $result_code");
        die();
        exit();
    }
    $has_loan = checkrowexists('o_loans',"customer_id='".$session_d['customer_id']."' AND disbursed=1 AND paid=0 AND status!=0");
    if($has_loan == 1){
        $result_ = 0;
        $details_ = '"You have an existing loan. Please repay it to get a new one"';
        $result_code = 107;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        store_event('o_customers', $session_d['customer_id'],"$result_, $details_, $result_code");
        die();
        exit();
    }
    ////-----Check if customer account is active
    if($status != 1){
        $result_ = 0;
        $details_ = '"Your Account is inactive, please contact support"';
        $result_code = 107;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        store_event('o_customers', $session_d['customer_id'],"$result_, $details_, $result_code");
        die();
        exit();
    }
    if($amount > $loan_limit){
        $result_ = 0;
        $details_ = '"Your Allowed limit is '.$loan_limit.'"';
        $result_code = 107;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        store_event('o_customers', $session_d['customer_id'],"$result_, $details_, $result_code");
        die();
        exit();
    }
    $total_loans_taken = countotal_withlimit('o_loans',"customer_id = $cust_id AND disbursed = 1","uid","1000");
    if($total_loans_taken == 0) {
        /// --------------Check all upfront fees
        $upfronts = fetchtable('o_addons', "paid_upfront=1", "uid", "asc", "10", "uid, amount, amount_type, applicable_loan");
        $total_upfront = 0;
        while ($up = mysqli_fetch_array($upfronts)) {
            $aid = $up['uid'];
            $product_addon = fetchrow('o_product_addons', "addon_id='$aid' AND status=1 AND product_id='$product_id'", "uid");
            if ($product_addon > 0) {
                $upfront_addon = $up['uid'];
                $applicable_loan = $up['applicable_loan'];
                $amount_ = $up['amount'];
                $amount_type = $up['amount_type'];

                if ($amount_type == 'FIXED_VALUE') {
                    $a_amount = $amount_;
                } else {
                    $a_amount = $amount * ($amount / 100);
                }


                if ($applicable_loan == 0) {
                    $total_upfront += $a_amount;
                } else {
                    if ($total_loans_taken < $applicable_loan) {
                        $total_upfront += $a_amount;
                    }
                }
            }
        }
//die (errormes($total_loans_taken));
        $paid = totaltable('o_incoming_payments', "mobile_number='$primary_mobile' AND loan_id=0 AND payment_category in (0, 1, 2) AND status=1", "amount");
        $total_repaid = totaltable('o_loans', "disbursed=1 AND paid=1 AND status!=0 AND customer_id='$cust_id'", "total_repaid");
        $total_repayable = totaltable('o_loans', "disbursed=1 AND paid=1 AND status!=0 AND customer_id='$cust_id'", "total_repayable_amount");
        $overpayments = false_zero($total_repaid - $total_repayable);

        if (($paid + $overpayments) < $total_upfront) {
            $balance = $total_upfront - $paid;
            $result_ = 567;
            $details_ = '"Please pay a registration fee of  ' . $total_upfront . ' to continue"';
            $result_code = 107;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            store_event('o_customers', $session_d['customer_id'], "$result_, $details_, $result_code");
            die();
            exit();
        } else {
            $update_repayment = 1;
        }
    }
    /*
    ////---------Check savings
    $customer_id =  $session_d['customer_id'];
    $customer_join_date = fetchrow('o_customers',"uid=$customer_id","date(added_date)");
    $ago_ = datediff3($customer_join_date, $date);

    $total_weeks = countXInY(7, $ago_);

    $required_savings = 0;

    $total_savings = 10;

    $balance_savings = $required_savings - $total_savings;
    if($total_loans_taken < 1){
        $balance_savings = 0;
    }
    if($balance_savings > 0){
        $balance = $total_upfront - $paid;
        $result_ = 568;
        $details_ = '"You have not done weekly savings. Please save Ksh. '.$balance_savings.'"';
        $result_code = 167;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        store_event('o_customers', $session_d['customer_id'], "$result_, $details_, $result_code");
        die();
        exit();
    }

    */

    ////---Apply loan

    $result = give_loan($cust_id, $product_id, $amount, 'APP' );
    if($result > 1){
        ///-----Add LO and CO
        ///---Update group
        $due_date = dateadd($date, 0, 0, 14);
        if($amount >= 2500){
            $extend_due = ", final_due_date='$due_date', next_due_date='$due_date'";
        }
        else{
            $extend_due = ", next_due_date='$due_date'";
        }

        $gr = fetchrow('o_group_members',"customer_id='$customer_id' AND status=1","group_id");
        if($gr > 0){
            $upd = updatedb('o_loans',"group_id='$gr' $extend_due","uid='$result'");
        }
        $result_ = 1;
        $details_ = '"Your request has been submitted successfully. Please wait"';
        $result_code = 111;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
      //  die();

    }
    else {
        $result_ = 0;
        $details_ = '"'.$result.'"';
        $result_code = 107;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        store_event('o_customers', $session_d['customer_id'],"$result_, $details_, $result_code");
        die();
        exit();

    }
    ///----End of apply loan


    //////----Split payment to registration and savings

    if($update_repayment == 1) {

        $p = fetchonerow('o_incoming_payments', "payment_category in (0,1,2) AND amount >= 150 AND split_from=0 AND status=1 AND (customer_id = $cust_id OR mobile_number = $primary_mobile)", "uid, customer_id, amount, payment_method, mobile_number, transaction_code, payment_date, record_method");

        $uid = $p['uid'];
        if ($uid > 0) {
            $customer_id = $p['customer_id'];
            $amount = $p['amount'];
            $payment_method = $p['payment_method'];
            $mobile_number = $p['mobile_number'];
            $transaction_code = $p['transaction_code'];
            $payment_date = $p['payment_date'];
            $record_method = $p['record_method'];

            /*
            $saving = $amount - 50;

            ////-----------Save registration
            $rflds = array('customer_id', 'split_from', 'payment_method', 'payment_category', 'mobile_number', 'amount', 'transaction_code', 'payment_date', 'recorded_date', 'record_method', 'comments', 'status');
            $rvals = array("$cust_id", "$uid", "$payment_method", "2", "$mobile_number", "50", "R1-$transaction_code", "$payment_date", "$fulldate", "SYSTEM", "Registration split from payment by system","1");
            $save = addtodb('o_incoming_payments', $rflds, $rvals);
            ///------------Save saving

            $sflds = array('customer_id', 'split_from', 'payment_method', 'payment_category', 'mobile_number', 'amount', 'transaction_code', 'payment_date', 'recorded_date', 'record_method', 'comments', 'status');
            $svals = array("$cust_id", "$uid", "$payment_method", "4", "$mobile_number", "$saving", "S1-$transaction_code", "$payment_date", "$fulldate", "SYSTEM", "Saving split from payment by system","1");
            $save = addtodb('o_incoming_payments', $sflds, $svals);


            ///------------Mark original payment
            $upd = updatedb('o_incoming_payments', "status=2, comments='Payments split from this payment by system'", "uid='$uid'");
            */
        }

        ///--------This looks like a registration fee, split to registration and saving
        ///


        /// ------Split payment to registration and savings


        store_event('o_customers', $cust_id, "$result_, $details_, $result_code");

    }


}




