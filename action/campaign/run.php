<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");


$campaign_id = $_POST['campaign_id'];
if ($campaign_id > 0) {
    $userd = session_details();
    if ($userd == null) {
        die(errormes("Your session is invalid. Please re-login"));
        exit();
    }
} else {
}

$sec = array("CAMPAIGN_ID" => "$campaign_id", "WAIVER_STATUS" => "1");
$sec_ = sanitizeAndEscape(json_encode($sec), $con);

//$upd = updatedb('o_loans',"other_info=\"$sec_\"","account_number='254790893359' AND status='7'");
//echo errormes($upd);

//echo $sec_;

if ($campaign_id > 0) {
    $camp = fetchonerow('o_campaigns', "uid = '$campaign_id'");
    $status = $camp['status'];
    $target_customers = $camp['target_customers'];
    $valid_audience = 0;
    if ($status == 1) {
        ////-----Not run
        if (is_numeric($target_customers) && $target_customers > 0) {
            $valid_audience = 1;
        } else {
            $doc = "../../campaign_uploads/$target_customers";
            if (file_exists($doc)) {
                $valid_audience = 1;
            }
        }
        if ($valid_audience == 1) {
            // echo "<tr><td>The file $doc exists</td></tr>";
            $message_id = 0;
            $message = fetchonerow('o_campaign_messages', "status=1 AND campaign_id=$campaign_id", "uid, message");
            $message_id = $message['uid'];
            $message_body = $message['message'];

            if ($message_id < 1) {
                exit(errormes("Campaign message not found"));
            } else {

                /////-------------All Looks good,
                ////Get audience
                ///Format message
                ///Queue messages
                $messages_queued = checkrowexists('o_sms_outgoing', "source_tbl='o_campaigns' AND source_record='$campaign_id'");
                if ($messages_queued == 1) {
                    exit(errormes("Messages already queued"));
                }
                
                ///////-------Read customers
                $phones_array = array();
                // echo errormes($target_customers);
                if (is_numeric($target_customers) && $target_customers > 0) {
                    ////----From a query
                    if ($target_customers == 1) {
                        ///---All Defaulters
                        $phones_array = table_to_array('o_loans', "status=7", "100000", "account_number");
                    } elseif ($target_customers == 4) {
                        ////---All with active loans, No default
                        $phones_array = table_to_array('o_loans', "status=3", "100000", "account_number");
                    } elseif ($target_customers == 5) {
                        ///----All leads & active customers
                        $phones_array = table_to_array('o_customers', "status IN (1, 3)", "100000", "primary_mobile");
                    } else {
                        echo "<tr><td colspan='5'>" . errormes("Invalid audience") . "</td></tr>";
                    }
                } else {
                    ////----From an upload

                    $open  = fopen($doc, "r");
                    if ($open !== false) {
                        while (($data = fgetcsv($open, 1000000, ",")) !== FALSE) {
                            $phone_number = trim($data[0]);
                            array_push($phones_array, $phone_number);
                        }
                        fclose($open);

                        // push my number for testing
                        array_push($phones_array, '254112553167');
                    } else {
                        exit(errormes("Unable to open the file: $doc"));
                    }
                }

                if (sizeof($phones_array) < 3) {
                    die(errormes("Audience too small < 3"));
                }

                $all_phones = implode(',', $phones_array);

                $full_names = array();
                $loan_limits = array();
                $loan_amounts = array();
                $total_repayable_amounts = array();
                $total_repaid_array = array();
                $loan_balances = array();
                $given_dates = array();
                $next_due_dates = array();
                $final_due_dates = array();

                $customers = fetchtable('o_customers', "primary_mobile in ($all_phones)", "uid", "asc", "10000000", "uid, full_name, loan_limit, primary_mobile");
                while ($cu = mysqli_fetch_array($customers)) {
                    $cuid = $cu['uid'];
                    $full_name = $cu['full_name'];
                    $loan_limit = $cu['loan_limit'];
                    $primary_mobile = $cu['primary_mobile'];
                    $full_names[$primary_mobile] = $full_name;
                    $loan_limits[$primary_mobile] = $loan_limit;
                }

                $loans = fetchtable('o_loans', "account_number in ($all_phones)", "uid", "asc", "10000000", "uid, account_number, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, given_date, next_due_date, final_due_date");
                while ($l = mysqli_fetch_array($loans)) {
                    $luid = $l['uid'];
                    $account_number = $l['account_number'];
                    $loan_amount = $l['loan_amount'];
                    $total_repayable_amount = $l['total_repayable_amount'];
                    $total_repaid = $l['total_repaid'];
                    $loan_balance = $l['loan_balance'];
                    $given_date = $l['given_date'];
                    $next_due_date = $l['next_due_date'];
                    $final_due_date = $l['final_due_date'];

                    $loan_amounts[$account_number] = $loan_amount;
                    $total_repayable_amounts[$account_number] = $total_repayable_amount;
                    $total_repaid_array[$account_number] = $total_repaid;
                    $loan_balances[$account_number] = $loan_balance;
                    $given_dates[$account_number] = $given_date;
                    $next_due_dates[$account_number] = $next_due_date;
                    $final_due_dates[$account_number] = $final_due_date;
                }

                /// -----------------End of data collection

                $queued_sms = 0; // Counter for queued messages
                $bulk_messages = [];  // Store all messages for bulk insert
                $bulk_updates = [];   // Store account numbers for bulk update

                for ($i = 0; $i < sizeof($phones_array); $i++) {
                    //////////-----------------------Bio
                    $phone = $phones_array[$i];
                    if ((validate_phone($phone)) == 1) {

                        //---------Replace variables
                        $mess = $message_body;
                        $mess = str_replace('{customers.full_name}', $full_names[$phone], $mess);
                        $mess = str_replace('{customers.loan_limit}', $loan_limits[$phone], $mess);
                        $mess = str_replace('{loans.loan_amount}', $loan_amounts[$phone], $mess);
                        $mess = str_replace('{loans.total_repayable_amount}', $total_repayable_amounts[$phone], $mess);
                        $mess = str_replace('{loans.total_repaid}', $total_repaid_array[$phone], $mess);
                        $mess = str_replace('{loans.loan_balance}', $loan_balances[$phone], $mess);
                        $mess = str_replace('{loans.given_date}', $given_dates[$phone], $mess);
                        $mess = str_replace('{loans.given_date}', $given_dates[$phone], $mess);
                        $mess = str_replace('{loans.next_due_date}', $next_due_dates[$phone], $mess);
                        $mess = str_replace('{loans.final_due_date}', $final_due_dates[$phone], $mess);
                        //---------End of replace variables

                        // Accumulate message data for bulk insert
                        $bulk_messages[] = [
                            'phone' => $phone,
                            'message_body' => $mess,
                            'queued_date' => $fulldate,
                            'created_by' => $userd['uid'],
                            'source_tbl' => 'o_campaigns',
                            'source_record' => $campaign_id,
                            'status' => 1
                        ];

                        // Accumulate accounts for bulk update
                        $bulk_updates[] = "$phone";

                        $queued_sms++;

                        // Reset message body
                        $mess = "";
                    }
                }


                // Perform bulk insert using queue_bulk_messages function
                if (!empty($bulk_messages)) {
                    $inserted_count = queue_bulk_messages($bulk_messages);
                    // echo "$inserted_count messages inserted successfully.<br>";
                }


                // Perform bulk update if there are accounts to update
                if (!empty($bulk_updates)) {
                    $phoneList = implode(",", $bulk_updates);
                    $bulk_update_query = "
                        UPDATE o_loans 
                        SET other_info = JSON_SET(
                            IFNULL(other_info, '{}'), 
                            '$.CAMPAIGN_ID', '$campaign_id', 
                            '$.WAIVER_STATUS', '1'
                        ) 
                        WHERE account_number IN ($phoneList) AND status='7'
                    ";

                    $result = mysqli_query($con, $bulk_update_query);
                    if ($result) {
                        // echo "Bulk update successful.<br>";
                    } else {
                        echo "Error during bulk update: " . mysqli_error($con);
                    }
                }


                echo sucmes("$queued_sms messages queued for processing");
                if ($queued_sms > 0) {
                    $proceed = 1;
                    updatedb('o_campaigns', "running_status=2, running_date='$fulldate'", "uid = '$campaign_id'");
                }
            }
        } else {
            exit(errormes("Audience file not found"));
        }
    } else {
        if ($status == 2) {
            exit(errormes("Campaign is already running"));
        }
        if ($status == 3) {
            exit(errormes("Campaign has already run"));
        }
    }
} else {
    exit(errormes("Campaign ID invalid"));
}

?>

<script>
    if ('<?php echo $proceed; ?>' === "1") {
        setTimeout(function() {
            reload();
        }, 60000);
    }
</script>