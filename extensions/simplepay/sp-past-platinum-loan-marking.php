<?php

include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");
if ($has_archive == 1) {
    include_once("../configs/archive_conn.php");
}

$rpp = $_GET['rpp'] ?? 10; // Records per page

echo "RPP => $rpp <br>";

// Step 1: Fetch required data in a single query
$query = "SELECT max(uid) as loan_code, customer_id FROM o_loans WHERE disbursed = 1 AND loan_amount >= 21000 AND (JSON_UNQUOTE(JSON_EXTRACT(other_info, '$.IS_PLATINUM')) != '1' OR JSON_EXTRACT(other_info, '$.IS_PLATINUM') IS NULL) group by customer_id LIMIT $rpp";

echo "Query => $query <br>";

try{
    
    $query_result = mysqli_query($con, $query);

    if (!$query_result) {
        throw new Exception("Query failed: " . mysqli_error($con));
    }

    echo "Total loans to mark as platinum: " . mysqli_num_rows($query_result) . "<br>";

}catch(Exception $e){
    echo "Error: " . $e->getMessage() . "<br>";
    exit();
}




$updates = []; // Array to store loan IDs to mark as platinum

while ($row = mysqli_fetch_array($query_result)) {
    $loanID = $row['loan_code'];
    $customerID = $row['customer_id'];

    // echo "Loan ID: $loanID, Customer ID: $customerID <br>";

    // Count total loans for the customer
    $total_loans_so_far = intval(countotal_withlimit('o_loans', "customer_id = $customerID AND disbursed = 1 AND uid != $loanID", "uid", "10"));

    // echo "Total loans so far (from current): $total_loans_so_far <br>";
    if ($has_archive == 1 && empty($total_loans_so_far)) {
        $total_loans_so_far = intval(countotal_archive('o_loans', "customer_id = $customerID AND disbursed = 1 AND uid != $loanID", "uid", "10"));

        // echo "Total loans so far (from archive): $total_loans_so_far <br>";
    }

    // If no loans, add the loan ID to the updates list
    if ($total_loans_so_far <= 0) {
        $updates[] = $loanID;
    }
}

// Step 2: Execute bulk update query if there are loans to update
if (!empty($updates)) {
    $loanIDs = implode(',', $updates); // Prepare the list of loan IDs
    $mark_platinum_query = "
        UPDATE o_loans 
        SET other_info = JSON_SET(
            IFNULL(other_info, '{}'), 
            '$.IS_PLATINUM', '1'
        ) 
        WHERE uid IN ($loanIDs)
    ";

    // Execute the bulk update query
    mysqli_query($con, $mark_platinum_query);
}else{
    echo "No loans to mark as platinum";
    echo "<br>";

}


echo count($updates) . " loans marked as platinum";
echo "<br>";

echo $loanIDs . " loans marked as platinum";
