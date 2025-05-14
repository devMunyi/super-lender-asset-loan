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


$query="SELECT uid, loan_interest, late_interest, loan_product  FROM s_loans WHERE uid > 0  ORDER BY  uid asc LIMIT $offset, $limit"; //echo "<tr><td>".$query."</td></tr>";
$result=mysqli_query($con1, $query);   //var_dump($query);
while($l = mysqli_fetch_array($result)){
    $uid = $l['uid'];
    $loan_interest = $l['loan_interest'];
    $late_interest = $l['late_interest'];
    $loan_product = $l['loan_product'];

    //echo "$uid, $loan_interest, $late_interest, $loan_product<br/>";
    if($loan_product == 11){
        $addon_id = 1;
        $addon2 = 3;
    }
    if($loan_product == 12){
        $addon_id = 4;
        $addon2 = 5;
    }
    elseif ($loan_product == 13)
    {
        $addon_id = 4;
        $addon2 = 5;
    }
    else{
        $addon_id = 1;
        $addon2 = 3;
    }

    $fds = array('loan_id','addon_id','addon_amount','added_by','added_date','status');
    $vals = array("$uid","$addon_id","$loan_interest","1","$fulldate","1");
    $create = addtodb('o_loan_addons',$fds, $vals);
    echo "Interest Create $uid, result: $create <br/>";
    if($late_interest > 0){
        $fds1 = array('loan_id','addon_id','addon_amount','added_by','added_date','status');
        $vals1 = array("$uid","$addon2","$late_interest","1","$fulldate","1");
        $create1 = addtodb('o_loan_addons',$fds1, $vals1);
        echo "Penalty Create $uid, result: $create1 <br/>";
    }


  //  $addex = fetchrow('o_loan_addons',"loan_id='$uid' AND addon_id='' AND status=1","uid");

}



/////----------Loan Interests
