<?php
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");


// $limits_object = table_to_obj("o_customer_limits", "status = 1", "1000000", "customer_uid", "amount"); // expected output: [1 => 100, 2 => 200, 3 => 300, 4 => 400, 5 => 500]

$limits_query = "SELECT uid, customer_uid, amount FROM o_customer_limits WHERE status = 1 order by uid ASC";

$limits = mysqli_query($con, $limits_query);

$limits_object = [];
while ($row = mysqli_fetch_assoc($limits)) {
    $customer_uid = $row['customer_uid'];
    $amount = $row['amount'];

    $limits_object[$customer_uid] = $amount;
}


// order in desc order based on limit amount
// arsort($limits_object);

// echo count 
// echo "Count of limits: " . count($limits_object) . "<br>";
// echo "All Limits: " . json_encode($limits_object) . "<br>";

// filter out to remain with only those with zero limit
$zero_limits = [];
foreach ($limits_object as $customer_uid => $limit_amount) {

    // if($customer_uid != 19080){
    //     continue;
    // }else{
    //     echo "Customer UID: " . $customer_uid . " Limit Amount: " . $limit_amount . "<br>";
    // }

    if ($limit_amount == 0) {
        $zero_limits[$customer_uid] = $limit_amount;
    }
}

// order in desc order based on key value
// ksort($zero_limits);


// echo zero limits
echo "Count of zero limits: " . count($zero_limits) . "<br>";
echo "Zero limits: " . json_encode($zero_limits) . "<br>";


$updated_counter = 0;
foreach ($zero_limits as $customer_uid => $limit_amount) {

    // if($customer_uid != 19080){
    //     continue;
    // }

    echo "Customer UID: " . $customer_uid . " Limit Amount: " . $limit_amount . "<br>";

    $updated = updatedb("o_customers", "loan_limit=$limit_amount", "uid=$customer_uid AND loan_limit != $limit_amount");

    if ($updated == 1) {
        $updated_counter++;
    }
}

// echo updated counter
echo "Updated Counter: " . $updated_counter . "<br>";