<?php
session_start();
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

ini_set('display_errors', 0); ini_set('display_startup_errors', 0);


// Open the CSV file
//$file = fopen('../test/sal-new-loans.sql', 'r');


// Read the SQL file
$sqlFile = '../test/sal-new-loans.sql';
$sql = file_get_contents($sqlFile);

if ($sql === false) {
    die("Error reading the SQL file.");
}

// Execute the SQL file
if (mysqli_multi_query($con, $sql)) {
    do {
        // Clear the result set
        if ($result = mysqli_store_result($con)) {
            mysqli_free_result($result);
        }
    } while (mysqli_more_results($con) && mysqli_next_result($con));

    echo "SQL file executed successfully.";
} else {
    echo "Error executing SQL file: " . mysqli_error($con);
}

// Close the connection

// Close the file
fclose($file);

