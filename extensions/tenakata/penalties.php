<?php
//ini_set('memory_limit','256M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// files includes
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

// Read the JSON file content
$paymentsJson = file_get_contents('../../airtel-uploads/repayments_schedule.json');


// Convert JSON data to a PHP array
$paymentsData = json_decode($paymentsJson, true);


//var_dump($loanData);


if (isset($paymentsData['data']) && is_array($paymentsData['data'])) {
    foreach ($paymentsData['data'] as $payData) {

        // set necessary variables
        $loan_id = intval($payData['loan']);
        $penalties = $payData['penalties'];
        $ldate = $payData['date'];
        if($penalties > 0){
            echo "$loan_id $penalties $ldate<br/>";
        }
    }
}


?>