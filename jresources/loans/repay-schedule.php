<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();

$loan_id = $_POST['loan_id'] ?? 0;

if ($loan_id == 0) {
    echo "<div class='row'><span class='font-18 font-italic text-black text-mute'>Loan id was not parsed!</span></div>";
    exit();
} else {
    $loan_id = decurl($loan_id);
}



echo repay_schedule($loan_id);

// include close connection
include_once("../../configs/close_connection.inc");
