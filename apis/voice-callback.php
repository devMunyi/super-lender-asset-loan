<?php
session_start();
$data  = file_get_contents('php://input');


$logFile = 'call-log.txt';
$log = fopen($logFile,"a");
fwrite($log, $data.''."Call->".date('Y-m-d H:i:s')."\n");
fclose($log);




// Read the file content
$fileContent = file_get_contents($logFile);

// Check if the file was read successfully
if ($fileContent === false) {
    echo "Failed to read the file.";
} else {
    // Display the file content
    echo nl2br($fileContent);
}