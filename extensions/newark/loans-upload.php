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
    echo errormes("Error uploading file, please retry".$upload);
    exit();
}



$products =  table_to_obj('o_loan_products', 'uid > 0', 1000, 'uid','name');
$branches =  table_to_obj('o_branches', 'uid > 0', 1000,'name','uid');

$customers_array = array();
$customer_branches_array = array();
$customer_mobile_array = array();

$all_cust = fetchtable('o_customers',"uid > 0","uid","asc","1000000000","uid, national_id, branch, primary_mobile");
while($a = mysqli_fetch_array($all_cust)){
    $uid = $a['uid'];
    $national_id = $a['national_id'];
    $branch = $a['branch'];
    $primary_mobile = $a['primary_mobile'];

    $customers_array[$national_id] = $uid;
    $customer_branches_array[$national_id] = $branch;
    $customer_mobile_array[$national_id] = $primary_mobile;
    //array_push($all_customer_uids, $uid);

}


$open2 = fopen($upload, "r");
$data2 = fgetcsv($open2, 100000, ",");
$mass_loans = "";
$mass_addons = "";
while(($data2 = fgetcsv($open2, 1000000, ",")) !== FALSE) {

   $loan_date = $data2[0];
   $branch = $data2[1];
   $loan_id = $data2[2];
   $national_id = $data2[3];
    $principal = removeNumberSeparators($data2[4]);
   $reg = $data2[5];
   $processing = removeNumberSeparators($data2[6]);
   $interest = removeNumberSeparators($data2[7]);
   $penalties = removeNumberSeparators($data2[8]);
   $late_interest = removeNumberSeparators($data2[9]);
   $total_due = removeNumberSeparators($data2[10]);
   $total_paid = removeNumberSeparators($data2[17]);
   $balance = removeNumberSeparators($data2[18]);
   $pay_status = $data2[19];
   $staff = $data2[20];

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
    else{
        $disbursed = 1;
        $paid = 0;
        $status = 3;
        $disburse_state = 'DELIVERED';
    }


   ////-------------
    $customer_id = $customers_array[$national_id];
    $phone_number = $customer_mobile_array[$national_id];
    $total_payable = $principal + $reg + $processing + $interest + $penalties + $late_interest;
    $total_addons = $reg + $processing + $interest + $penalties + $late_interest;
    $given_date = convertDateFormat($loan_date);
    $final_due_date = dateadd($given_date, 0,0, 30);
    $created_date = "$given_date 00:00:00";
    $transcode = $loan_id;
    $branch_id = $branches[$branch];

    $all_penalties = $penalties + $late_interest;
    $all_interest = $interest;

    $product_id = 1;
    $period= 30;
    /// -----------

    $mass_loans = "('$loan_id', '$customer_id',

        '$phone_number',
        '$product_id',
        '2',
        $principal,
        $principal,
        $total_payable,
        $total_paid,
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
        \"$branch_id\",
        \"$created_date\",
        1,
        \"$transcode\",
        \"$given_date 00:00:00\",
        'MANUAL',
        \"$disburse_state\",
        $disbursed,
        $paid,
        $status),".$mass_loans;

 $update = updatedb('o_loans',"total_repayable_amount='$total_payable', total_repaid='$total_paid', loan_balance='$balance', loan_flag=1, current_branch='$branch_id'","loan_code='$loan_id'");

 ////------Update addons
    ///
    ///
    /// ----Reg

    if($reg > 1) {
        $iflds = array('loan_code', 'addon_id', 'addon_amount', 'added_date', 'status');
        $ivals = array("$loan_id", "2", "$reg", "$fulldate", "2");
        $upd = updatedb('o_loan_addonsx', "addon_amount='$reg', status=2", "loan_code='$loan_id' AND addon_id='2'");
        $mass_addons = "('$loan_id', '2', '$reg', '$fulldate', '2'),".$mass_addons;
    }
    /// ----Processing
    if($processing > 1) {
        $iflds = array('loan_code', 'addon_id', 'addon_amount', 'added_date', 'status');
        $ivals = array("$loan_id", "5", "$processing", "$fulldate", "2");
        $upd = updatedb('o_loan_addonsx', "addon_amount='$processing', status=2", "loan_code='$loan_id' AND addon_id='5'");
        $mass_addons = "('$loan_id', '5', '$processing', '$fulldate', '2'),".$mass_addons;
    }

    /// ----Interest
    if($interest > 1) {
        $iflds = array('loan_code', 'addon_id', 'addon_amount', 'added_date', 'status');
        $ivals = array("$loan_id", "1", "$all_interest", "$fulldate", "2");
        $upd = updatedb('o_loan_addonsx', "addon_amount='$all_interest', status=2", "loan_code='$loan_id' AND addon_id='1'");
        $mass_addons = "('$loan_id', '1', '$interest', '$fulldate', '2'),".$mass_addons;
    }

    /// ----Interest Penalties
    if($all_penalties > 0) {
        $iflds = array('loan_code', 'addon_id', 'addon_amount', 'added_date', 'status');
        $ivals = array("$loan_id", "12", "$all_penalties", "$fulldate", "2");
        $upd = updatedb('o_loan_addonsx', "addon_amount='$all_penalties', status=2", "loan_code='$loan_id' AND addon_id='12'");
        $mass_addons = "('$loan_id', '12', '$interest', '$fulldate', '2'),".$mass_addons;
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

$mass = addtodbmulti('o_loans', $flds, rtrim($mass_loans, ","));
/////-----Try to create
$iflds = array('loan_code', 'addon_id', 'addon_amount', 'added_date', 'status');
$mass2 = addtodbmulti('o_loan_addonsx', $iflds, rtrim($mass_addons, ","));
//echo $mass_loans;

echo "CREATE Mass: $mass <br/>";
echo "CREATE Adds: $mass2 <br/>";

////-----Adds are updated in mass from a different page
$all_loans = table_to_array('o_loan_addonsx',"status=2","10000000","loan_code","uid","asc");
$all_loan_codes = "'".implode("','", $all_loans)."'";
$all_loan_ids = table_to_obj('o_loans',"uid > 0 AND loan_code in ($all_loan_codes)","1000000000","loan_code","uid");

//echo "$all_loan_codes";

////----Move addons from temp table
$mass_addons2 = "";
$addons = fetchtable('o_loan_addonsx',"status=2","uid","asc","10000000","*");
while($ad = mysqli_fetch_array($addons)){
    $auid = $ad['uid'];
    $loan_code = $ad['loan_code'];
    $addon_id = $ad['addon_id'];
    $addon_amount = $ad['addon_amount'];

    $uid_loan = $all_loan_ids[$loan_code];

    if($uid_loan > 0) {
        $ivals = array("$uid_loan", "$addon_id", "$addon_amount", "$fulldate", "1");
        $upd = updatedb('o_loan_addons', "addon_amount='$addon_amount'", "loan_id='$uid_loan' AND addon_id='$addon_id' AND status=1");
       // echo $upd;

        $mass_addons2 = "('$uid_loan', '$addon_id', '$addon_amount', '$fulldate', '1'),".$mass_addons2;
    }


}
echo "Addons update $upd<br/>";


/////-----Create addons mass
echo "<br/>___";
//echo "(".$mass_addons2.")";
echo "<br/>___";
$jflds = array('loan_id', 'addon_id', 'addon_amount', 'added_date', 'status');
$mass3 = addtodbmulti('o_loan_addons', $jflds, rtrim($mass_addons2, ","));

echo "CReate Mass3: $mass3";

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