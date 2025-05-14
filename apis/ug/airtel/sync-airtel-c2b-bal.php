<?php 

// include_once("../../../php_functions/authenticator.php");
include_once("../../../php_functions/functions.php");
include_once("../../../configs/conn.inc");

if ($cc == 256) {
    include_once("../../../vendor/autoload.php");
    include_once("../../../php_functions/airtel-ug.php");
    include_once("../../../configs/airtel-ug.php");
}


$bal = doubleval(airtelUGC2BBalanceEnquiry()['payload']);

updateUgAirtelB2CBalance($bal);

?>