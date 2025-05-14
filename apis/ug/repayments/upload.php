<?php

// allowed origins
$allowedOrigins = [
    'localhost'
];

// Check the request method
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Check the 'Host' header to determine the origin
$headers = getallheaders();
$origin = $headers['Host'];

if ($requestMethod != 'POST') {
    http_response_code(404);
    exit;
}

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: " . $origin);

    // Only set Access-Control-Allow-Methods for POST requests
    if ($requestMethod === 'POST') {
        header("Access-Control-Allow-Methods: POST");
    }

    // header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
    // header("Content-Type: application/json; charset=UTF-8");
} else {
    http_response_code(403);
    exit;
}

require("../../../vendor/autoload.php");
include_once("../../../configs/conn.inc");
include_once("../../../php_functions/functions.php");
include_once("../../../php_functions/jwtAuth.php");
include_once("../../../configs/jwt.php");


// check access token validity
$payload = verifyBearerTokenFromHeaders($jwt_key);

if ($payload === null) {
    sendApiResponse(401, "Access token missing or invalid");
}

$user = $payload->user;
$added_by = intval($user->uid);
$user_type = $user->user_type ? $user->user_type : '';


// $raw_input = json_decode(file_get_contents('php://input'), true);
$file_data = $_FILES['file_'];
$file_name = $file_data['name'];
$file_size = $file_data['size'];
$file_tmp = $file_data['tmp_name'];
$upload_location = '../../../airtel-uploads/';


$permi = permission($added_by, 'o_incoming_payments', "0", "general_");
if ($permi != 1) {
    sendApiResponse(401, "You don't have permission to upload payment");
}


$allowed_formats = "csv";
$allowed_formats_array = explode(",", $allowed_formats);

if ($file_size > 10) {
    if ((file_type($file_name, $allowed_formats_array)) == 0) {
        sendApiResponse(400, "This file format is not allowed. Only $allowed_formats files");
    }
} else {
    sendApiResponse(400, "File not attached or has invalid size");
}

$handle = fopen($file_tmp, "r");
$i = 0;

$upload = upload_file($file_name, $file_tmp, $upload_location);
if ($upload === 0) {
    sendApiResponse(500, "Error uploading file, please retry");
}
$saved = $skipped = $failed = 0;

$open = fopen("../../../airtel-uploads/" . $upload, "r");
$amount_v = 0;
$is_first_row = true; // Flag variable to track the first row

$maxIterations = 10000; // Set a maximum number of iterations
while (($data = fgetcsv($open, 100000, ",")) !== FALSE) {
    if ($is_first_row) {
        $is_first_row = false;
        continue; // Skip the first row and move to the next iteration
    }

    //$data = fgetcsv($handle);
    ///----Check if airtel or MTN
    $amount_test = trim((int)str_replace(',', '', $data[5]));
    if ($amount_test > 0 && $amount_test < 1500000) {
        ///----Airtel
        $mpesa_code = trim($data[0]);
        $completed_time = trim($data[2]);
        $details = trim(str_replace("'", "", $data[12]));
        $transaction_status = trim($data[9]);
        $amount = trim((int)str_replace(',', '', $data[5]));
        $cost = trim((int)str_replace(',', '', $data[10]));
        $till_balance = trim($data[7]);   //////or paybill balance
        $account = trim($data[4]);
        $phone = $data[3];
        $d = explode(' ', $completed_time);
        $dd = explode('-', $d[0]);
        if ($dd[2] < 100) {
            $dd = explode('/', $d[0]);
        }
        $formatted_date =  $dd[2] . '-' . $dd[1] . '-' . $dd[0];
        if ($dd[0] <  32) {
            $formatted_date =  $dd[2] . '-' . $dd[1] . '-' . $dd[0];
        }
    } else {
        $amount_test = trim((int)str_replace(',', '', $data[14]));

        if ($amount_test > 0) {
            //----MTN
            $mpesa_code = trim($data[0]);
            $completed_time = trim($data[2]);
            $details = trim(str_replace("'", "", $data[9]));
            $payer = trim(str_replace("'", "", $data[8]));
            $payer_a = explode('/', $payer);
            $payer_aa = explode(':', $payer_a[0]);

            $transaction_status = trim($data[3]);
            $amount = trim((int)str_replace(',', '', $data[14]));
            $cost = trim((int)str_replace(',', '', $data[20]));
            $till_balance = trim($data[28]);   //////or paybill balance
            $account = trim($data[4]);
            $phone = $payer_aa[1];
            $d = explode(' ', $completed_time);
            $dd = explode('/', $d[0]);
            if ($dd[2] < 100) {
                $dd = explode('-', $d[0]);
            }
            $formatted_date =  $dd[0] . '-' . $dd[1] . '-' . $dd[2];
            if ($dd[0] <  32) {
                $formatted_date =  $dd[2] . '-' . $dd[1] . '-' . $dd[0];
            }
        } else {
        }
    }



    //echo $data[0].'<br/>';
    // echo "Mpesa Code: $mpesa_code, Completed Time:  $completed_time, Details: $details, Trans Status: $transaction_status, Amount: $amount, Cost: $cost, Till Balance: $till_balance, Account: $account <hr/>";
    ////---------Check if this is a valid transaction
    $phone_valid = validate_phone(make_phone_valid($phone));
    $mpesa_code_valid = input_length($mpesa_code, 8);
    $date_diff = datediff3($formatted_date, $date);

    //echo "$formatted_date";

    if ($amount_test > 0 && $phone_valid == 1 && $date_diff < 365) {

        //////////-------------Verify data


        //   echo "Mpesa Code: $mpesa_code, Completed Time:  $completed_time, Details: $details, Trans Status: $transaction_status, Amount: $amount, Cost: $cost, Till Balance: $till_balance, Account: $account <hr/>";
        $payer = explode(' ', $details);


        // echo ">>>$phone<br/>";
        // echo make_phone_valid($phone).',';
        if (validate_phone(make_phone_valid($phone)) == 1) {
            ////-----Phone is validated, valid
            if (input_length($mpesa_code, 5) == 1) {
                // $d = datefromdatetime2($completed_time);
                // $formatted_date = dateformatchange($d);

                ////////////////////////////////////
                //  echo $completed_time.$formatted_date;
                //  die();
                $validated_acc = make_phone_valid($account);
                $validated_phone = make_phone_valid($phone);
                $customer_det = fetchonerow('o_customers', "primary_mobile='$validated_acc' OR national_id='$validated_acc' OR primary_mobile='$validated_phone'", "uid, branch");
                $customer_id = intval($customer_det['uid']);
                if ($customer_id > 0) {
                } else {
                    $customer_det = fetchonerow('o_customer_contacts', "value='$validated_acc' OR value='$validated_phone'", "customer_id");
                    $customer_id = intval($customer_det['customer_id']);
                    if ($customer_id > 0) {
                        $customer_det['branch'] = fetchrow('o_customers', "uid='$customer_id'", "branch");
                    }
                }
                if ($customer_id > 0) {
                    $branch_id = intval($customer_det['branch']);
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
                }
                $payment_method = 3;

                $formatted_date = trim($formatted_date);
                if (datediff3($formatted_date, $date) > 365) {
                    $formatted_date = $date;
                }
                // echo $formatted_date.',';
                $fds = array('customer_id', 'branch_id', 'payment_method', 'mobile_number', 'amount', 'transaction_code', 'loan_id', 'payment_date', 'recorded_date', 'record_method', 'added_by', 'collected_by', 'comments', 'status');
                $vals = array("$customer_id", $branch_id, "$payment_method", "$phone", "$amount", "$mpesa_code", "$latest_loan_id", "$formatted_date", "$fulldate", "UPLOAD", "$added_by", "$collector", "$details", "1");
                //  echo implode(',', $vals) . '<br/>';
                // echo $formatted_date.',';


                $save = addtodb('o_incoming_payments', $fds, $vals);
                echo $save;
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

    $i++;

    // Check if we've reached the maximum number of iterations
    if ($i >= $maxIterations) {
        // break; // Exit the loop
    }
}

if ($amount_v > 1) {
    sendApiResponse(400, "The document is not in the right format");
} else {
    sendApiResponse(200, "Upload Successsful", "OK", ["saved" => $saved, "skipped" => $skipped]);
}

