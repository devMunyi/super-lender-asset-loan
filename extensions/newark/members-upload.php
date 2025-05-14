<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

$products =  table_to_obj('o_loan_products', 'uid > 0', 1000, 'uid','name');
$branches =  table_to_obj('o_branches', 'uid > 0', 1000,'name','uid');

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
$data2 = fgetcsv($open2, 100000, ",");

$mass_customers = "";

while(($data2 = fgetcsv($open2, 1000000, ",")) !== FALSE) {

    $branch = $data2[0];
    $djoined = $data2[1];
    $name = trim(addslashes($data2[2]));
    $acc = $data2[3];
    $idno = trim($data2[4]);
    $phone = trim($data2[5]);
    $limit = removeNumberSeparators($data2[6]);

    $branch_id = $branches[$branch];
    $join_date = convertDateFormat($djoined);



    $mass_customers="('$name','$phone','$idno','$added_by','0','$join_date 00:00:00','$branch_id','1','$limit','$acc','1'),".$mass_customers;
        ///----Update
      //  $update = updatedb('o_loans',"total_repayable_amount='$total_payable', total_repaid='$total_repaid', loan_balance='$balance', loan_flag=1, current_branch='$branch_'","loan_code='$loan_id'");





}


$fds = array('full_name','primary_mobile','national_id','added_by','current_agent','added_date','branch','primary_product','loan_limit','device_id','status');


$mass = addtodbmulti('o_customers', $fds, rtrim($mass_customers, ","));
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