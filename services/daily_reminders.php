<?php
session_start();
include_once("../configs/20200902.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include_once("../php_functions/functions.php");


$db = $db_;
$_SESSION['db_name'] = $db;
include_once("../configs/conn.inc");

$last_ = fetchrow('o_last_service', "uid=1", "last_date");
$dt = new DateTime($last_);

$last_date = $dt->format('Y-m-d');
$events_array = array('INSTALMENT_DATE', 'DUE_TOMORROW', 'DUE_TODAY', 'DUE_YESTERDAY');
$yesterday = datesub($date, 0, 0, 1);
$tomorrow = dateadd($date, 0, 0, 1);
$due_dates = "'$date','$yesterday','$tomorrow'";

if ($last_date == $date) {
    //  die("Scheduled messages already sent for $fulldate $db");
    //   exit();
} else {
    echo "Ready to send messages<br/>";
}

$reminder_obj = array();
$statuses = array();
$loan_days = array();
$products = array();

$all_reminders = fetchtable('o_product_reminders', "status=1", "uid", "asc", "1000", "uid, message_body, product_id, loan_day, loan_status, custom_event");
while ($ar = mysqli_fetch_array($all_reminders)) {
    $uid = $ar['uid'];
    $message_body = $ar['message_body'];
    $product_id = $ar['product_id'];
    $loan_day = $ar['loan_day'];
    $loan_date = datesub($date, 0, 0, $loan_day);
    $loan_status = $ar['loan_status'];
    $custom_event = $ar['custom_event'];

    // echo "$loan_status <br/>";

    $reminder_obj = array();
    $reminder_obj['message_body'];

    array_push($statuses, $loan_status);
    array_push($loan_days, $loan_date);
    array_push($products, $product_id);

    if ($loan_day > 0) {
        ////----Reminders for specific days e.g. 1, 2, 4
        $reminder_details[$product_id][$loan_day][$loan_status] = $message_body;
    } else {
        ///---Reminders for common days e.g. due dates
        if (in_array($custom_event, $events_array)) {
            $reminder_details[$product_id][$loan_day][$loan_status][$custom_event] = $message_body;
        }
    }
}

echo "<br/>Reminders<br/>";

$status_string = implode(',', $statuses);
$days_string = "'" . implode("','", $loan_days) . "'";
$products_string = implode(',', $products);

///////-------------------------End of get company details
///-------Lets start here
$customers_list = table_to_array('o_loans', "disbursed=1 AND paid=0 AND status !=0 AND status in ($status_string) AND (given_date in ($days_string) OR next_due_date = '$date' OR final_due_date in ($due_dates)) AND product_id in ($products_string)", "10000", "customer_id", "uid", "asc");
echo "<br/>Customer List<br/>";

$all_customers = array();
$customer_details = fetchtable('o_customers', "uid in (" . implode(',', $customers_list) . ")", "uid", "asc", "10000", "uid, full_name, primary_mobile, email_address, national_id, loan_limit, total_loans");
while ($cd = mysqli_fetch_array($customer_details)) {
    $cid = $cd['uid'];
    $full_name = $cd['full_name'];
    $names = explode(' ', $full_name);
    $customer_array = array();
    foreach ($cd as $fieldName => $fieldValue) {
        $customer_array[$fieldName] = $fieldValue; // Assign field name as key and field value as value
        $customer_array['first_name'] = $names[0]; // Assign field name as key and field value as value
    }
    $all_customers[$cid] = $customer_array;
}
echo "<br/>All Customers<br/>";

$expected_queue_total = 0;
$queued_total = 0;
$loans = fetchtable('o_loans', "disbursed=1 AND paid=0 AND status !=0 AND status in ($status_string) AND (given_date in ($days_string) OR next_due_date = '$date' OR final_due_date in ($due_dates)) AND product_id in ($products_string)", "uid", "desc", "10000", "uid, customer_id, account_number, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, period, period_units, given_date, next_due_date, final_due_date, status");
while ($l = mysqli_fetch_array($loans, MYSQLI_ASSOC)) {
    
    $messaged_dec = ""; // initialize the message as empty string
    $uid = $l['uid'];
    $customer_id = $l['customer_id'];
    $account_number = $l['account_number'];
    $product_id = $l['product_id'];
    $given_date = $l['given_date'];
    $next_due_date = $l['next_due_date'];
    $final_due_date = $l['final_due_date'];
    $status = $l['status'];
    $days_ago = datediff($given_date, $date);

    $message = $reminder_details[$product_id][$days_ago][$status];
    if (input_available($message) == 0) {
        if ($final_due_date == $date) {
            $custom_event_ = 'DUE_TODAY';
        } elseif ($final_due_date == $yesterday) {
            $custom_event_ = 'DUE_YESTERDAY';
        } elseif ($final_due_date == $tomorrow) {
            $custom_event_ = 'DUE_TOMORROW';
        } elseif ($next_due_date == $date) {
            $custom_event_ = 'INSTALMENT_DATE';
        } else {
            $custom_event_ = 'XXfff';
        }
        $message = $reminder_details[$product_id][0][$status][$custom_event_];
    }


    if (input_length($message, 10) == 1) {
        $loan_array = array(); // Create an empty array for each row
        foreach ($l as $fieldName => $fieldValue) {
            $loan_array[$fieldName] = $fieldValue; 
        }
        $customer_obj = $all_customers[$customer_id];
        if (doubleval($loan_array['loan_balance']) > 0) {
            $messaged_dec = convert_message_offline($message, $loan_array, $customer_obj);
        } 
    }

    if (input_length($messaged_dec, 5) == 1) {
        // Instead of calling queue_message(), add data to the bulk array
        $bulk_messages[] = [
            'phone' => $account_number,
            'message_body' => $messaged_dec,
            'queued_date' => $fulldate,
            'created_by' => 0,
            'source_tbl' => '',
            'source_record' => 0,
            'status' => 1
        ];

        $expected_queue_total += 1;
    }

    // Clean up reusable variables in the loop
    unset($messaged_dec, $loan_array, $customer_obj);
    
}

// Perform bulk insert if there are messages to insert
if (!empty($bulk_messages)) {
    $queued_total = queue_bulk_messages($bulk_messages);
}

echo "Expected Queue Total: $expected_queue_total<br/>";
echo "Queued Total: $queued_total<br/>";

// clean up queued messages
unset($expected_queue_total, $queued_total, $bulk_messages);


include_once("../configs/close_connection.inc");
