<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);


$userd = session_details();
$added_by = $userd['uid'];
$file_name = $_FILES['file_']['name'];
$file_size = $_FILES['file_']['size'];
$file_tmp = $_FILES['file_']['tmp_name'];
$upload_location = '../../mpesa_uploads/';

$undisbursed_array = $loan_dates_array = array();

$two_days_ago = datesub($date,0, 0, 2);
$undisbursed = table_to_obj('o_loans',"given_date>='$two_days_ago' AND status=2","5000","account_number","uid");
$loans = fetchtable('o_loans',"given_date>='$two_days_ago' AND status=2","uid","desc","5000","uid, account_number, given_date, next_due_date, final_due_date, period, period_units");
while($l = mysqli_fetch_array($loans)){
    $lid = $l['uid'];
    $account = $l['account_number'];
    $given_date = $l['given_date'];
    $next_due_date = $l['next_due_date'];
    $final_due_date = $l['final_due_date'];
    $period = $l['period'];
    $period_units = $l['period_units'];
    $loan_dates_array[$lid] = "$given_date,$next_due_date,$final_due_date,$period,$period_units";
    $masked = substr($account, 0, 6) . "***" . substr($account, -3);
    $undisbursed_array[$masked] = $lid;

}

//echo json_encode($undisbursed_array);

$allowed_formats = "csv";
$allowed_formats_array = explode(",", $allowed_formats);

if($file_size > 10){
    if((file_type($file_name, $allowed_formats_array)) == 0){
        die(errormes("This file format is not allowed. Only $allowed_formats files"));
    }
}else{
    die(errormes("File not attached or has invalid size"));
}


$handle = fopen($file_tmp, "r");
$i = 0;

$upload = upload_file($file_name, $file_tmp, $upload_location);
if($upload === 0){
    echo errormes("Error uploading file, please retry");
    exit();
}
$saved = $skipped = $failed = 0;
$saved = 0;

$open = fopen("../../mpesa_uploads/".$upload, "r");
$data = fgetcsv($open, 10000000, ",");

while (($data = fgetcsv($open, 100000, ",")) !== FALSE){
    //$data = fgetcsv($handle);
    $mpesa_code = trim($data[0]);
    $completed_time = trim($data[1]);
    $details = trim(str_replace("'", "",$data[3]));
    $transaction_status = trim($data[4]);
    $amount = trim((int)str_replace(',', '', $data[6]));

    $amount = $amount * -1;



    // echo $data[0].'<br/>';
    // echo "Mpesa Code: $mpesa_code, Completed Time:  $completed_time, Details: $details, Trans Status: $transaction_status, Amount: $amount, Cost: $cost, Till Balance: $till_balance, Account: $account <hr/>";
    ////---------Check if this is a valid transaction
    if($amount > 200){

        // echo "Mpesa Code: $mpesa_code, Completed Time:  $completed_time, Details: $details, Trans Status: $transaction_status, Amount: $amount, Cost: $cost, Till Balance: $till_balance, Account: $account <hr/>";
        $payer = explode(' ', $details);
        $phone = trim(make_phone_valid_masked($payer[3]));

       /////// echo $phone.',';

        $matched = $undisbursed_array[$phone];



        /////// echo ">>>$matched<br/>";
        if($matched > 0){
            ////-----Phone is validated, valid
            if (input_length($mpesa_code, 5) == 1) {
                $d = datefromdatetime2($completed_time);
                $formatted_date = dateformatchange($d);

                $loan_dates = $loan_dates_array[$matched];
                $loan_dates_exp = explode(',',$loan_dates);
                $given_date = $loan_dates_exp[0];
                $next_due_date = $loan_dates_exp[1];
                $final_due_date = $loan_dates_exp[2];
                $period = $loan_dates_exp[3];
                $period_units = $loan_dates_exp[4];
                /////-----Update loan dates
                if($given_date != $formatted_date){
                    $diff = datediff3($formatted_date, $given_date);
                    $new_next_d = dateadd($next_due_date, 0,0, $diff);

                    // $final_due_d = move_to_monday(dateadd($final_due_date, 0,0, $diff));
                    $final_due_d = move_to_monday(final_due_date($formatted_date, $period, $period_units));
                    $new_dates = "NEW Dates-> Disbursed: $formatted_date, Next Due: $new_next_d, Final Due: $final_due_d";
                    $new_dates_update = ", given_date='$formatted_date', next_due_date='$new_next_d', final_due_date='$final_due_d'";

                }
                else{
                    $new_dates = "";
                    $new_dates_update = "";
                }



              ///////  echo "$matched, uid=$matched AND disbursed_amount=$amount AND given_date='$formatted_date' AND disbursed=1 AND paid=0 AND status!=0 AND uid > 0";
              ///////  echo "<br>";
                ////////////////////////////////////
            //  echo $formatted_date;
               // echo $amount.','.$formatted_date.','.$phone.'<br/>';
                $save = updatedb('o_loans', "transaction_code='$mpesa_code', disburse_state='DELIVERED', disbursed=1, status=3 $new_dates_update", "uid=$matched AND disbursed_amount=$amount AND given_date >= '$two_days_ago' AND disbursed=1 AND paid=0 AND status = 2 AND uid > 0");
                if($save == 1){
                    store_event('o_loans',"$matched","Loan marked disbursed via upload. $new_dates");
                }
//                echo "UPDATE o_loans SET transaction_code='$mpesa_code', disburse_state='DELIVERED', disbursed=1, status=3 WHERE account_number='$phone' AND disbursed_amount=$amount AND given_date='$formatted_date' AND disbursed=0 AND uid > 0; <br/>";
                $saved+=1;

            }
        }
    }


}

echo notice("Completed $saved");



function make_phone_valid_masked($phone)
{
    $cc = 254; // Country code
    $county_code = $cc;
    $phone = trim($phone);
    $phone = str_replace([' ', '+'], '', $phone); // Remove spaces and plus signs

    if ($county_code > 0) {
        // The country code is already set correctly
    } else {
        $county_code = '254'; // Default country code if none is provided
    }

    // Function to mask the phone number with exactly 3 asterisks
    $mask_phone = function($phone) {
        return substr($phone, 0, 6) . '***' . substr($phone, -3);
    };

    // If the phone starts with 254 and is 12 digits long, return it masked
    if (strlen($phone) == 12 && substr($phone, 0, 3) == "$county_code") {
        return $mask_phone($phone);
    } else {
        // If the phone starts with 0, strip the 0 and append the country code
        if (substr($phone, 0, 1) === "0") {
            $hone = ltrim($phone, "0");
            $vphone = "$county_code" . $hone;
            return $mask_phone($vphone);
        } else {
            // Otherwise, just append the country code
            return $mask_phone("$county_code" . $phone);
        }
    }
}
?>