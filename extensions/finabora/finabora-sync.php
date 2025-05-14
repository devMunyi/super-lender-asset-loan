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


$query="SELECT * FROM s_users_primary WHERE uid > 0 ORDER BY  uid desc LIMIT $offset, $limit"; //echo "<tr><td>".$query."</td></tr>";
$result=mysqli_query($con1, $query);   //var_dump($query);
while($c = mysqli_fetch_array($result)){

    $uid = $c['uid'];
    $first_name = $c['first_name'];
    $second_name = $c['second_name'];
    $sir_name = $c['sir_name'];
    $full_name = "$first_name $second_name $sir_name";
    $primary_phone = $c['primary_phone'];
    $full_address = $c['full_address'];
    $national_id = $c['national_id'];
    $national_id = $c['national_id'];
    $gender = $c['gender'];  if($gender == 1){$g = "M";} else{ $g= 'F';}
    $dob = $c['dob'];
    $added_by = $c['added_by'];
    $added_date = $c['added_date'];
    $branch = $c['section'];
    $product = $c['product_id'];
    $limit = $c['loan_limit'];
    $state = $c['status'];  if($state == 2){$status=1;} else{$status=2;}
    echo "$uid $first_name $second_name [";


    $nq = "INSERT IGNORE INTO finabora_new_db.o_customers(uid, full_name, primary_mobile,  physical_address, passport_photo,  national_id, gender, dob, added_by, added_date, branch, primary_product, loan_limit, events, status) VALUES ($uid, '".addslashes($full_name)."', $primary_phone,  '".addslashes($full_address)."','',  '$national_id', '$g', '$dob', '$added_by', '$added_date', $branch, $product, '$limit','Customer Uploaded', $status)";

 //   echo $nq;

    if(!mysqli_query($con,$nq))
    {
        echo mysqli_error($con);  //var_dump($e);
        echo 0;
    }
    else
    {
       // logupdate($tb, $insertq);
        echo 1;
    }

echo "] <br/>";

}