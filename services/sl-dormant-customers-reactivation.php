<?php
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$rpp = $_GET['rpp'] ?? 10;

try{

    $sql = "SELECT 
    c.uid,
    c.primary_mobile,
    JSON_UNQUOTE(JSON_EXTRACT(c.other_info, '$.DORMANT_ID')) AS dormant_id,
    cd.dormant_date,
    MAX(l.given_date) AS reactivation_date
  FROM o_customers c
  LEFT JOIN o_loans l ON l.customer_id = c.uid AND l.disbursed = 1
  LEFT JOIN o_customer_dormancy cd ON cd.customer_id = c.uid
  WHERE 
    JSON_VALID(c.other_info) 
    AND JSON_UNQUOTE(JSON_EXTRACT(c.other_info, '$.DORMANT_ID')) > 0
    AND l.given_date > cd.dormant_date
  GROUP BY 
    c.uid, c.primary_mobile, JSON_UNQUOTE(JSON_EXTRACT(c.other_info, '$.DORMANT_ID'))
  ORDER BY 
    l.uid DESC LIMIT $rpp";
  
  $result1 = mysqli_query($con, $sql);

  echo "Total Dormant Customers: " . mysqli_num_rows($result1) . "<br>";


  while($row = mysqli_fetch_array($result1)){
    $dormant_id = intval($row['dormant_id']);
    $customer_id = $row['uid'];
    $reactivation_date = $row['reactivation_date'];

    echo "Customer ID: $customer_id, Dormant ID: $dormant_id, Reactivation Date: $reactivation_date <br>";

    if($dormant_id > 0){
        $customer_reactivation_query = "UPDATE o_customers 
                            SET other_info = JSON_SET(
                                IFNULL(other_info, '{}'), 
                                '$.DORMANT_ID', 0 
                            ) WHERE uid = $customer_id";
        $result2 = mysqli_query($con, $customer_reactivation_query);

        if (!$result2) {
            echo "Customer Update Query failed: " . mysqli_error($con);
        }else{
            // updatedb("o_customer_dormancy", "reactivation_date='$date'", "uid = $dormant_id");
            $update_dormancy_query = "UPDATE o_customer_dormancy 
                            SET reactivation_date = '$reactivation_date' 
                            WHERE uid = $dormant_id";
            $result3 = mysqli_query($con, $update_dormancy_query);

            if(!$result3){
                echo "Customer Dormancy Update Query failed: " . mysqli_error($con);
            }else{
                // store event
                store_event("o_customers", "$customer_id", "Customer Unmarked as dormant by system", 1);
            }

        }
    }

  }

}catch(Exception $e){
    echo $e->getMessage();
    exit();
}

