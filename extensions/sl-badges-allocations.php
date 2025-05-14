<?php
session_start();
include_once ('../configs/20200902.php');
include_once("../php_functions/functions.php");
$_SESSION['db_name'] = $db_;
include_once("../configs/conn.inc");

////----------Platinum customers
$max_rep = fetchtable('o_loans',"uid > 0 AND disbursed=1 AND paid=1","total_repaid","desc","5","uid, customer_id, total_repaid");
while($max = mysqli_fetch_array($max_rep)){
    $customer_id = $max['customer_id'];
    $total_repaid = $max['total_repaid'];
    $upd = updatedb('o_customers',"badge_id='4'","uid='$customer_id' AND badge_id not in (3,5, 8, 9, 10) AND total_loans >= 5");
    if($upd == 1){
        store_event('o_customers', $customer_id,"Customer given a new badge Platinum Member (4) by system service");
    }
    echo "$customer_id:".$upd;

}
/// ----------Good client
///
/// Platinum member
///
/// Attention
///
///
/// Top client

$top_clients = fetchtable('o_customers',"uid > 0 AND total_loans > 10","total_loans","desc","5","uid");
while($max = mysqli_fetch_array($top_clients)){
    $customer_id = $max['uid'];
    $upd = updatedb('o_customers',"badge_id='7'","uid='$customer_id' AND badge_id not in (3,5, 8, 9, 10, 4)");
    if($upd == 1){
        store_event('o_customers', $customer_id,"Customer given a new badge Top Client (7) by system service");
    }
     echo "$customer_id:".$upd;
}


/// ===================Defaulter // DD+1, DD+7
// Calculate the defaulter badge date
$defaulter_badge_date = datesub($date, 0, 0, $defaulter_badge_dd ? $defaulter_badge_dd : 1); // Default is 1 day

// Query to get unique defaulter customers
$defaulters_customers_query = "
    SELECT DISTINCT c.uid AS customer_id
    FROM o_customers c
    LEFT JOIN o_loans l
    ON l.customer_id = c.uid
    WHERE l.status != 0
      AND l.disbursed = 1
      AND l.paid = 0
      AND l.final_due_date <= '$defaulter_badge_date'
      AND c.badge_id != 10
";

$defaulters_customers_res = mysqli_query($con, $defaulters_customers_query);

// Collect unique customer IDs
$defaulters_customers = [];
while ($row = mysqli_fetch_array($defaulters_customers_res)) {
    $defaulters_customers[] = $row['customer_id'];
}

// Perform a bulk update if there are customers to update
if (!empty($defaulters_customers)) {
    // Convert customer IDs to a comma-separated list
    $customers_list = implode(",", $defaulters_customers);

    echo "Customers to update: " . $customers_list;

    // Bulk update customers' badge_id
    $update_query = "
        UPDATE o_customers
        SET badge_id = 10
        WHERE uid IN ($customers_list)
          AND badge_id NOT IN (10)
    ";
    $update_result = mysqli_query($con, $update_query);

    if ($update_result) {
        // Prepare data for bulk insert into o_events
        $event_values = [];
        $status = 1;
        $tbl = 'o_customers';
        $event_date = $fulldate; // Assuming $fulldate is defined globally
        $event_by = 0;
        $batch_size = 1000; // Batch size for inserting events
        $batch_count = 0;

        foreach ($defaulters_customers as $customer_id) {
            $event_details = "Customer given a new badge Defaulter(10) by system service";
            $event_values[] = "('$tbl', '$customer_id', '$event_details', '$event_date', '$event_by', $status)";
            $batch_count++;

            // Insert events in batches of 1000
            if ($batch_count % $batch_size === 0) {
                $bulk_insert_query = "
                    INSERT INTO o_events (tbl, fld, event_details, event_date, event_by, status)
                    VALUES " . implode(",", $event_values);

                $insert_result = mysqli_query($con, $bulk_insert_query);
                if (!$insert_result) {
                    echo "Event logging failed for batch: " . mysqli_error($con);
                }
                $event_values = []; // Reset the batch
            }
        }

        // Insert any remaining events
        if (!empty($event_values)) {
            $bulk_insert_query = "
                INSERT INTO o_events (tbl, fld, event_details, event_date, event_by, status)
                VALUES " . implode(",", $event_values);

            $insert_result = mysqli_query($con, $bulk_insert_query);
            if (!$insert_result) {
                echo "Event logging failed for the last batch: " . mysqli_error($con);
            }
        }

        echo "Bulk update and event logging successful for customers.";
    } else {
        echo "Bulk update failed: " . mysqli_error($con);
    }
} else {
    echo "No customers to update.";
}

// ============= End of Defaulter badge allocation

include_once("../configs/close_connection.inc");