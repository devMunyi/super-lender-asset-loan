<?php 

$qs = $_GET['qs'] ?? "";

if($qs != "slSys"){
    exit("Invalid Request");
}

include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$phones_without_hash_query = "SELECT uid, primary_mobile, enc_phone, added_date, status FROM o_customers where enc_phone = '' or enc_phone is null order by uid DESC LIMIT 1000";

$phones_without_hash_result = mysqli_query($con, $phones_without_hash_query);

$updated = 0;
$skipped = 0;
while($phone = mysqli_fetch_assoc($phones_without_hash_result)){
    $uid = $phone['uid'];
    $primary_mobile = $phone['primary_mobile'];
    $enc_phone = hash('sha256', $primary_mobile);
    $update_query = "UPDATE o_customers SET enc_phone = '$enc_phone' WHERE uid = $uid";
    $update_result = mysqli_query($con, $update_query);
    if($update_result){
        $updated++;
        echo "Phone $primary_mobile hashed successfully<br/>";
    }else{
        $skipped++;
        echo "Error hashing phone $primary_mobile<br/>";
    }
}

echo "Updated $updated phones and skipped $skipped phones from o_customers <br/>";


$phones_without_hash_query2 = "SELECT uid, customer_id, value FROM o_customer_contacts where (enc_phone = '' OR enc_phone is null) AND contact_type IN (1, 2) order by uid DESC LIMIT 10000";

$phones_without_hash_result2 = mysqli_query($con, $phones_without_hash_query2);
$updated2 = 0;
$skipped2 = 0;
while($phone = mysqli_fetch_assoc($phones_without_hash_result2)){
    $uid_ = $phone['uid'];
    $customer_id = $phone['customer_id'];
    $value = $phone['value'];
    $enc_phone2 = hash('sha256', $value);
    $update_query2 = "UPDATE o_customer_contacts SET enc_phone = '$enc_phone2' WHERE uid = $uid_";
    $update_result2 = mysqli_query($con, $update_query2);
    if($update_result2){
        $updated2++;
        echo "Phone $value hashed successfully<br/>";
    }else{
        $skipped2++;
        echo "Error hashing phone $value<br/>";
    }
}
echo "Updated $updated2 phones and skipped $skipped2 phones fro o_customer_contacts <br/>";