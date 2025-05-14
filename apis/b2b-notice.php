<?php
session_start();
include_once '../configs/20200902.php';

$company = $_GET['c'];
$req_id = $_GET['r'];
$data  = file_get_contents('php://input');

//$logFile = 'b2b-log.txt';
//$log = fopen($logFile,"a");
//fwrite($log, $data.date('Y-m-d H:i:s')."\n");
//fclose($log);


// The path to the file
$filename = 'b2b-log.txt';
$date_ = date('Y-m-d H:i:s');

// The text you want to write to the file
$textToWrite = "This is a new log entry. $data $date_\n";

// Try to open the file for writing
$fileHandle = fopen($filename, 'a'); // 'a' mode opens the file for writing and places the pointer at the end of the file

if ($fileHandle === false) {
    // If fopen failed, display an error message
    echo "Error: Unable to open file ($filename).";
} else {
    // Try to write to the file
    $result = fwrite($fileHandle, $textToWrite);

    if ($result === false) {
        // If fwrite failed, display an error message
        echo "Error: Unable to write to file ($filename).";
    } else {
        // Successfully written to the file
        echo "Success: Written to file ($filename).";
    }

    // Close the file
    fclose($fileHandle);
}

