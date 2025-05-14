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


/////

$sql = "SELECT uid, branch FROM o_customers order by uid asc LIMIT 0, 10000000000";
$result = mysqli_query($con1, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {

           $cid = $row['uid'];
           $branch = $row['branch'];

         //  echo "$cid , $branch <br/>";
        $insertq="UPDATE o_loans SET current_branch='$branch' WHERE customer_id='$cid'"; //var_dump($insertq);

        if(!mysqli_query($con1, $insertq))
        {
            echo mysqli_error($con1);
        }
        else
        {

            echo 1;

        }

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








