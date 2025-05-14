<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$products =  table_to_obj('o_loan_products', 'uid > 0', 1000, 'uid','name');
$branches =  table_to_obj('o_branches', 'uid > 0', 1000,'name','uid');
$customers =  table_to_obj('o_customers', 'uid > 0',1000000000, 'national_id','uid');

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

$open2 = fopen($upload, "r");
$data2 = fgetcsv($open2, 10000000, ",");


while(($data2 = fgetcsv($open2, 100000000, ",")) !== FALSE) {

    $loan_id = trim($data2[5]);
    $branch = trim($data2[0]);
    $date_given = trim($data2[1]);
    $national_id = trim($data2[2]);
    $phone_number = trim($data2[4]);
    $loan_amount = $data2[6];
    $charges = $data2[7];
    $paid = $data2[8];
    $balance = $data2[9];
    $due_date = $data2[10];
    $pay_status = $data2[11];
    $BDO = $data2[12];




   echo "$loan_id, $branch, $date_given, $national_id, $phone_number, $loan_amount, $charges, $paid, $balance, $due_date, $BDO <br/>";




    $customer_id = $customers[$national_id];

    $product_id = 1;
    $total_payable = $loan_amount + $charges;
    $principal = $loan_amount;
    $total_addons = $charges;
    $total_repaid = $paid;
    $given_date = convertDateFormat($date_given);
    $final_due_date = convertDateFormat($due_date);
    $created_date = "$given_date 00:00:00";
    $transcode = "";

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

    $mass_loans = ",($loan_id, $customer_id,

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
        0,
        \"$created_date\",
        \"$transcode\",
        \"$given_date 00:00:00\",
        'MANUAL',
        \"$disburse_state\",
        $disbursed,
        $paid,
        $status)";

    ////----Try updating
    if((input_length($loan_id, 2)) == 1){
        ///----Update
       // $update = updatedb('o_loans',"total_repayable_amount='$total_payable', total_repaid='$total_repaid', loan_balance='$balance'","loan_code='$loan_id'");

    }




}


$flds = array('loan_code','customer_id',
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
    'transaction_code',
    'transaction_date',
    'application_mode',
    'disburse_state',
    'disbursed',
    'paid',
    'status');

//$mass = addtodbmulti('o_loans', $flds, ltrim($mass_loans, ","));
/////-----Try to create
echo "CREATE Mass: $mass_loans <br/>";

////-----Adds are updated in mass from a different page




