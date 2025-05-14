<?php
session_start();

$_SESSION['db_name'] = 'stawika_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");




$all_loans_array = array();
$loan_due_dates_array = array();
$customers_array = array();
$latest_loan_amount = array();
$loan_customer_array = array();
$unpaid_array = array();
$latest_loan_id = array();
$customer_phones = array();
$customer_limits = array();
$latest_loan_status = array_push();
////-----Get loans taken in particular period
$customers = fetchtable('o_customers',"loan_limit>=500","uid","asc","100000","uid, full_name, loan_limit, primary_mobile");
while($c = mysqli_fetch_array($customers)){
    $uid = $c['uid'];
    $full_name = $c['full_name'];
    $loan_limit = $c['loan_limit'];
    $primary_mobile = $c['primary_mobile'];
    array_push($customers_array, $uid);
    $customer_phones[$uid] = $primary_mobile;
    $customer_limits[$uid] = $loan_limit;

   // echo "Customer: $uid $full_name $loan_limit $primary_mobile <br/>";
}

$customers_list = implode(',', $customers_array);

$limits_ = table_to_array('o_customer_limits',"given_date >= '2022-09-01 10:04:25'",100000, "customer_uid");
//var_dump($limits_);

///-------------------Customers with active loans
$customers_with_loans_array = array();
$loans = fetchtable('o_loans',"customer_id in ($customers_list) AND disbursed=1 AND paid=0 AND status!=0 AND final_due_date >= '2022-08-25' AND final_due_date <= '2022-09-05'","uid","desc","10000","uid, customer_id");
while($l = mysqli_fetch_array($loans)){
     $customer_id = $l['customer_id'];
     array_push($customers_with_loans_array, $customer_id);
}


for($i = 0; $i <= sizeof($customers_array); ++$i){
    if(in_array($customers_array[$i], $customers_with_loans_array)){
      // echo "Has Loan". $customers_array[$i]."<br/>";
      $phone = $customer_phones[$customers_array[$i]];
      $limit = $customer_limits[$customers_array[$i]];

      if($limit <= 10000){
         $new_limit = $limit + 1000;
      }
      if($limit > 10000 && $limit <= 20000){
          $new_limit = $limit + 2000;
      }
      if($limit > 20000){
          $new_limit = $limit + 3000;
      }
   //  echo update_limit($customers_array[$i], $new_limit, "Added Manually in Bulk");

      echo "$phone, $limit , $new_limit <br/>";
    }
    else{
       
      


    }
}