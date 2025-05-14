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



$products =  table_to_obj('o_loan_products', 'uid > 0', 1000, 'uid','name');
$branches =  table_to_obj('o_branches', 'uid > 0', 1000,'name','uid');

$customers_array = array();
$customer_branches_array = array();
$customer_mobile_array = array();

///------Pick all loans, customers
$open1 = fopen($upload, 'r');
$data1  = fgetcsv($open1, 100000000, ",");
$all_nationals = array();
$all_loan_codes = array();
while(($data1 = fgetcsv($open1, 100000000, ",")) !== FALSE) {
    $nat_id = $data1[3];
    $lcode = $data1[5];

    array_push($all_nationals, $nat_id);
    array_push($all_loan_codes, $lcode);

}
///------End of pick all loans, customers

$nationals = implode("','", $all_nationals);
$loans = implode("','", $all_loan_codes);

$customers_list = table_to_obj('o_customers',"national_id in ('$nationals')","1000000","national_id","uid");
$loans_list = table_to_obj('o_loans',"loan_code in ('$loans')","1000000","loan_code","uid");

$open2 = fopen($upload, "r");
$data2 = fgetcsv($open2, 100000, ",");
$mass_payments = "";

$to_recalc = array();

while(($data2 = fgetcsv($open2, 1000000, ",")) !== FALSE) {

    $branch = trim($data2[0]);
    $p_date = $data2[1];
    $id_number = $data2[3];
    $phone_number = $data2[4];
    $loan_code = $data2[5];
    $amount = removeNumberSeparators($data2[6]);
    $transid = $data2[7];
    $balance = removeNumberSeparators($data2[8]);

    $branch_id = $branches[$branch];


    $customer_id = $customers_list[$id_number];
    $loan_id = $loans_list[$loan_code];

    if($balance < 5 && $loan_id > 0){
        array_push($to_recalc, $loan_id);
    }

    $pay_date = convertDateFormat($p_date);

    $mass_payments =  "('$customer_id','$branch_id',
'3',
 '1',
    '$phone_number',
    '$amount',
    '$transid',
    '$loan_id',
    '$loan_code',
    '$balance',
    '$pay_date',
    '$pay_date 00:00:00',
    '1',
    'MANUAL',
    'Copied from other system',
    '1'),".$mass_payments;

    ////------Update addons

}


$flds = array('customer_id','branch_id',
'payment_method',
 'payment_category',
    'mobile_number',
    'amount',
    'transaction_code',
    'loan_id',
    'loan_code',
    'loan_balance',
    'payment_date',
    'recorded_date',
    'added_by',
    'record_method',
    'comments',
    'status');

//$mass = addtodbmulti('o_incoming_payments', $flds, rtrim($mass_payments, ","));
//echo $mass_payments;

echo "CREATE Mass: $mass <br/>";

for($i = 0; $i <= sizeof($to_recalc); ++$i){
    $loan_idd = $to_recalc[$i];
    $upd = updatedb('o_loans',"status=5","uid='$loan_idd' AND status!=5");
    echo "Recalc $loan_idd, $upd";
}


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