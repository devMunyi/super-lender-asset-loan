<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
$userd = session_details();
$added_by = $userd['uid'];
$file_name = $_FILES['file_']['name'];
$file_size = $_FILES['file_']['size'];
$file_tmp = $_FILES['file_']['tmp_name'];
$upload_location = '';

$handle = fopen($file_tmp, "r");
$i = 0;


$upload = upload_file($file_name, $file_tmp, $upload_location);
if($upload === 0){
    echo errormes("Error uploading file, please retry");
    exit();
}


$open = fopen($upload, "r");
$data= fgetcsv($open, 100000, ",");
$all_loans = array();
$all_customers = array();
while(($data = fgetcsv($open, 1000000, ",")) !== FALSE) {
    $loan_id = $data[5];
    $customer_national_id = trim($data[3]);
    array_push($all_loans, $loan_id); /////Select all the loan codes from the document
    array_push($all_customers, $customer_national_id);

}
$all_loans_list = implode("','", $all_loans);  ///////Add the loan codes in a single array

$products =  table_to_obj('o_loan_products', 'uid > 0', 1000, 'uid','name');
$branches =  table_to_obj('o_branches', 'uid > 0', 1000,'name','uid');
$customers =  array();
$customer_branches =  array();




$all_customer_list = implode(',', $all_customers);
$all_customer_uids = array();
/////---------Customer details
$all_cust = fetchtable('o_customers',"uid > 0 AND national_id in ($all_customer_list)","uid","asc","1000000000","uid, national_id, branch");
while($a = mysqli_fetch_array($all_cust)){
    $uid = $a['uid'];
    $national_id = $a['national_id'];
    $branch = $a['branch'];
    $customers[$national_id] = $uid;
    $customer_branches[$national_id] = $branch;
    array_push($all_customer_uids, $uid);

}
////------Customer total loans
$all_customer_uids_list = implode(',', $all_customer_uids);
$total_loans_per_customer = array();
$loans = fetchtable('o_loans',"customer_id in ($all_customer_uids_list) AND disbursed=1 AND status!=0","uid","asc","1000000000","uid, customer_id");
while($l = mysqli_fetch_array($loans)){

    $luid = $l['uid'];
    $customer_id = $l['customer_id'];

    $total_loans_per_customer = obj_add($total_loans_per_customer, $customer_id, 1);

}



$open2 = fopen($upload, "r");
$data2 = fgetcsv($open2, 100000, ",");

$mass_loans = "";

while(($data2 = fgetcsv($open2, 1000000, ",")) !== FALSE) {

    $loan_id = $data2[5];
    if((input_length($loan_id, 2)) == 1){
    $branch = trim($data2[0]);
    $date_given = trim($data2[1]);
    $national_id = trim($data2[3]);
    $phone_number = trim($data2[4]);
    $loan_amount = removeNumberSeparators($data2[6]);
    $charges = removeNumberSeparators($data2[7]);
    $paid = removeNumberSeparators($data2[8]);
    $balance = removeNumberSeparators($data2[9]);
    $due_date = $data2[10];
    $pay_status = $data2[11];
    $BDO = $data2[12];




   //echo "$loan_id, $branch, $date_given, $national_id, $phone_number, $loan_amount, $charges, $paid, $balance, $due_date, $BDO <br/>";




    $customer_id = $customers[$national_id];
    $branch_ = $customer_branches[$national_id];

    $product_id = 1;

    $principal = removeNumberSeparators($loan_amount);
    $other_charges = removeNumberSeparators($charges);
    $total_repaid = removeNumberSeparators($paid);
    $given_date = convertDateFormat($date_given);
    $final_due_date = convertDateFormat($due_date);
    $created_date = "$given_date 00:00:00";
    $transcode = "";

    $total_loans_taken = false_zero($total_loans_per_customer[$customer_id]);

    $days_passed = datediff3($given_date, $date);




      /////-----Interest -
    $interest = ($balance - $principal) + ($total_repaid - $other_charges);
    $total_addons = $interest + $other_charges;
    $total_payable = $principal + $total_addons;


        /// ----processing
        $processing = 0.06*$principal;

        /// ----Registration -
        if($total_loans_taken == 0){
            $reg_fees = 500;
        }
        else{
            $reg_fees = 0;
        }
        /// ----Penalties
        $penalties = $other_charges - $processing - $reg_fees;

     echo "Loan: $loan_id Pr: $principal, Int: $interest, Processing: $processing, Reg: $reg_fees, Pen: $penalties, Total Addons: $total_addons, Rep: $total_payable, Paid: $total_repaid, Balance: $balance <br/>";

    $period = datediff3($given_date, $final_due_date);

    if($pay_status == 'CLOSED'){
        $disbursed = 1;
        $paid = 1;
        $status = 5;
        $disburse_state = 'DELIVERED';
    }
    elseif ($pay_status == 'DEFAULTED'){
        $disbursed = 1;
        $paid = 0;
        $status = 7;
        $disburse_state = 'DELIVERED';
    }
    elseif ($pay_status == 'PENDING DISBURSEMENT'){
        $disbursed = 0;
        $paid = 0;
        $status = 2;
        $disburse_state = 'NONE';
    }
    elseif ($pay_status == 'ACTIVE'){
        $disbursed = 1;
        $paid = 0;
        $status = 3;
        $disburse_state = 'DELIVERED';
    }



    $mass_loans="('$loan_id', '$customer_id',

        $phone_number,
        $product_id,
        '2',
        $principal,
        $principal,
        $total_payable,
        $total_repaid,
        $balance,
        $period,
        1,
        1,
        $total_addons,
        \"$given_date\",
        \"$final_due_date\",
        \"$final_due_date\",
        0,
        0,
        0,
        0,
        0,
        '$branch_',
        \"$created_date\",
        1,
        \"$transcode\",
        \"$given_date 00:00:00\",
        'MANUAL',
        \"$disburse_state\",
        $disbursed,
        $paid,
        $status),".$mass_loans;

    ////----Try updating

        ///----Update
       // $update = updatedb('o_loans',"total_repayable_amount='$total_payable', total_repaid='$total_repaid', loan_balance='$balance', loan_flag=1, current_branch='$branch_'","loan_code='$loan_id'");

    }




}


$flds = array('loan_code',
    'customer_id',
    'account_number',
    'product_id',
    'loan_type',
    'loan_amount',
    'disbursed_amount',
    'total_repayable_amount',
    'total_repaid',
    'loan_balance',
    'period',
    'period_units',
    'payment_frequency',
    'total_addons',
    'given_date',
    'next_due_date',
    'final_due_date',
    'added_by',
    'current_agent',
    'current_lo',
    'current_co',
    'allocation',
    'current_branch',
    'added_date',
    'loan_flag',
    'transaction_code',
    'transaction_date',
    'application_mode',
    'disburse_state',
    'disbursed',
    'paid',
    'status');

//$mass = addtodbmulti('o_loans', $flds, rtrim($mass_loans, ","));
/////-----Try to create
///
//echo $mass_loans;

echo "CREATE Mass: $mass <br/>";

////-----Adds are updated in mass from a different page




function convertDateFormat($inputDate) {
    // Convert the input date to a Unix timestamp
    $timestamp = strtotime($inputDate);

    // Check if the conversion was successful
    if ($timestamp === false) {
        return '0000-00-00'; // Return false in case of an invalid date
    }

    // Convert the Unix timestamp to the desired format
    $outputDate = date('Y-m-d', $timestamp);

    return $outputDate;
}
function convertToMySQLDatetime($inputDate) {
    // Convert the input date to a DateTime object
    $dateTime = DateTime::createFromFormat('d-M-y h.i.s.u A', $inputDate);

    // Check if the conversion was successful
    if ($dateTime === false) {
        return false; // Return false in case of an invalid date
    }

    // Convert the DateTime object to MySQL datetime format
    $outputDate = $dateTime->format('Y-m-d H:i:s');

    return $outputDate;
}

function removeNumberSeparators($input) {
    // Use regular expressions to remove non-numeric characters
    $cleaned_number = preg_replace('/[^\d.]+/', '', $input);
    // If you want to preserve the decimal point, you can use this line instead:
    // $cleaned_number = preg_replace('/[^\d.]+/', '', $input);

    return $cleaned_number;
}