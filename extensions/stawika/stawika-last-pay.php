<?php
session_start();

$_SESSION['db_name'] = 'stawika_db';
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");




$all_loans_array = array();
$loan_due_dates_array = array();
$loan_disbursed_date_array = array();
$customers_array = array();
$latest_loan_amount = array();
$loan_customer_array = array();
$unpaid_array = array();
$latest_loan_id = array();
$customer_phones = array();
////-----Get loans taken in particular period
$loans = fetchtable('o_loans',"disbursed=1 AND given_date >= '2022-01-01' AND last_pay_date is null","given_date","asc","0, 500000","customer_id, uid, account_number, given_date,  uid, final_due_date, loan_amount, status, disbursed, paid");
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

    $loan_disbursed_date_array[$loan_id] = $given_date;

    $customer_phones[$customer_id] = $account_number;

    array_push($customers_array, $customer_id);

    $latest_loan_id[$customer_id] = $loan_id;
    array_push($all_loans_array, $loan_id);


  //echo "Loan ID: $loan_id,"."Given Date: $given_date,  Due date $due_date, Status: $status [$account_number] , Customer_id: $customer_id".'<br/>';
    //array_push($all_loans_array, $loan_id);

}

$unique_loans = array();

foreach ($latest_loan_id as $cus => $loa){
    array_push($unique_loans, $loa);
}

$all_cust_list = implode(',', $customers_array);

$eligible_customers = table_to_array('o_customers',"status = 1 AND uid in ($all_cust_list) AND loan_limit = 0","1000000","uid");
$eligible_list = implode(',',$eligible_customers);

$payments_date = table_to_obj('o_incoming_payments',"status=1","1000000","loan_id","payment_date");




for($i=0; $i<=sizeof($all_loans_array); ++$i){
    $last_pay = $payments_date[$all_loans_array[$i]];
    $lid= $all_loans_array[$i];
    $disb = $loan_disbursed_date_array[$all_loans_array[$i]];

    if(strlen($last_pay)>4) {
        echo $all_loans_array[$i] . ' Disbursed:' . $disb . ' ,' . $last_pay . '<br/>';
        echo updatedb('o_loans', "last_pay_date='$last_pay'", "uid='$lid'");
    }
}














