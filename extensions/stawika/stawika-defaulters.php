<?php
session_start();

$_SESSION['db_name'] = 'stawika_db';
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");




$all_loans_array = array();
$loan_due_dates_array = array();
$customers_array = array();
$latest_loan_amount = array();
$latest_loan_due_date = array();
$loan_customer_array = array();
$unpaid_array = array();
$latest_loan_id = array();
$customer_phones = array();
////-----Get loans taken in particular period
$loans = fetchtable('o_loans',"disbursed=1 AND given_date >= '2021-01-01' AND status!=0","given_date","asc","0, 500000","customer_id, uid, account_number, given_date,  uid, final_due_date, loan_amount, status, disbursed, paid");
while($l = mysqli_fetch_array($loans)){
    $loan_id = $l['uid'];
    $customer_id = $l['customer_id'];
    $due_date = $l['final_due_date'];
    $given_date = $l['given_date'];
    $loan_amount = $l['loan_amount'];
    $account_number = $l['account_number'];
    $status = $l['status'];
    $disbursed = $l['disbursed'];
    $paid = $l['paid'];

    $customer_phones[$customer_id] = $account_number;
    $latest_loan_due_date[$customer_id] = $due_date;
    $latest_loan_amount[$customer_id] = $loan_amount;
    $latest_loan_id[$customer_id] = $loan_id;
    array_push($all_loans_array, $loan_id);
    array_push($customers_array, $customer_id);


}
$overdues = table_to_array('o_loans',"disbursed=1 AND paid=0","1000000","customer_id");
$customers_list = implode(',', $customers_array);
$loan_list = implode(',', $all_loans_array);

//////----------------------------Payments
$cleared_date_array = array();
$pays = fetchtable('o_incoming_payments',"loan_id in ($loan_list) AND status=1","uid","asc","1000000","payment_date, loan_id");
while($p = mysqli_fetch_array($pays)){
    $payment_date = $p['payment_date'];
    $loan_idd = $p['loan_id'];
    $cleared_date_array[$loan_idd] = $payment_date;
}



/////////////////////---

echo "<table>";
echo "<tr><td>Customer_Name,</td><td>Phone_Number,</td><td>Last_Loan_ID,</td><td>Last_Loan_Due_Date,</td><td>Last_Loan_Amount,</td><td>Last_Pay_Date,</td><td>Days_Late</td></tr>";
/////---------------Customers with no limits but have borrowed before
$customers = fetchtable('o_customers',"loan_limit = 0 AND uid in ($customers_list)","uid","asc","1000000","uid, full_name, primary_mobile, national_id,loan_limit");
while($c = mysqli_fetch_array($customers)){
    $uid = $c['uid'];
    $full_name = $c['full_name'];
    $primary_mobile = $c['primary_mobile'];
    $national_id = $c['national_id'];
    $loan_limit = $c['loan_limit'];

    $last_due = $latest_loan_due_date[$uid];
    $last_amount = $latest_loan_amount[$uid];



        if((in_array($uid, $overdues))){ ///////Not in overdues

            /////-------Have an overdue loan
        }
        else {
            $latest_loan_id_ = $latest_loan_id[$uid];
            $cleared_date = $cleared_date_array[$latest_loan_id_];

                $late_days = datediff($last_due,$cleared_date);
                if($late_days < 361) {
                  //  $limit_change = update_limit($uid, $last_amount, "Limit updated by system manual process 16-06-2022");
                  echo "<tr><td>$full_name,</td><td>$primary_mobile,</td><td>$latest_loan_id_,</td><td>$last_due,</td><td>$last_amount,</td><td>$cleared_date,</td><td>$late_days </td></tr>";
                }
           // echo "<tr><td>$full_name</td><td>$primary_mobile</td><td>$latest_loan_id_</td><td>$last_due</td><td>$last_amount</td><td>$cleared_date</td><td>$late_days</td></tr>";
        }


}
echo "</table>";














