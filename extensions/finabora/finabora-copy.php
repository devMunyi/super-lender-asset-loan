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

$relation_array = array();
$relates = "SELECT * FROM o_referee_relationships WHERE uid > 0 ORDER BY uid asc LIMIT 100";
$resu=mysqli_query($con1, $relates);
while($r = mysqli_fetch_array($resu)){
    $rid = $r['uid'];
    $name_ = $r['name'];
    $relation_array[$rid] = $name_;
}


$customers_array = array();

$query="SELECT * FROM o_guarantors WHERE uid > 0  ORDER BY  uid asc LIMIT $offset, $limit"; //echo "<tr><td>".$query."</td></tr>";
$result=mysqli_query($con1, $query);   //var_dump($query);
while($g = mysqli_fetch_array($result)){

    $uid = $g['uid'];
    $customer_id = $g['customer_id'];
    $surname = $g['surname'];
    $othernames = $g['othernames'];
    $full_name = "$surname $othernames";
    $known_as = $g['known_as'];
    $relationship = $g['relationship'];    $relationship_name = $relation_array[$relationship];
    $postal_address = $g['postal_address'];
    $mobile_number = $g['mobile_number'];
    $alternative_number = $g['alternative_number'];
    $resident_type = $g['resident_type'];
    $business_type = $g['business_type'];
    $home_address = $g['home_address'];
    $business_address = $g['business_address'];
    $passport = $g['passport'];
    $id_front = $g['idfront'];
    $id_back = $g['idback'];
    $national_id = $g['national_id'];
    $mobile_no = $g['mobile_no'];
    $amount_guaranteed = $g['amount_guaranteed'];
    $added_date = $g['added_date'];
    $status = $g['status'];


   $sec1 = ('"5": "'.$full_name.'", "6": "'.$national_id.'", "7": "'.$relationship_name.'", "8": "'.$postal_address.'", "9": "'.$mobile_number.'", "10": "'.$alternative_number.'", "11": "'.$resident_type.'", "12": "'.$business_type.'", "13": "'.$home_address.'", "14": "'.$business_address.'"');

   $sec2 = ('"15": "'.$full_name.'", "16": "'.$national_id.'", "17": "'.$relationship_name.'", "18": "'.$postal_address.'", "19": "'.$mobile_number.'", "20": "'.$alternative_number.'", "21": "'.$resident_type.'", "22": "'.$business_type.'", "23": "'.$home_address.'", "24": "'.$business_address.'"');

    $first_value = $customers_array[$customer_id];
  if((input_length($first_value, 5)) == 1){
      ////----First one exists
      $customers_array[$customer_id] = $first_value.','.$sec2;
  }
  else{
      ///----No exists
      $customers_array[$customer_id] = $sec1;
  }



 /* $curr = fetchrow('o_customers',"uid='$customer_id'","sec_data");
  $sec_obj = json_decode($curr, true);
  $g1 = $sec_obj[5];
  $g2 = $sec_obj[15];

  //echo "$customer_id,".$curr.'<br/>';

  if((input_length($g1, 2)) == 1){
       //////-------1 Exists
      // echo "1 Exists <br/>";
   if((input_length($g2, 2)) == 1)
      {
          /////-----2 Exists
        //  echo "2 Exists <br/>";
      }
   else{
          /////-----2 Not Exist
      // echo "Update o_customers SET sec_data='$sec2' WHERE uid='$customer_id'; <br/>";
       }
  }
  else{
      //////-----1 Does not exist
      //  echo "Update o_customers SET sec_data='$sec1' WHERE uid='$customer_id'; <br/>";
    }*/

  //  echo "Update o_customers SET sec_data='$sec1' WHERE uid='$customer_id'; <br/>";


}

foreach ($customers_array as $key => $value)
{
   // $res = "{ $value } <br/> <br/> <br/>";
    echo "Update o_customers SET sec_data= '{ ".addslashes($value)." }' WHERE uid='$key'; <br/>";
}