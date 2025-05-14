<?php
session_start();
include_once ('../configs/20200902.php');
include_once("../php_functions/functions.php");
$_SESSION['db_name'] = $db_;
include_once("../configs/conn.inc");

////-----Fetch all staff
$staff = fetchtable('o_users',"uid > 0 AND status=1","uid","asc","100000","uid, name, email, phone, national_id, branch, join_date, user_group");
while($s = mysqli_fetch_array($staff)){

    $uid = $s['uid'];
    $name = $s['name'];
    $email = $s['email'];
    $phone = $s['phone'];
    $national_id = $s['national_id'];
    $branch = $s['branch'];
    $join_date = $s['join_date'];
    $user_group = $s['user_group'];
    $enc_phone = hash('sha256', $phone);

    echo $phone.','.$user_group.'<br/>';
    $loan_limit = 10000;

    if($user_group == 1 || $user_group == 2 || $user_group == 6){
        $loan_limit = 25000;
    }
    if($user_group == 23){
        $loan_limit = 20000;
    }
    elseif ($user_group == 5 || $user_group == 13){
        $loan_limit = 15000;
    }
    elseif ($user_group == 7 || $user_group == 8){
        $loan_limit = 10000;
    }

   echo "$loan_limit";



    $fds = array('full_name', 'primary_mobile', 'enc_phone', 'phone_number_provider', 'email_address', 'physical_address', 'geolocation', 'town', 'national_id', 'gender', 'dob', 'added_by', 'current_agent', 'added_date', 'branch', 'primary_product', 'loan_limit', 'status');
    $vals = array("$name", "$phone", "$enc_phone", "1", "$email", "Current Staff Member", "", "1", "$national_id", "", "1900-01-01", "0", "0", "$fulldate", "$branch", "4", "$loan_limit", "1");
    $create = addtodb('o_customers', $fds, $vals);
    if ($create == 1) {
        echo $create;
        $events = "Customer created by a cron service from the staff table";
        echo sucmes('Customer Saved Successfully');
        $customer_id = encurl(fetchrow('o_customers', "primary_mobile='$phone'", "uid"));
        $proceed = 1;
        $cust_id = decurl($customer_id);
        store_event('o_customers', $cust_id, "$events");

    }
    else{
        echo "Error".$create;
    }




}



include_once("../configs/close_connection.inc");