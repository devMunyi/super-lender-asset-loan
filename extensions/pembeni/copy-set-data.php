<?php
session_start();
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

$offset = $_GET['offset'];
$rpp = $_GET['rpp'];
if(isset($offset) && isset($rpp)){

}
else{
    die("offset and rpp must be set");
}

ini_set('display_errors', 0); ini_set('display_startup_errors', 0); error_reporting(E_ALL);


// Open the CSV file
//$file = fopen('../../test/sec_data.csv', 'r');

$locations = array(
    "DAGORETTI" => 6,
    "HEAD OFFICE" => 1,
    "KANGARI" => 11,
    "KIMENDE" => 12,
    "LIMURU" => 5,
    "RUAI" => 7,
    "RUIRU" => 3,
    "THIKA" => 2,
    "WANGIGE" => 4
);

$sec_array = array();

$select_ = fetchtable('customers',"id > 0","id","asc","$offset,$rpp");
while($s = mysqli_fetch_array($select_)){
    $id = $s['id'];
     $postal_address = $s['postal_address']; //
    $sec_data[44] = $town = $s['town'];
     $residential = $s['residential']; //
    $sec_data[5] =  $s['marital_status'];
    $sec_data[44] =  $s['town'];
    $sec_data[54] = $s['county']; //

    $referee_name = $s['referee_name']; //
    $referee_phone = $s['referee_phone']; //

    $sec_data[16] = addslashes($s['business_name']);
    $sec_data[17] = addslashes($s['business_type']);
    $sec_data[55] = $s['daily_sales']; //
    $sec_data[13] = $s['landmark'];
    $sec_data[56] = $s['year_est']; //
    $sec_data[57] = $s['business_reg_status']; //
    $sec_data[18] = $s['business_location'];

    $sec_data[58] = $s['nok_name']; //
    $sec_data[59] = $s['nok_relationship']; //
    $sec_data[60] = $s['nok_phone']; //

    ////--Guarantor
    $gmarital_status = $s['gmarital_status'];
    $gfirstname = $s['gfirstname'];
    $gphone_number = $s['gphone_number'];
    $gmiddlename = $s['gmiddlename'];
    $g_lastname = $s['glastname'];
    $grelationship = $s['grelationship'];
    $goccupation = $s['goccupation'];

    //--
    /*
    if(input_length($referee_name, 2) == 1) {
        $rfields = array('customer_id', 'added_date', "referee_name", "mobile_no", "relationship", "status");
        $rvals = array("$id", "$fulldate", "$referee_name", "$referee_phone", "0", "1");
        $create = addtodb('o_customer_referees', $rfields, $rvals);
        echo $create;
        echo "<br/>";

    }
    else{
        echo "Skipped";
        echo "<br/>";
    }
    */

  /*  $gfullname = trim("$gfirstname $gmiddlename $g_lastname");
    if(input_length($gfullname, 5) == 1) {
        $gfields = array('guarantor_name', 'customer_id', "mobile_no", "physical_address", "added_date", "relationship", "status");
        $gvals = array("$gfirstname $gmiddlename $g_lastname", "$id", "$gphone_number", "$goccupation [$grelationship]", "$fulldate", "10", "1");
        $create = addtodb('o_customer_guarantors', $gfields, $gvals);
        echo $create;
        echo "<br/>";

    }
    else{
        echo "Skipped";
        echo "<br/>";
    }  */

    ////----Images
    $customer_passport = $s['customer_passport'];
    $customer_id_front = $s['customer_id_front'];
    $customer_id_back = $s['customer_id_back'];
    $business_photo = $s['business_photo'];
    $customer_sign = $s['customer_sign'];
    $registered_by = $s['registered_by'];


    /////--------ID Front
   /* if(input_length($customer_id_front, 7) == 1) {
        $fds = array('title', 'description', 'category', 'added_by', 'added_date', 'tbl', 'rec', 'stored_address', 'status');
        $vals = array("ID Front", "Imported", "2", "2", "$fulldate", "o_customers", "$id", "$customer_id_front", "1");
        $create = addtodb('o_documents', $fds, $vals);
        echo "FRONT $create, $id <br/>";
    }
    ///------ID Back
    if(input_length($customer_id_back, 7) == 1) {
        $fds1 = array('title', 'description', 'category', 'added_by', 'added_date', 'tbl', 'rec', 'stored_address', 'status');
        $vals1 = array("ID Back", "Imported", "3", "2", "$fulldate", "o_customers", "$id", "$customer_id_back", "1");
        $create1 = addtodb('o_documents', $fds1, $vals1);
        echo "BACK $create1, $id <br/>";
    }
    ///------Business Photo
    if(input_length($business_photo, 22) == 1) {
        $fds2 = array('title', 'description', 'category', 'added_by', 'added_date', 'tbl', 'rec', 'stored_address', 'status');
        $vals2 = array("Business Photo", "Imported", "4", "2", "$fulldate", "o_customers", "$id", "$business_photo", "1");
        $create2 = addtodb('o_documents', $fds2, $vals2);
        echo "Busi $create2, $id <br/>";
    }

   */
    if(input_length($customer_passport, 20) == 1) {
        $fds2 = array('title', 'description', 'category', 'added_by', 'added_date', 'tbl', 'rec', 'stored_address', 'status');
        $vals2 = array("Passport Photo", "Imported", "1", "2", "$fulldate", "o_customers", "$id", "$customer_passport", "1");
        $create2 = addtodb('o_documents', $fds2, $vals2);
        echo "Passport $create2, $id <br/>";
    }
    /*
    ///------Customer Sign
    if(input_length($customer_sign, 10) == 1) {
        $fds3 = array('title', 'description', 'category', 'added_by', 'added_date', 'tbl', 'rec', 'stored_address', 'status');
        $vals3 = array("Customer Sign", "Imported", "7", "2", "$fulldate", "o_customers", "$id", "$customer_sign", "1");
        $create3 = addtodb('o_documents', $fds3, $vals3);
        echo "Sign $create3 <br/>";
    }
      */
   /*

    $item1_customer = addslashes($s['item1_customer']);
    $item1_description_customer = addslashes($s['item1_description_customer']);
    $item1_value_customer = addslashes($s['item1_value_customer']);
    $item1_serial_customer = addslashes($s['item1_serial_customer']);

    if(input_length($item1_customer, 1) == 1) {
        $fds1 = array('customer_id', 'category', 'title', 'description', 'money_value', 'doc_reference_no','added_date','added_by','loan_id', 'status');
        $vals1 = array("$id", "3", "$item1_customer", "$item1_description_customer", "$item1_value_customer", "$item1_serial_customer","$fulldate","2","0", "1");
        $create1 = addtodb('o_collateral', $fds1, $vals1);
        echo "Asset 1 $create1 <br/>";
    }

    $item2_customer = addslashes($s['item2_customer']);
    $item2_description_customer = addslashes($s['item2_description_customer']);
    $item2_value_customer = addslashes($s['item2_value_customer']);
    $item2_serial_customer = addslashes($s['item2_serial_customer']);

    if(input_length($item2_customer, 1) == 1) {
        $fds2 = array('customer_id', 'category', 'title', 'description', 'money_value', 'doc_reference_no','added_date','added_by','loan_id', 'status');
        $vals2 = array("$id", "3", "$item2_customer", "$item2_description_customer [Asset 2]", "$item2_value_customer", "$item2_serial_customer","$fulldate","2","0", "1");
        $create2 = addtodb('o_collateral', $fds2, $vals2);
        echo "Asset 2 $create2 <br/>";
    }



    $item3_customer = addslashes($s['item3_customer']);
    $item3_description_customer = addslashes($s['item3_description_customer']);
    $item3_value_customer = addslashes($s['item3_value_customer']);
    $item3_serial_customer = addslashes($s['item3_serial_customer']);

    if(input_length($item3_customer, 1) == 1) {
        $fds3 = array('customer_id', 'category', 'title', 'description', 'money_value', 'doc_reference_no','added_date','added_by','loan_id', 'status');
        $vals3 = array("$id", "3", "$item3_customer", "$item3_description_customer [Asset 3]", "$item3_value_customer", "$item3_serial_customer","$fulldate","2","0", "1");
        $create3 = addtodb('o_collateral', $fds3, $vals3);
        echo "Asset 3 $create3 <br/>";
    }

    $item4_customer = addslashes($s['item4_customer']);
    $item4_description_customer = addslashes($s['item4_description_customer']);
    $item4_value_customer = addslashes($s['item4_value_customer']);
    $item4_serial_customer = addslashes($s['item4_serial_customer']);

    if(input_length($item4_customer, 1) == 1) {
        $fds4 = array('customer_id', 'category', 'title', 'description', 'money_value', 'doc_reference_no','added_date','added_by','loan_id', 'status');
        $vals4 = array("$id", "3", "$item4_customer", "$item4_description_customer [Asset 4]", "$item4_value_customer", "$item4_serial_customer","$fulldate","2","0", "1");
        $create4 = addtodb('o_collateral', $fds4, $vals4);
        echo "Asset 4 $create4 <br/>";
    }

    $item5_customer = addslashes($s['item5_customer']);
    $item5_description_customer = addslashes($s['item5_description_customer']);
    $item5_value_customer = addslashes($s['item5_value_customer']);
    $item5_serial_customer = addslashes($s['item5_serial_customer']);

    if(input_length($item5_customer, 1) == 1) {
        $fds5 = array('customer_id', 'category', 'title', 'description', 'money_value', 'doc_reference_no','added_date','added_by','loan_id', 'status');
        $vals5 = array("$id", "3", "$item5_customer", "$item5_description_customer [Asset 5]", "$item5_value_customer", "$item5_serial_customer","$fulldate","2","0", "1");
        $create5 = addtodb('o_collateral', $fds5, $vals5);
        echo "Asset 5 $create5 <br/>";
    }

    $item6_customer = addslashes($s['item6_customer']);
    $item6_description_customer = addslashes($s['item6_description_customer']);
    $item6_value_customer = addslashes($s['item6_value_customer']);
    $item6_serial_customer = addslashes($s['item6_serial_customer']);

    if(input_length($item6_customer, 1) == 1) {
        $fds6 = array('customer_id', 'category', 'title', 'description', 'money_value', 'doc_reference_no','added_date','added_by','loan_id', 'status');
        $vals6 = array("$id", "3", "$item6_customer", "$item6_description_customer [Asset 6]", "$item6_value_customer", "$item6_serial_customer","$fulldate","2","0", "1");
        $create6 = addtodb('o_collateral', $fds6, $vals6);
        echo "Asset 6 $create6 <br/>";
    }
     */

    $sec_data[7] = $s['house_name'];
    $sec_data[8] = $s['house_number'];
    $pin_location = addslashes($s['pin_location']); //
    $sec_data[61] = $s['pin_location2']; //

    $loan_formpg1 = $s['loan_formpg1'];
    $loan_formpg2 = $s['loan_formpg2'];
    $loan_formpg3 = $s['loan_formpg3'];
    $logbook = $s['logbook'];
    $repossession_form = $s['repossession_form'];
    $approved_by = $s['approved_by'];

    $sec_ = sanitizeAndEscape(json_encode(escape_quotes($sec_data)), $con);





  //  echo "UPDATE o_customers set sec_data=\"$sec_\", geolocation=\"$pin_location\" WHERE uid='$id';";
 /*   $upd = updatedb('o_customers',"sec_data=\"$sec_\", geolocation=\"$pin_location\"","uid='$id'");
    if($upd == 1){
        echo sucmes("Account updated successfully");
    }
    else{
        echo "Error $upd";
    }   */

   // echo "<br/>";
}


function escape_quotes($array) {
    foreach ($array as $key => $value) {
        // Check if the value is a string
        if (is_string($value)) {
            // Add backslashes to single and double quotes
            $array[$key] = (trim($value));
        }
    }
    return $array;
}
