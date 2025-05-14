<?php
session_start();
$_SESSION['db_name'] = 'finabora_new_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");
require_once('../php_functions/AfricasTalkingGateway.php');
echo "jdjd";

$all_customers = fetchtable('o_customers',"uid > 0","uid","asc","100000","uid, primary_mobile");
while($a = mysqli_fetch_array($all_customers)){
    $uid = $a['uid'];
    $primary_ph = $a['primary_mobile'];
    $message = "NOTICE is hereby given that DAVID MWAURA KIRIUNGI is no longer in the employment of FINABORA LTD with effect from 1/9/2022. He is not authorized to represent the company in any matters whatsoever, and any person dealing with him shall do so at his/her own risk and responsibility. Contact us on 0720424779";
    echo queue_message($message, $primary_ph);
}


?>