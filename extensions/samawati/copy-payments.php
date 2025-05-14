<?php
session_start();
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

error_reporting(0);
// Open the CSV file
$file = fopen('../../test/Payments_SAMAWATI_ASTRA_LIMITED_from_01092017_to_22102024_Confirmed.csv', 'r');

$locations = array(
    "Pipeline" => 439,
    "HQ" => 1,
    "Kiambu" => 436,
    "Thika" => 440,
    "Kinoo" => 438,
    "Ruai" => 441,
    "Ruiru" => 437,
    "Head Office" =>1
);

// Skip the header row if it exists
fgetcsv($file);
$order = 10;
// Loop through each row
while (($row = fgetcsv($file)) !== false) {
/*
    Loan Number0,Loan1,Due2,Customer3,Phone Number4,Employee Number5,Group6,Organization7,Branch8,Sales Rep9,Payment Status10,Principal11,Interest12,Fees13,Penalties,Balance,Days
1853,UWEZO30,15/07/2024,KITONDE DAVID KYALO,2.54707E+11,,Emerald,Nairobi,Pipeline,,NOTPAID,832.75,250.58,0,806.2,1889.53,99
1861,UWEZO30,15/07/2024,NZUKI MAGDALINE MUNANIE,2.54705E+11,,Emerald,Nairobi,Pipeline,,NOTPAID,166.67,50,0,161.28,377.95,99
*/
    $loan_id = $row[0];


}


// Close the file
fclose($file);

function convertToMySQLDate($dateString) {
    // Parse the date string (day/month/year)
    $parts = explode('/', $dateString);

    // Check if we have the expected number of parts
    if (count($parts) !== 3) {
        return false;
    }

    // Extract day, month, and year
    $day = intval($parts[0]);
    $month = intval($parts[1]);
    $year = intval($parts[2]);

    // Validate date components
    if (!checkdate($month, $day, $year)) {
        return false;
    }

    // Format the date for MySQL (YYYY-MM-DD)
    return sprintf('%04d-%02d-%02d', $year, $month, $day);
}
