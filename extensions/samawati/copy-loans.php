<?php
session_start();
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

error_reporting(0);
// Open the CSV file
$file = fopen('../test/sal-loans2.csv', 'r');

$locations = array(
    "Pipeline" => 439,
    "HQ" => 1,
    "Kiambu" => 436,
    "Thika" => 440,
    "Kinoo" => 438,
    "Ruai" => 441,
    "Ruiru" => 437,
    "Head Office" =>1
);

// Skip the header row if it exists
fgetcsv($file);
$order = 10;
// Loop through each row
while (($row = fgetcsv($file)) !== false) {
    // Extract the variables
 //---   RefId[0],ImportLoanNo[1],LoanNo[2],Created[3],Disbursed[4],Maturity[5],Payment Cycle[6],Customer[7],ID Number[8],Email[9],Phone Number[10],Product Name[11],Employee Number[12],Loan Source[13],Loan Status[14],Payment Status[15],Balance B/F[16],Principal[17],Disbursed[18],Interest[19],Fees[20],Total[21],Penalties[22],Paid[23],Waivers[24],Principal Balance[25],Interest Balance[26],Fees Balance[27],Penalties Balance[28],Balance[29],Overdue Days[30]

//----Cyml5QfIpMDUHGqj,,2110,,7/10/2024,6/11/2024,DAILY,Mauku Patrick Kobia,22611135,,2.5473E+11,UWEZO30,,,NEWAPPLICATION,NOTPAID,0,8000,8000,"2,400.00",0,0,0,0,0,0,0,0,0,0,0


    $uid = $row[2];
    $loan_code = $row[0];
    $client_id = 0;
    $account_number = $row[10];
    $enc_phone = hash('sha256', $account_number);
    $product_id = 3; /////////////---------------------------MANUAL
    $loan_type = 1;
    $loan_amount = $row[17];
    $interest_rate = floatval(str_replace(',', '', $row[19]));
    $fees =floatval(str_replace(',', '', $row[20]));
    $penalties = floatval(str_replace(',', '', $row[22]));


    $disbursed_amount = $loan_amount;
    $total_repayable_amount = $loan_amount + $interest_rate + $fees + $penalties;
    $total_repaid = floatval(str_replace(',', '', $row[23]));
    $loan_balance = $total_repayable_amount - $total_repaid;
    $given_date_u = $row[4]; //////////////-------------------NEEDS FORMATTING
      $given_date = convertToMySQLDate($given_date_u);
    $due_date_u = $row[5];   //////////////-------------------NEEDS FORMATTING
      $final_due_date = convertToMySQLDate($due_date_u);
      $next_due_date = $final_due_date;
    $period = datediff3($final_due_date, $given_date);
    $period_units = 1;
    $payment_frequency = $period_units;
    $total_addons = $total_repayable_amount - $loan_amount;
  //  $added_by = 0;
    $allocation = 'BRANCH';
   // $current_branch = 0;  /////-----FIX this
    $added_date = $fulldate;
    $loan_stage = 0;
    $application_mode = 'MANUAL';

    $payment_status = $row[15]; //////////--------CUSTOM   (NOTPAID, PAID, PARTIALLYPAID)
     if($payment_status == 'NOTPAID' || $payment_status == 'PARTIALLYPAID'){
         $disburse_state = 'DISBURSED';
         $disbursed = 1;
         $paid = 0;
         $status = 7;
     }
     else{
         $disburse_state = 1; /////----FIX THIS
         $disbursed = 1; ///////////----FIX THIS
         $paid = 1;     /////////////---FIX THIS
         $status = 5;   ////////////---FIX THIS
     }

     $id_number = $row[8];
    // echo $id_number.',';

    /* $cust = fetchonerow('o_customers',"national_id='$id_number'","uid, primary_mobile, current_agent, branch");
     $cuid = $cust['uid'];
     $cprimary_mobile = $cust['primary_mobile'];
       $enc_p = hash('sha256', $cprimary_mobile);
     $ccurrent_agent = $cust['current_agent'];
     $ccurrent_branch = $cust['branch']; */

    // echo "UPDATE o_loans set customer_id='$cuid', account_number='$cprimary_mobile', enc_phone='$enc_p', current_lo='$ccurrent_agent', current_branch='$ccurrent_branch' where uid='$uid'; <br/>";
    // updatedb('o_loans',"customer_id='$cuid', account_number='$cprimary_mobile', enc_phone='$enc_p', current_lo='$ccurrent_agent', current_branch='$ccurrent_branch'","uid='$uid'");

  /*  $jflds = array('loan_id', 'addon_id', 'addon_amount', 'added_date', 'status');
   $vals = array("$uid","16","$total_addons","$fulldate","1");
   echo addtodb('o_loan_addons', $jflds, $vals);
  $random_time = generateRandomTime(); */

  if($loan_balance < 1){
     echo updatedb('o_loans', "status=5", "uid='$uid'");
  }


   /* echo "INSERT IGNORE INTO o_loans (uid, loan_code,customer_id, account_number, enc_phone, product_id, loan_type,loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, period, period_units, payment_frequency, total_addons, given_date, next_due_date, final_due_date, added_by, allocation, current_branch, added_date, loan_stage, loan_flag, transaction_date,application_mode, disburse_state, disbursed, paid, status) VALUES ('$uid','$loan_code','$cuid','$account_number', '$enc_phone', '$product_id', '$loan_type','$loan_amount', '$disbursed_amount', '$total_repayable_amount', '$total_repaid', '$loan_balance', '$period', '$period_units', '$payment_frequency', '$total_addons', '$given_date', '$next_due_date', '$final_due_date', '$ccurrent_agent', '$allocation', '$ccurrent_branch', '$given_date $random_time', $loan_stage, 25,'$given_date $random_time', '$application_mode', '$disburse_state', '$disbursed', '$paid', '$status'); <br/>"; */
   //echo "(((($random_time))))";


}
//
//$customers = fetchtable('o_customers',"uid > 0","uid","asc","1000000","uid, national_id, primary_mobile");
//while($c = mysqli_fetch_array($customers)){
//    $uid = $c['uid'];
//    $national_id = $c['national_id'];
//    $primary_mobile = $c['primary_mobile'];
//
//    ////----Update
//    $upd = updatedb('o_loans',"customer_id='$uid'","")
//}

///-------------------

// Close the file
fclose($file);

function convertToMySQLDate($dateString) {
    // Parse the date string (day/month/year)
    $parts = explode('/', $dateString);

    // Check if we have the expected number of parts
    if (count($parts) !== 3) {
        return false;
    }

    // Extract day, month, and year
    $day = intval($parts[0]);
    $month = intval($parts[1]);
    $year = intval($parts[2]);

    // Validate date components
    if (!checkdate($month, $day, $year)) {
        return false;
    }

    // Format the date for MySQL (YYYY-MM-DD)
    return sprintf('%04d-%02d-%02d', $year, $month, $day);
}

function generateRandomTime() {
    // Define the start and end time in seconds since midnight
    $start = 6 * 3600;  // 6:00:00 AM in seconds
    $end = 21 * 3600;   // 9:00:00 PM in seconds

    // Generate a random time within the range
    $randomTimeInSeconds = rand($start, $end);

    // Convert seconds to hours, minutes, and seconds
    $hours = floor($randomTimeInSeconds / 3600);
    $minutes = floor(($randomTimeInSeconds % 3600) / 60);
    $seconds = $randomTimeInSeconds % 60;

    // Format as HH:MM:SS
    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
}
