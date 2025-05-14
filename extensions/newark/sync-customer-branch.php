<?php 

include_once("../configs/secure.php");
include_once("../php_functions/functions.php");

$sql = "SELECT `uid`, `branch` FROM o_customers";

$result = mysqli_query($con, $sql);

if (!$result) {
    die("Query 1 failed: " . mysqli_error($con));
}

$customer_branch_kv = [];

// Fetch each row and add the customer_id to the array
while ($row = mysqli_fetch_assoc($result)) {
    $cust_id = $row['uid'];
    $cust_branch = $row['branch'];

    $customer_branch_kv[$cust_id] = $cust_branch;
}

$sql2 = "
SELECT 
l.customer_id
FROM
o_loans l
    LEFT JOIN
o_customers c ON c.uid = l.customer_id
WHERE
c.branch != 0 AND l.current_branch = 0;";



$result2 = mysqli_query($con, $sql2);

if (!$result2) {
    die("Query 2 failed: " . mysqli_error($con));
}

// Fetch each row and add the customer_id to the array

$updated = $skipped = 0;
while ($r2 = mysqli_fetch_assoc($result2)) {
    $l_cust_id = intval($r2['customer_id']);
    $l_cust_branch = intval($customer_branch_kv[$l_cust_id]);


    if($l_cust_branch > 0){
        $resp = updatedb('o_loans', "current_branch = $l_cust_branch", "customer_id = $l_cust_id");
        if($resp == 1){
            $updated++;
        }else {
            $skipped++;
        }
    }else {
        $skipped++;
    }
}


echo "UPDATED => $updated <br>";
echo "SKIPPED => $skipped <br>";

?>