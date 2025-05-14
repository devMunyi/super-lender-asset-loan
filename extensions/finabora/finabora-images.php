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

$query="SELECT * FROM s_users_primary WHERE uid > 0  ORDER BY  uid asc LIMIT $offset, $limit"; //echo "<tr><td>".$query."</td></tr>";
$result=mysqli_query($con1, $query);   //var_dump($query);
while($g = mysqli_fetch_array($result)) {

    $uid = $g['uid'];
   $passport = $g['passport'];
   $idfront = $g['idfront'];
   $idback = $g['idback'];


   if((input_length($passport, 5)) == 1){
       ///---Upload passport
      echo "INSERT into o_documents (code_name, title, description, category, added_by, added_date, tbl, rec, stored_address, status) VALUES ('','passport','uploaded automatically',1,1,'$fulldate','o_customers',$uid, '$passport', 1); <br/>";
   }
    if((input_length($idfront, 5)) == 1){
        ///---Upload ID front
        echo "INSERT into o_documents (code_name, title, description, category, added_by, added_date, tbl, rec, stored_address, status) VALUES ('','ID Front','uploaded automatically',2,1,'$fulldate','o_customers',$uid, '$idfront', 1) ; <br/>";
    }
    if((input_length($idback, 5)) == 1){
        ///---Upload id back
        echo "INSERT into o_documents (code_name, title, description, category, added_by, added_date, tbl, rec, stored_address, status) VALUES ('','ID Back','uploaded automatically',3,1,'$fulldate','o_customers',$uid, '$idback', 1) ; <br/>";
    }


}