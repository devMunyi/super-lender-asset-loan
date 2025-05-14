<?php
session_start();
$_SESSION['db_name'] = 'finabora_new_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

$offset = $_GET['offset'];
$limit = $_GET['limit'];

if($offset > -1 AND $limit > 0){

}
else{
    die("Offset and Limit required");
}

/////----------------External connection

$hostname1 = '159.65.231.232'; // Your MySQL hostname. Usualy named as 'localhost', so you're NOT necessary to change this even this script has already online on the internet.
$dbname1  = 'fina_db'; // Your database name.
$username1 = 'kaguius';             // Your database username.
$password1 = 'U6xZfLn9A7SwcP9%';

$con1=mysqli_connect($hostname1,$username1,$password1,$dbname1);
if(mysqli_connect_errno())
{
    printf('Error Establishing a database connection');
    exit();
}


$query="SELECT *, DATE(date_received) as pay_date FROM s_incoming_payments WHERE uid > 0 ORDER BY  uid desc LIMIT $offset, $limit"; //echo "<tr><td>".$query."</td></tr>";
$result=mysqli_query($con1, $query);   //var_dump($query);
while($data = mysqli_fetch_array($result)){
    $uid = $data['uid'];
    $pay_date = $data['pay_date'];
    $phone = $data['phone'];
   // $loan_id = $data['loan_id'];
    $transcode = $data['receipt_no'];
    $amount = $data['amount'];
    $payer_account = $data['payer_account'];
    $date_received = $data['date_received'];
    $d = datefromdatetime2($pay_date);

   // $customer_id = $data['customer_id'];
    $status = $data['status'];

   /*
    /////////-----------------------BIO
    ///
    $customer_id = 0;
    $loan_id = 0 ;
    $cust = "SELECT uid from o_customers WHERE (primary_mobile = '".$payer_account."' OR national_id = '$payer_account') ";
    $cust_result = mysqli_query($con, $query);
    while($cu = mysqli_fetch_array($cust_result)){
        $customer_id = $cu['uid'];
        if($customer_id > 0){
            $latest_loan = "SELECT uid from o_loans WHERE customer_id='$customer_id' AND disbursed=1 AND paid=0 ORDER BY uid desc LIMIT 1";
            $ll = mysqli_query($con, $query);
            while($l = mysqli_fetch_array($ll)){
                $loan_id = $l['uid'];
            }
        }
    }
    ///
    $q = "INSERT IGNORE INTO finabora_new_db.o_incoming_payments(customer_id, branch_id, payment_method, mobile_number, amount, transaction_code, loan_id, loan_code,payment_date, recorded_date, added_by, record_method, status) VALUES ('$customer_id', 1,'2','$phone', '$amount', '$transcode',  '$loan_id', '$payer_account','$d','$date_received',2,'MANUAL', $status)";

    $insertq = "$q";
   // echo $insertq.'<br/>';


    if(!mysqli_query($con,$insertq))
    {
        echo mysqli_error($con).'<br/>';
    }
    else
    {
        echo $uid.'Success! <br/>';
        if($loan_id > 0){
            recalculate_loan($loan_id, true);
        }
    }
   */


}