<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$stage_id = $_POST['stage_id'];
$product_id = $_POST['pid'];


$proceed = 0;


    $exists = checkrowexists("o_product_stages","product_id='$product_id' AND stage_id='$stage_id' AND status=1");
    if($exists == 1){
        ////----Update
        $update2 = updatedb('o_product_stages', "is_final_stage=0", "product_id='$product_id'");
        $update = updatedb('o_product_stages', "status=1, is_final_stage=1", "product_id='$product_id' AND stage_id='$stage_id'");
        if($update == 1)
        {
            $feedback = sucmes('Success setting final stage');
            $proceed = 1;
        }
        else
        {
            $feedback = errormes('Error setting final stage');
        }
    }
    else{
        ////----Add


            $feedback = errormes('Please add this stage to loan first');

    }


echo   json_encode("$feedback");
?>
