<?php

error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);
date_default_timezone_set("Africa/Nairobi");

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

$hostname = '137.184.5.171'; // Your MySQL hostname. Usualy named as 'localhost', so you're NOT necessary to change this even this script has already online on the internet.
$dbname   ='newark_db'; // Your database name.
$username = 'admin';             // Your database username.
$password = 'RRetdre53553*gd';

$con1=mysqli_connect($hostname,$username,$password,$dbname);
if(mysqli_connect_errno())
{
    printf('Error Establishing a database connection');
    echo $dbname;
    exit();
}


$dbname2   ='old_system'; // Your database name.


$con2=mysqli_connect($hostname,$username,$password,$dbname2);
if(mysqli_connect_errno())
{
    printf('Error Establishing a database connection');
    echo $dbname2;
    exit();
}




////----Old DB
///
$products =  fetchTableData('o_loan_products', 'name', 'uid','newark_db');
$branches =  fetchTableData('o_branches', 'name', 'uid','newark_db');

$sql = "SELECT * FROM members order by uid asc LIMIT 0, 10000000000";
$result = mysqli_query($con2, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $full_name = $row['F_NAME'].' '.$row['L_NAME'].' ';
        $primary_mobile = $row['TEL_NUM'];
        $email_address = "";
        $physical_address = $row['ADDRESS'];
        $town = $row['TOWN'];
        $national_id = $row['ID_NUM'];
        $gender = 'U';
        $date_ob = $row['M_DOB'];
        $dob = convertDateFormat($date_ob);
        $added_by = 0;
        $agent = 0;
        $added_date = convertToMySQLDatetime($row['LAST_UPDATED']);
        $branch = $branches[$row['BRANCH']];
        $primary_product = 1;
        $loan_limit = $row['FINAL_APPROVED_AMT'];
        $acc = $row['ACCT_NO'];
        $status = 3;
        if($row['APPROVAL_STATUS'] == 'APPROVED'){
            $status = 1;
        }elseif ($row['APPROVAL_STATUS'] == 'DECLINED'){
            $status = 2;
        }
        elseif ($row['APPROVAL_STATUS'] == 'PENDING'){
            $status = 3;
        }

        $sec = '{"5": "'.$row['MAR_STATUS'].'", "51": "'.addslashes($row['KIN_NAME']).'", "52": "'.$row['KIN_TEL'].'", "53": "'.$row['KIN_REL'].'", "16": "'.addslashes($row['B_NAME']).'", "17": "'.addslashes($row['B_TYPE']).'", "18": "'.addslashes($row['B_LOCATION']).'", "44": "'.addslashes($row['TOWN']).'", "7": "'.addslashes($row['VILLAGE_ESTATE']).'", "13": "'.addslashes($row['LANDMARK']).'", "54": "'.$row['KIN_ID'].'"}';

       //echo $sec;

        $fds = array('full_name','primary_mobile','physical_address','town','national_id','gender','dob','added_by','current_agent','added_date','branch','primary_product','loan_limit','sec_data','device_id','status');
        $vals = array(addslashes("$full_name"),"$primary_mobile",addslashes("$physical_address"),addslashes("$town"),"$national_id","$gender","$dob","$added_by","$agent", "$added_date","$branch","$primary_product","$loan_limit","$sec","$acc","$status");
        $create = addtodb('o_customers',$fds,$vals, $con1);
        echo $create;

    }

    mysqli_free_result($result);
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($con2);
}


///-----NEW DB
///
///
///
///

















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

function fetchTableData($tableName, $field1, $field2, $dbname ) {
    // Replace these with your actual database connection details
   global $servername;
   global $username;
   global $password;

    // Create a database connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check the connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // SQL query to select data from the table
    $sql = "SELECT $field1, $field2 FROM $tableName";
    $result = $conn->query($sql);

    // Initialize an empty associative array
    $data = array();

    // Check if there are results
    if ($result->num_rows > 0) {
        // Fetch rows and convert them to an associative array
        while ($row = $result->fetch_assoc()) {
            $data[$row[$field1]] = $row[$field2];
        }
    } else {
        echo "No results found.";
    }

    // Close the database connection
    $conn->close();

    // Return the associative array
    return $data;
}







function addtodb($tb, $fds, $vals, $con)
{

    ////example              // $ffields=array('user_id','module_id','vie','ad','edi','del');
    // $vvals=array("$selectedval","$uuid","0","0","0","0");
    // $iinsertnew=addtodbsilent('user_permissions',$ffields,$vvals);

    /////////________Secure input
    // $vals = array_map('stripslashes', $vals);
    $fields=implode(',',$fds); //implode () returns string from the elements of an array
    $values=implode("','",$vals);
    $values="'$values'";

    $insertq="INSERT into $tb ($fields) VALUES ($values)";  //echo $insertq;

    //return;

    if(!mysqli_query($con,$insertq))
    {
        return mysqli_error($con);  // var_dump($e);
    }
    else
    {
      //  logupdate($tb, $insertq);
        return 1;
    }

}








