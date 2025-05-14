<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);
session_start();
$_SESSION['db_name'] = 'stawika_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

////------------ACCOUNT SUMMARY


$session_code = $data['session_id'];
$device_id = $data['device_id'];


if((input_length($device_id, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid device Id"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
if((input_length($session_code, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid Session Code"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}


$session_d = fetchonerow('o_customer_sessions',"device_id='$device_id' AND session_code='$session_code' AND ending_date >= '$fulldate' AND status=1","uid, customer_id");
if($session_d['uid'] < 1){
    $result_ = 0;
    $details_ = '"Session Invalid"';
    $result_code = 107;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    die();
    exit();
}
else{
    $customer_id = $session_d['customer_id'];
    ////----You have customer ID
    $mypr = table_to_array('o_customer_products',"customer_id='$customer_id' AND status=1","10","product_id");

    $cust_det = fetchonerow('o_customers',"uid=".$session_d['customer_id']."","primary_product");
    $primary_product = $cust_det['primary_product'];

    array_push($mypr, $primary_product);
    array_push($mypr, 4);

    $myprod_st = implode(',', $mypr);


    $products_array = array();
    $products = fetchtable('o_loan_products',"status=1 AND uid in($myprod_st)","name","asc","10","uid, name, description, period, period_units, min_amount, max_amount, pay_frequency");
    while($p = mysqli_fetch_array($products)){
        $parray = array();
        $parray['uid'] = $p['uid'];
        $parray['name'] = $p['name'];
        $parray['description'] = $p['description'];
        $parray['period'] = $p['period'];
        $parray['period_units'] = $p['period_units'];
        $parray['min_amount'] = $p['min_amount'];
        $parray['max_amount'] = $p['max_amount'];
        $parray['pay_frequency'] = $p['max_amount'];


        $total_period = $p['period'] * $p['period_units'];
        $parray['due_date'] = dateadd($date, 0,0, $total_period);
        $parray['period_days'] = $total_period;

        array_push($products_array, $parray);

    }



    $result_ = 1;
    $details_ = json_encode($products_array);
    $result_code = 111;

    echo json_encode("{\"result_\":$result_,\"details_\":$details_,  \"product_\":$primary_product,  \"result_code\":$result_code}");

}



