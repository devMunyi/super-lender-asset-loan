<?php
session_start();
$_SESSION['db_name'] = 'stawika_db';
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


$query="SELECT * FROM s_loans WHERE uid > 0 ORDER BY  uid desc LIMIT $offset, $limit"; //echo "<tr><td>".$query."</td></tr>";
$result=mysqli_query($con1, $query);   //var_dump($query);
while($l = mysqli_fetch_array($result)){
    $uid = $l['uid'];
    $customer_id = $l['customer_id'];
    $customer_phone = $l['customer_phone'];
    $given_date = $l['given_date'];
    $due_date = $l['due_date'];
    $loan_amount = $l['loan_amount'];
    $repayable = $l['loan_total'];
    $product = $l['loan_product'];
    $created_time = $l['time_created'];
    $response_code = $l['response_code'];

    $status = $l['status'];
    $branch = 2;

    if($status == 2){
        $state = 3;
    }
    elseif($status == 3){
        $state = 10;
    }
    elseif($status == 4){
        $state = 5;
    }
    elseif($status == 5){
        $state = 7;
    }
    elseif($status == 6){
        $state = 9;
    }
    elseif($status == 7){
        $state = 0;
    }
    elseif($status == 8){
        $state = 6;
    }
    elseif($status == 9){
        $state = 1;
    }
    elseif($status == 10){
        $state = 6;
    }

    $q = "INSERT IGNORE INTO finabora_db.o_loans(uid, customer_id, account_number, product_id, loan_amount, disbursed_amount, total_repayable_amount,total_repaid, loan_balance,period, period_units, total_addons, total_instalments, current_instalment, given_date, next_due_date, final_due_date, added_by, added_date, loan_stage, application_mode,disbursed, paid, status ) VALUES ('$uid', $customer_id,'$customer_phone',$product, '$loan_amount', '$loan_amount', '$repayable','0', '$repayable',30, 1, 0, 1, 1, '$given_date', '$due_date', '$due_date', 1, '$created_time', 2, 1, 1, 0, $state)";

    $insertq = "$q";
    // echo $insertq.'<br/>';

    if(!mysqli_query($con,$insertq))
    {
        echo mysqli_error($con).'<br/>';
    }
    else
    {
        echo $uid.'Success! <br/>';
    }


}



/////----------Loan Interests
