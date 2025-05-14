<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");


$userd = session_details();
$added_by = $userd['uid'];
$msisdn_provider = strtoupper(trim($_POST['msisdn_provider'] ?? ""));

$file_name = $_FILES['file_']['name'];
$file_size = $_FILES['file_']['size'];
$file_tmp = $_FILES['file_']['tmp_name'];
$upload_location = '../../airtel-uploads/';


if(empty($msisdn_provider)){
    // die(errormes("Please select a provider"));
}

$permi = permission($userd['uid'], 'o_incoming_payments', "0", "general_");
if ($permi != 1) {
    exit(errormes("You don't have permission to upload payment"));
}


$allowed_formats = "csv";
$allowed_formats_array = explode(",", $allowed_formats);

if ($file_size > 10) {
    if ((file_type($file_name, $allowed_formats_array)) == 0) {
        die(errormes("This file format is not allowed. Only $allowed_formats files"));
    }
} else {
    die(errormes("File not attached or has invalid size"));
}


$handle = fopen($file_tmp, "r");
$i = 0;

$upload = upload_file($file_name, $file_tmp, $upload_location);
if ($upload === 0) {
    echo errormes("Error uploading file, please retry");
    exit();
}
$saved = $skipped = $failed = 0;

$open = fopen("../../airtel-uploads/" . $upload, "r");
$titles = fgetcsv($open, 10000000, ",");
$amount_v = 0;


while (($data = fgetcsv($open, 100000, ",")) !== FALSE) {
    ///----Check if airtel or MTN
    // $amount_test = trim((int)str_replace(',', '', $data[5]));
    // $amount_test = trim((int)str_replace(',', '', $data[46]));
    $amount_test = trim((int)str_replace(',', '', $data[48]));
    if ($amount_test > 0 && $amount_test < 2000000) {
        ///----MTN
        // echo "TITLES ====> " . json_encode($titles);
        for ($column = 0; $column < count($data); $column++) {
            switch (strtolower(trim($titles[$column]))) {
                case "id": {
                        $mpesa_code = trim($data[$column]);
                        break;
                    }
                case "date": {
                        $formatted_date = (new DateTime(trim($data[$column])))->format('Y-m-d');
                        break;
                    }
                case "from name": {
                        $details = trim(str_replace("'", "", $data[$column]));
                        break;
                    }
                case "from handler name": {
                        $airtelPayerDetails = trim(str_replace("'", "", $data[$column]));
                        break;
                    }
                case "from": {
                        $phone = $account = preg_replace('/[^0-9]/', '', trim($data[$column]));
                        break;
                    }
                case "status": {
                        $transaction_status = trim($data[$column]);
                        break;
                    }
                case "amount": {
                        $amount = $amount_test = trim((int)str_replace(',', '', $data[48]));;
                        break;
                    }
                case "to / fee": {
                        $cost = trim((int)str_replace(',', '', $data[$column]));
                        break;
                    }
                case "balance": {
                        $till_balance = trim($data[$column]);   //////or paybill balance
                        break;
                    }
            }
        }

        $details = trim($details) ? $details : $airtelPayerDetails;

        // ///----Airtel
        // $mpesa_code = trim($data[0]);
        // $completed_time = trim($data[2]);
        // // $details = trim(str_replace("'", "", $data[12]));
        // $details = trim(str_replace("'", "", $data[7]));
        // // $airtelPayerDetails = trim($data[13]);
        // $airtelPayerDetails = trim($data[8]);
        // $details = trim($details) ? $details : $airtelPayerDetails;

        // // $transaction_status = trim($data[9]);
        // $transaction_status = trim($data[3]);
        // // $amount_test = trim((int)str_replace(',', '', $data[5]));
        // // $amount = trim((int)str_replace(',', '', $data[46]));
        // $amount = trim((int)str_replace(',', '', $data[48]));
        // // $cost = trim((int)str_replace(',', '', $data[10]));
        // $cost = trim((int)str_replace(',', '', $data[22]));
        // // $till_balance = trim($data[7]);   //////or paybill balance
        // $till_balance = trim($data[12]);   //////or paybill balance
        // // $account = sanitizeAndEscape($data[4], $con);
        // $account = preg_replace('/[^0-9]/', '', $data[6]);
        // // $phone = sanitizeAndEscape($data[3], $con);
        // $phone = preg_replace('/[^0-9]/', '', $data[6]);
        // $d = explode(' ', $completed_time);
        // // $dd = explode('-', $d[0]);
        // // if ($dd[2] < 100) {
        // //     $dd = explode('/', $d[0]);
        // // }
        // // $formatted_date =  $dd[2] . '-' . $dd[1] . '-' . $dd[0];
        // // if ($dd[0] <  32) {
        // //     $formatted_date =  $dd[2] . '-' . $dd[1] . '-' . $dd[0];
        // // }

        // $formatted_date = (new DateTime($completed_time))->format('Y-m-d');
    } else {
        // ["Transaction ID"," External Reference"," Transaction Date"," Sender Mobile Number"," Receiver Mobile Number"," Transaction Amount"," Previous Balance"," Post Balance"," Service Type"," Status"," External Transaction Id"," Service Charge"," Employee Detail"," Payer Details"," Approved value"," Transaction Type"]
        for ($column = 0; $column < count($data); $column++) {

            switch (trim($titles[$column])) {
                case "Transaction ID": {
                        $mpesa_code = trim($data[$column]);
                        break;
                    }
                case "Transaction Date": {
                        $formatted_date = (new DateTime(trim($data[$column])))->format('Y-m-d');
                        break;
                    }
                case "Payer Details": {
                        $details = trim(str_replace("'", "", $data[$column]));
                        break;
                    }
                case "Payer Details": {
                        $airtelPayerDetails = trim(str_replace("'", "", $data[$column]));
                        break;
                    }
                case "Sender Mobile Number": {
                        $phone = trim($data[$column]);
                        break;
                    }
                case "Status": {
                        $transaction_status = trim($data[$column]);
                        break;
                    }
                case "Transaction Amount": {
                        $amount = $amount_test = trim((int)str_replace(',', '', $data[$column]));
                        break;
                    }
                case "Service Charge": {
                        $cost = trim((int)str_replace(',', '', $data[$column]));
                        break;
                    }
                case "Post Balance": {
                        $till_balance = trim($data[$column]);   //////or paybill balance
                        break;
                    }

                case "Sender Mobile Number": {
                        $account = trim($data[$column]);
                        break;
                    }
            }

            // switch (trim($titles[$column])) {
            //     case "Id": {
            //             $mpesa_code = trim($data[$column]);
            //             break;
            //         }
            //     case "Date": {
            //             $completed_time = trim($data[$column]);
            //             $d = explode(' ', $completed_time);
            //             $dd = explode('/', $d[0]);
            //             if ($dd[2] < 100) {
            //                 $dd = explode('-', $d[0]);
            //             }
            //             $formatted_date =  $dd[0] . '-' . $dd[1] . '-' . $dd[2];
            //             if ($dd[0] <  32) {
            //                 $formatted_date =  $dd[2] . '-' . $dd[1] . '-' . $dd[0];
            //             }
            //             break;
            //         }
            //     case "From name": {
            //             $details = trim(str_replace("'", "", $data[$column]));
            //             break;
            //         }
            //     case "Payer Details": {
            //             $airtelPayerDetails = trim(str_replace("'", "", $data[$column]));
            //             break;
            //         }
            //     case "From": {
            //             $payer = trim(str_replace("'", "", $data[$column]));
            //             $payer_a = explode('/', $payer);
            //             $payer_aa = explode(':', $payer_a[0]);
            //             $phone = trim($payer_aa[1]);
            //             break;
            //         }
            //     case "Status": {
            //             $transaction_status = trim($data[$column]);
            //             break;
            //         }
            //     case "Amount": {
            //             $amount = $amount_test = trim((int)str_replace(',', '', $data[$column]));
            //             break;
            //         }
            //     case "To / Fee": {
            //             $cost = trim((int)str_replace(',', '', $data[$column]));
            //             break;
            //         }
            //     case "Balance": {
            //             $till_balance = trim($data[$column]);   //////or paybill balance
            //             break;
            //         }
            //     case "Type": {
            //             $account = sanitizeAndEscape($data[$column], $con);
            //             break;
            //         }
            // }


        }



        // break;

        // // echo "MTN UPLOAD  $amount_test <br>";

        // if ($amount_test > 0) {
        //     //----MTN



        //     $mpesa_code = trim($data[0]);
        //     $completed_time = trim($data[2]);
        //     $details = trim(str_replace("'", "", $data[7]));
        //     $payer = trim(str_replace("'", "", $data[6]));
        //     $payer_a = explode('/', $payer);
        //     $payer_aa = explode(':', $payer_a[0]);



        //     $transaction_status = trim($data[3]);
        //     $amount = trim((int)str_replace(',', '', $data[50]));
        //     $cost = trim((int)str_replace(',', '', $data[24]));
        //     $till_balance = trim($data[48]);   //////or paybill balance
        //     $account = sanitizeAndEscape($data[4], $con);
        //     $phone = trim($payer_aa[1]);
        //     $d = explode(' ', $completed_time);
        //     $dd = explode('/', $d[0]);
        //     if ($dd[2] < 100) {
        //         $dd = explode('-', $d[0]);
        //     }
        //     $formatted_date =  $dd[0] . '-' . $dd[1] . '-' . $dd[2];
        //     if ($dd[0] <  32) {
        //         $formatted_date =  $dd[2] . '-' . $dd[1] . '-' . $dd[0];
        //     }
        // } else {
        // }
    }

    if ($amount_test > 2500000 || $amount_test <= 0) {
        continue;
    }


    ////---------Check if this is a valid transaction
    $phone_valid = validate_phone(make_phone_valid($phone));
    $mpesa_code_valid = input_length($mpesa_code, 8);
    $date_diff = datediff3($formatted_date, $date);
    //echo "$formatted_date";


    // echo "formatted_date => $formatted_date from => $phone, details => $details <br>";
    // echo("amount_test => $amount_test, phone_valid ===> $phone_valid, date_diff ===> $date_diff<br>");

    if ($amount_test > 0 && $phone_valid == 1 && $date_diff < 365) {

        //////////-------------Verify data


        //   echo "Mpesa Code: $mpesa_code, Completed Time:  $completed_time, Details: $details, Trans Status: $transaction_status, Amount: $amount, Cost: $cost, Till Balance: $till_balance, Account: $account <hr/>";
        $payer = explode(' ', $details);


        // echo ">>>$phone<br/>"; 
        // echo make_phone_valid($phone).',';
        $validated_acc = make_phone_valid($account);
        $validated_phone = make_phone_valid($phone);
        if (validate_phone($validated_phone) == 1 || validate_phone($validated_acc) == 1) {
            ////-----Phone is validated, valid
            if (input_length($mpesa_code, 5) == 1) {
                // $d = datefromdatetime2($completed_time);
                // $formatted_date = dateformatchange($d);

                ////////////////////////////////////
                //  echo $completed_time.$formatted_date;
                //  die();
                $customer_det = fetchonerow('o_customers', "primary_mobile IN ('$validated_acc', '$validated_phone') OR national_id='$account'", "uid, branch");
                $customer_id = intval($customer_det['uid']);
                if ($customer_id > 0) {
                } else {
                    $customer_det = fetchonerow('o_customer_contacts', "value IN ('$validated_acc', '$validated_phone') AND status = 1", "customer_id");
                    $customer_id = intval($customer_det['customer_id']);
                    if ($customer_id > 0) {
                        $customer_det['branch'] = fetchrow('o_customers', "uid='$customer_id'", "branch");
                    }
                    // else {
                    //     $customer_det = fetchonerow('o_customer_referees', "mobile_no IN ('$validated_acc', '$validated_phone') AND status = 1", "customer_id");
                    //     $customer_id = intval($customer_det['customer_id']);

                    //     if($customer_id > 0){
                    //         $customer_det['branch'] = fetchrow('o_customers', "uid='$customer_id'", "branch");
                    //     }
                    // }
                }
                if ($customer_id > 0) {
                    $branch_id = $customer_det['branch'];
                    $latest_loan = fetchmaxid('o_loans', "customer_id='$customer_id' AND disbursed=1 AND paid=0", "uid, current_co, current_agent");
                    $latest_loan_id = $latest_loan['uid'];
                    $collector = $latest_loan['current_co'];
                    $current_agent = $latest_loan['current_agent'];
                    if ($current_agent > 0) {
                        $collector = $current_agent;
                    } else {
                        $collector = $latest_loan['current_co'];
                    }
                } else {
                    $latest_loan_id = 0;
                    $collector = 0;
                    $branch_id = 0;
                }
                $payment_method = 3;




                $formatted_date = trim($formatted_date);
                if (datediff3($formatted_date, $date) > 365) {
                    $formatted_date = $date;
                }


                if (validate_phone($validated_phone) == 1) {
                    $phone = $validated_phone;
                } elseif (validate_phone($validated_acc) == 1) {
                    $phone = $validated_acc;
                }

                // handle payment meant for topping up account
                $accout_top_up_phones = ['256709107017', '256100103697'];
                if (in_array($phone, $accout_top_up_phones)) {
                    $status = 0;
                } else {
                    $status = 1;
                }

                $fds = array('customer_id', 'branch_id', 'payment_method', 'mobile_number', 'amount', 'transaction_code', 'loan_id', 'payment_date', 'recorded_date', 'record_method', 'added_by', 'collected_by', 'comments', 'status');
                $vals = array("$customer_id", "$branch_id", "$payment_method", "$phone", "$amount", "$mpesa_code", "$latest_loan_id", "$formatted_date", "$fulldate", "UPLOAD", "$added_by", "$collector", "$details", $status);


                $save = addtodb('o_incoming_payments', $fds, $vals);
                if ($save == 1) {
                    $saved = $saved + 1;
                    //  echo "Save Payment for Loan $latest_loan_id: $save";
                    if ($latest_loan_id > 0) {
                        recalculate_loan($latest_loan_id);

                        $ld = fetchonerow("o_incoming_payments", "transaction_code = '$mpesa_code'", "uid");
                        $max_pid = $ld["uid"];

                        $balance = loan_balance($latest_loan_id);

                        $balanceup = updatedb("o_incoming_payments", "loan_balance = '$balance'", "uid = $max_pid");
                        //  echo "Update Balance: $balance, Payment Id:$max_pid, $ Result: $balanceup, Loan: $latest_loan_id ";

                    }
                } else {
                    // echo "Save Payment for Loan $latest_loan_id:$save";
                    $skipped = $skipped + 1;
                }
            }
        }
    } else {
        $amount_v = $amount_v + 1;
    }
}

if ($amount_v > 1) {

    echo errormes("The document is not in the right format");
}

echo notice("Added: $saved, Skipped: $skipped");

unlink($upload_location . $upload);
