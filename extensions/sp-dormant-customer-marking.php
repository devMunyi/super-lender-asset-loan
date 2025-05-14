<?php

include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");
if ($has_archive == 1) {
    include_once("../configs/archive_conn.php");
}

// enable error reporting
// error_reporting(E_ALL);

$dormant_threshold = 45; //==> 45 days since last payment was made

try {

    $sql = "SELECT l.customer_id 
    FROM o_loans l 
    RIGHT JOIN o_customers c ON c.uid = l.customer_id  
    WHERE   
        l.disbursed = 1 
        AND l.status > 0
        AND c.status = 1
        AND (
            (JSON_VALID(c.other_info) AND JSON_UNQUOTE(JSON_EXTRACT(c.other_info, '$.DORMANT_ID')) <= 0) 
            OR c.other_info IS NULL
        ) 
    ORDER BY l.uid ASC";

    $result = mysqli_query($con, $sql);

    if (!$result) {
        throw new Exception("Query failed cdb: " . mysqli_error($con));
    }
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

$customer_ids = [];

// Fetch each row and add the customer_id to the array
while ($row = mysqli_fetch_assoc($result)) {
    $cust_id = $row['customer_id'];
    // $l_status = $row['status'];
    $customer_ids[] = $cust_id;
    // $customer_ks[$cust_id] = $l_status;
}



if ($has_archive == 1) {

    try {

        $sql = "SELECT l.customer_id 
        FROM o_loans l 
        RIGHT JOIN o_customers c ON c.uid = l.customer_id  
        WHERE   
            l.disbursed = 1 
            AND l.status > 0
            AND c.status = 1
            AND (
                (JSON_VALID(c.other_info) AND JSON_UNQUOTE(JSON_EXTRACT(c.other_info, '$.DORMANT_ID')) <= 0) 
                OR c.other_info IS NULL
            ) 
        ORDER BY l.uid ASC";

        $archive_result = mysqli_query($con1, $sql);

        if (!$archive_result) {
            throw new Exception("Query failed adb: " . mysqli_error($con));
        }
    } catch (Exception $e) {
        echo $e->getMessage();
        exit();
    }

    // $archived_customer_ks = [];
    $archived_customer_ids = [];

    // Fetch each row and add the customer_id to the array
    while ($row = mysqli_fetch_assoc($archive_result)) {
        $cust = $row['customer_id'];
        // $l_status = $row['status'];
        if ($cust > 0) {
            $archived_customer_ids[] = $cust;
            // $archived_customer_ks[$cust] = $l_status;
        }
    }

    // merge the two arrays
    $customer_ids = array_merge($customer_ids, $archived_customer_ids);
    // $customer_ks = array_merge($customer_ks, $archived_customer_ks);

    // remove duplicates 
    $customer_ids = array_unique($customer_ids);

    // convert to comma separated string
    $customer_list = implode(',', $customer_ids);
} else {
    $customer_list = implode(',', $customer_ids);
}


/////--------------Customers with loans

$customers_with_loans = table_to_array('o_loans', "disbursed=1 AND paid=0 AND status!=0 AND customer_id in ($customer_list)", "10000000", "customer_id");



// remove duplicates
$customers_with_loans = array_unique($customers_with_loans);

$customers_with_loans_list = implode(',', $customers_with_loans);
///----------------End of customers with loans


// ==== Begin Loans Table fetch of dormant customers
$dormant_customer_array = table_to_array('o_customers', "uid IN ($customer_list) AND uid not in ($customers_with_loans_list) AND status = 1 AND ((JSON_VALID(other_info) AND JSON_UNQUOTE(JSON_EXTRACT(other_info, '$.DORMANT_ID')) <= 0) OR other_info IS NULL)", "10000000", "uid");

$dormant_customer_list = implode(',', $dormant_customer_array);
$cdb_last_loan_query = "SELECT customer_id, `uid` as loan_id FROM o_loans WHERE customer_id IN ($dormant_customer_list) AND disbursed = 1 AND status not in (0, 6) order by uid ASC";

$dormant_customers_kv = [];
$cdb_last_loan = mysqli_query($con, $cdb_last_loan_query);
while ($c = mysqli_fetch_assoc($cdb_last_loan)) {
    $dormant_customers_kv[$c['customer_id']] = $c['loan_id'];
}




// === get customers in $dormant_customer_array but missing in $dormant_customers_kv
// === meaning they have loans in archive
if ($has_archive == 1 && count($dormant_customers_kv) < count($dormant_customer_array)) {
    $adb_customer_array = array_diff($dormant_customer_array, array_keys($dormant_customers_kv));

    $adb_customer_list = implode(',', $adb_customer_array);

    $adb_last_loan_query = "SELECT customer_id, `uid` as loan_id FROM o_loans WHERE customer_id IN ($adb_customer_list) AND disbursed = 1 AND status not in (0, 6) order by uid ASC";


    $adb_last_loan = mysqli_query($con1, $adb_last_loan_query);
    while ($c = mysqli_fetch_assoc($adb_last_loan)) {

        // add it to the $dormant_customers_kv if it does not exist
        if (!isset($dormant_customers_kv[$c['customer_id']])) {
            $dormant_customers_kv[$c['customer_id']] = $c['loan_id'];
        }
    }
}

///==== End of Loans Table fetch of dormant customers




////==== Begin fetch payments for the loans which marks the last payment date and customer dormancy

// from dormant_customers_kv extract loan_ids as array
$loan_uid_array = array_values($dormant_customers_kv);
$loan_uid_list = implode(',', $loan_uid_array);

$payments_query = "SELECT loan_id, payment_date FROM o_incoming_payments WHERE loan_id IN ($loan_uid_list) AND status = 1 order by payment_date ASC"; // order is important for sliding window technique

$payments_result = mysqli_query($con, $payments_query);

$payment_dormancy_kv = [];
while ($p = mysqli_fetch_assoc($payments_result)) {
    $payment_dormancy_kv[$p['loan_id']] = $p['payment_date'];
}

if ($has_archive == 1 && count($payment_dormancy_kv) < count($loan_uid_array)) {

    $adb_payments_query = "SELECT loan_id, payment_date FROM o_incoming_payments WHERE loan_id IN ($loan_uid_list) AND status = 1 order by payment_date ASC"; // order is important for sliding window technique

    $adb_payments_result = mysqli_query($con1, $adb_payments_query);

    while ($p = mysqli_fetch_assoc($adb_payments_result)) {
        // add it to the $payment_dormancy_kv if it does not exist
        if (!isset($payment_dormancy_kv[$p['loan_id']])) {
            $payment_dormancy_kv[$p['loan_id']] = $p['payment_date'];
        }
    }

}

///==== End of fetching payments


// loop $dormant_customers_kv
$updated_counter = 0;
$skipped_counter = 0;

// echo "Total Dormant Customers kv: " . json_encode($dormant_customers_kv) . "<br>";
// echo "Count kv " . count($dormant_customers_kv) . "<br>";

$dormant_customer_vk = array_flip($dormant_customers_kv); // Flip keys and values for direct access of customer_id from loan_id

// echo "Total Dormant Customers vk: " . json_encode($dormant_customer_vk) . "<br>";
// echo "Count vk " . count($dormant_customer_vk) . "<br>";

foreach ($payment_dormancy_kv as $loan_id => $payment_date) {

    $customer_id = intval($dormant_customer_vk[$loan_id] ?? 0);
    if($customer_id == 0){
        echo "Customer ID is $customer_id hence skipped! <br>";
        continue;
    }

    $last_payment_date = $payment_date;
    $last_payment_date = strtotime($last_payment_date);
    $today = strtotime($date);
    $diff = $today - $last_payment_date;
    $days = floor($diff / (60 * 60 * 24));


    if ($days >= $dormant_threshold) {
        // do an insert to o_customer_dormancy table
        // required fields customer_id, dormant_date
        // dormant date should be in YYYY-MM-DD format
        // calculated by adding $dormant_threshold to the last loan date

        $dormant_date = date('Y-m-d', strtotime($payment_date . " + $dormant_threshold days"));

        // echo "Customer ID: $customer_id, Last Loan Date: $payment_date, Dormant Date: $dormant_date <br>";

        $sql = "INSERT INTO o_customer_dormancy (customer_id, dormant_date, created_at) VALUES ($customer_id, '$dormant_date', '$date')";

        $result = mysqli_query($con, $sql);
        if (!$result) {
            echo "Error inserting dormant customers: " . mysqli_error($con) . "<br>";

            $skipped_counter++;
        } else {
            $inserted_id = mysqli_insert_id($con);

            $sql = "UPDATE o_customers 
                        SET other_info = JSON_SET(
                            IFNULL(other_info, '{}'), 
                            '$.DORMANT_ID', '$inserted_id' 
                        ) WHERE uid = $customer_id";
            $result = mysqli_query($con, $sql);
            if (!$result) {
                echo "Error updating dormant customers: " . mysqli_error($con) . "<br>";

                $skipped_counter++;
            } else {
                store_event("o_customers", "$customer_id", "Customer marked as dormant by system", 1);
                $updated_counter++;
            }
        }
    }

    // clean up variables that could be accidentally retained in the next iteration
    unset($last_payment_date, $today, $diff, $days, $dormant_date, $sql, $result, $inserted_id, $customer_id, $loan_id, $payment_date);
}


echo "Updated: $updated_counter, Skipped: $skipped_counter <br>";

// include close connection
include_once("../configs/close_connection.inc");
