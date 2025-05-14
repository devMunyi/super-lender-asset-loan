<?php 

// SELECT * FROM o_incoming_payments where customer_id > 0 AND mobile_number IN (null, '') order by uid DESC;

session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$customer_l = table_to_array('o_incoming_payments',"customer_id > 0 AND mobile_number IN (null, '')","1000000","customer_id");
$customer_list = implode(',', $customer_l);

$phones = table_to_obj('o_customers',"uid in ($customer_list)","1000000","uid","primary_mobile");


$pays = fetchtable('o_incoming_payments', "customer_id > 0 AND mobile_number IN (null, '')", "uid", "DESC", "1000000", "customer_id, uid");

// echo mysqli_num_rows($pays);
$updated = $skipped = 0;
while($pay = mysqli_fetch_assoc($pays)){
    $pay_id = intval($pay['uid']);
    if($pay_id !== 2946348){
        continue;
    }
    $customer_id = $pay['customer_id'];
    $primary_mobile = $phones[$customer_id];
    // echo "CUST ID => $customer_id , $primary_mobile<br>";
    
    if(validate_phone($primary_mobile) == 1){
        $sql = "UPDATE o_incoming_payments SET mobile_number = '$primary_mobile' WHERE customer_id = $customer_id AND mobile_number IN (null, '')";
        $result = mysqli_query($con, $sql);
        if($result){
            echo "Pay ID => $pay_id, UPDATED CUST ID => $customer_id, PHONE => $primary_mobile <br>";
            $updated += 1;
        }else {
            echo "Pay ID => $pay_id, SKIPPED ON SQL EXECUTION, CUST ID => $customer_id, PHONE => $primary_mobile <br>";
            $skipped += 1;
        }
    }else {
        echo "Pay ID => $pay_id, SKIPPED CUST ID => $customer_id, PHONE => $primary_mobile <br>";
        $skipped += 1;
    }
    
}

echo "UPDATED $updated <br>";
echo "SKIPPED $skipped <br>";