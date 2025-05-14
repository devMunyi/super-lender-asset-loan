<?php 

//== This script checks if customer should be reactivated i.e unset DORMANT_ID
// Conditions: if DORMANT_ID is set to integer value greater than 0 which references o_customer_dormancy table primary key
//== $customerID will be available in the context of the script.
//==  Hook it to All Products

$sql = "SELECT JSON_UNQUOTE(JSON_EXTRACT(other_info, '$.DORMANT_ID')) AS dormant_id  
FROM o_customers 
WHERE JSON_VALID(other_info) 
    AND JSON_UNQUOTE(JSON_EXTRACT(other_info, '$.DORMANT_ID')) > 0 
    AND uid = $customerID LIMIT 1";

$result = mysqli_query($con, $sql);

if (!$result) {
    echo "Query failed: " . mysqli_error($con);
}

$row = mysqli_fetch_assoc($result);
$dormant_id = intval($row['dormant_id'] ?? 0);

// update reactivation_date from o_customer_dormancy table
// where dormant_id = $dormant_id;

if($dormant_id > 0){
    $customer_reactivation_query = "UPDATE o_customers 
                        SET other_info = JSON_SET(
                            IFNULL(other_info, '{}'), 
                            '$.DORMANT_ID', 0 
                        ) WHERE uid = $customerID";
    $result = mysqli_query($con, $customer_reactivation_query);

    if (!$result) {
        echo "Query failed: " . mysqli_error($con);
    }else{
        // updatedb("o_customer_dormancy", "reactivation_date='$date'", "uid = $dormant_id");
        $update_dormancy_query = "UPDATE o_customer_dormancy 
                        SET reactivation_date = '$date' 
                        WHERE uid = $dormant_id";
        $result = mysqli_query($con, $update_dormancy_query);

        if(!$result){
            echo "Query failed: " . mysqli_error($con);
        }else{
            // store event
            store_event("o_customers", "$customerID", "Customer Unmarked as dormant by system", 1);
        }

    }
}



