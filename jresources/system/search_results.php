<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$term = $_POST['search_term'];
$userd = session_details();

if(input_length($term, 1) == 0){
    echo errormes("Please enter at least 3 characters");
    die();
    }
$orcustomerphone = $orcustomernationalid = "";
///----Check if it may be a phone
if(input_length($term, 10) == 1){
    $valid_phone = make_phone_valid($term);
    if((validate_phone($valid_phone)) == 1){
        ///----It's a phone
        $orcustomerphone = " OR primary_mobile = '$valid_phone' ";
    }
    else{
        ///-----Not a phone
        $orcustomerphone = "";
    }
}
if(input_between(7,8, $term) == 1){
    ////----Probably a national id
    $orcustomernationalid = " OR national_id = '$term'";
}
else{
    $orcustomernationalid = "";
}

$branchCondition = getBranchCondition($userd, 'o_customers');
$branchUserCondition = $branchCondition['branchUserCondition'];
$branchLoanCondition = $branchCondition['branchLoanCondition'];




////----Search customers
//echo "<li>uid > 0 $branchUserCondition AND (uid='$term' $orcustomerphone $orcustomernationalid)</li>";
$customer = fetchonerow('o_customers',"uid > 0 $branchUserCondition AND (uid='$term' $orcustomerphone $orcustomernationalid)","uid, full_name");
///---- Search loan codes
$loan = fetchonerow('o_loans',"uid = '$term' $branchLoanCondition","uid, loan_amount, given_date");
///----- Search payments codes
$payment = fetchonerow('o_incoming_payments',"transaction_code = '$term' $branchUserCondition","uid, amount, payment_date");
///------Search staff
///
$staff = fetchonerow('o_users',"uid> 0 $branchLoanCondition AND (uid = '$term' OR email = '$term' OR phone = '$valid_phone' OR national_id='$term')","uid, name, email");

if($customer['uid'] > 0){
   // echo "<li>1 Customer Found <a href='customers?'>View</a> </li>";
    $cid = encurl($customer['uid']);
    echo "<li><a href=\"customers?customer=$cid\">  <span class=\"font-14 font-bold\">1 customer found</span>  <br> &rArr; ".$customer['full_name']."  </a></li>";
}
else{
    $nocustomer = 1;
}
if($loan['uid'] > 0){
    $lid = encurl($loan['uid']);
    echo "<li><a href=\"loans?loan=$lid\">  <span class=\"font-14 font-bold\">1 loan found</span>  <br> &rArr; ".$loan['loan_amount'].", ".fancydate($loan['given_date'])." </a></li>";
}
else{
    $noloan = 1;
}
if($payment['uid'] > 0){
    $pid = encurl($payment['uid']);
    echo "<li><a href=\"incoming-payments?repayment=$pid\">  <span class=\"font-14 font-bold\">1 payment found</span>  <br> &rArr; ".$payment['amount'].", ".fancydate($payment['payment_date'])." </a></li>";
}
else{
    $nopayment = 1;
}
if($staff['uid'] > 0){
    $sid = encurl($staff['uid']);
    echo "<li><a href=\"staff?staff=$sid\">  <span class=\"font-14 font-bold\">1 staff found</span>  <br> &rArr;  ".$staff['name'].", ".$staff['email']."  </a></li>";
}
else{
    $nostaff = 1;
}

$notfound = $nocustomer + $noloan + $nopayment + $nostaff;
if($notfound == 4){
    echo "<li class='header'><i>No records found, Search by phone or UID</i></li>";
}

include_once("../configs/close_connection.inc");