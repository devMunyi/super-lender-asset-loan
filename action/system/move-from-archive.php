<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");
include_once ("../../configs/archive_conn.php");

$table = $_POST['table'];
$where = $_POST['where'];

if((input_length($table, 3)) == 0){
    die(errormes("Table name needed"));
}
if((input_length($where, 3)) == 0){
    die(errormes("Where is needed"));
}



// Connect to the first server
//$conn1 = mysqli_connect($hostname1, $username1, $password1, $dbname1);
$conn1 = mysqli_connect($hostname1,$username1,$password1,$dbname1);;

// Check if the connection was successful
if (!$conn1) {
    die("Connection failed: " . mysqli_connect_error());
}

// SQL query to select all records from the first table
$sql = "SELECT * FROM $table where $where";

$logFile = 'delete_backup.txt';
$log = fopen($logFile,"a");
fwrite($log, $sql."->".date('Y-m-d H:i:s')."[$table, $where]"."\n");
fclose($log);

// Execute the query on the first server connection
$result = mysqli_query($conn1, $sql);

// Connect to the second server
//$conn2 = mysqli_connect($hostname, $username, $password, $dbname);
$conn2 = mysqli_connect($hostname,$username,$password,$live_db);;

// Check if the connection was successful
if (!$conn2) {
    die("Connection failed: " . mysqli_connect_error());
}

// SQL query to insert records into the second table
$sql = "INSERT INTO $table VALUES ";

// Loop through the results from the first table and build the SQL query
while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
    $sql .= "(";
    foreach ($row as $value) {
        $sql .= "'" . mysqli_real_escape_string($conn2, $value) . "',";
    }
    $sql = rtrim($sql, ","); // Remove the last comma
    $sql .= "),";
}

$sql = rtrim($sql, ","); // Remove the last comma

//////////-----Before a screw-up happens, lets save the record in a text document
echo $sql;

$logFile = 'delete_backup.txt';
$log = fopen($logFile,"a");
fwrite($log, $sql."->".date('Y-m-d H:i:s')."[$table, $where]"."\n");
fclose($log);

// Execute the query on the second server connection
if (mysqli_query($conn2, $sql)) {
    echo "Records copied successfully";
    ///----Delete from former
} else {
    echo "Error copying records: " . mysqli_error($conn2);
}

// Close the database connections
mysqli_close($conn1);
mysqli_close($conn2);





/*
// Replace these variables with your database connection details
$hostname1 = 'localhost';
$username1 = 'root';
$password1 = '';
$database1 = 'tenova_local2';
$hostname2 = 'localhost';
$username2 = 'root';
$password2 = '';
$database2 = 'tenova_local';

// Connect to the first server
$conn1 = mysqli_connect($hostname1, $username1, $password1, $database1);

// Check if the connection was successful
if (!$conn1) {
    die("Connection failed: " . mysqli_connect_error());
}

// SQL query to select all records from the first table
$sql = "SELECT * FROM o_loans where uid = 1253";

// Execute the query on the first server connection
$result = mysqli_query($conn1, $sql);

// Connect to the second server
$conn2 = mysqli_connect($hostname2, $username2, $password2, $database2);

// Check if the connection was successful
if (!$conn2) {
    die("Connection failed: " . mysqli_connect_error());
}

// SQL query to insert records into the second table
$sql = "INSERT INTO o_loans VALUES ";

// Loop through the results from the first table and build the SQL query
while ($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
    $sql .= "(";
    foreach ($row as $value) {
        $sql .= "'" . mysqli_real_escape_string($conn2, $value) . "',";
    }
    $sql = rtrim($sql, ","); // Remove the last comma
    $sql .= "),";
}

$sql = rtrim($sql, ","); // Remove the last comma

echo $sql;

// Execute the query on the second server connection
if (mysqli_query($conn2, $sql)) {
    echo "Records copied successfully";
} else {
    echo "Error copying records: " . mysqli_error($conn2);
}

// Close the database connections
mysqli_close($conn1);
mysqli_close($conn2);

*/
