<?php
session_start();
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);

$_SESSION['db_name'] = 'stawika_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");





        $keen_name = trim($data['keen_name']);
        $keen_email = trim($data['keen_email']);
        $keen_phone = make_phone_valid(trim($data['keen_phone']));
        $keen_relationship = trim($data['keen_relationship']);
        $ref1_name = trim($data['ref1_name']);
        $ref1_email = trim($data['ref1_email']);
        $ref1_phone = make_phone_valid(trim($data['ref1_phone']));
        $ref1_relationship = trim($data['ref1_relationship']);
        $ref2_name = trim($data['ref2_name']);
        $ref2_email = trim($data['ref2_email']);
        $ref2_phone = make_phone_valid(trim($data['ref2_phone']));
        $ref2_relationship = trim($data['ref2_relationship']);


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

      //  $sess = fetchonerow('o_customer_sessions',"uid='1'","uid");

        $session_d = fetchonerow('o_customer_sessions',"device_id='$device_id' AND session_code='$session_code' AND ending_date >= '$fulldate' AND status=1","uid, customer_id");
        if($session_d['uid'] < 1){
            $result_ = 0;
            $details_ = '"Session Invalid"';
            $result_code = 107;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            die();
            exit();
        }

        $customer_id = $session_d['customer_id'];

        if((input_length($keen_name, 5)) == 0){
            $result_ = 0;
            $details_ = '"Next of Kin name required"';
            $result_code = 101;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            die();
            exit();
        }
        if((emailOk($keen_email)) == 0){
            $result_ = 0;
            $details_ = '"Next of Kin email required"';
            $result_code = 101;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            die();
            exit();
        }
        if((validate_phone($keen_phone)) == 0){
            $result_ = 0;
            $details_ = '"Next of Kin phone invalid"';
            $result_code = 101;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            die();
            exit();
        }
        if($keen_relationship == '0'){
            $result_ = 0;
            $details_ = '"Next of Kin Relationship required"';
            $result_code = 101;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            die();
            exit();
        }
        //////////--------------Referee 1
        if((input_length($ref1_name, 5)) == 0){
            $result_ = 0;
            $details_ = '"Referee name required"';
            $result_code = 102;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            die();
            exit();
        }
        if((emailOk($ref1_email)) == 0){
            $result_ = 0;
            $details_ = '"Referee email required"';
            $result_code = 102;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            die();
            exit();
        }
        if((validate_phone($ref1_phone)) == 0){
            $result_ = 0;
            $details_ = '"Referee phone invalid"';
            $result_code = 102;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            die();
            exit();
        }
        if($ref1_relationship == '0'){
            $result_ = 0;
            $details_ = '"Referee Relationship required"';
            $result_code = 102;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            die();
            exit();
        }
        //////////--------------Referee 2
        if((input_length($ref2_name, 5)) == 0){
            $result_ = 0;
            $details_ = '"Referee name required"';
            $result_code = 103;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            die();
            exit();
        }
        if((emailOk($ref2_email)) == 0){
            $result_ = 0;
            $details_ = '"Referee email required"';
            $result_code = 103;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            die();
            exit();
        }
        if((validate_phone($ref2_phone)) == 0){
            $result_ = 0;
            $details_ = '"Referee phone invalid"';
            $result_code = 103;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            die();
            exit();
        }
        if($ref2_relationship == '0'){
            $result_ = 0;
            $details_ = '"Referee Relationship required"';
            $result_code = 103;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            die();
            exit();
        }


        if ($keen_phone != $ref1_phone && $keen_phone != $ref2_phone && $ref1_phone != $ref2_phone) {
           // echo "All three variables are different.";
        } else {
            //echo "At least two variables are the same.";
            $result_ = 0;
            $details_ = '"Phone numbers of Next of KIN and Referees should be unique"';
            $result_code = 103;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            die();
            exit();
        }


        $sec = '{"1": "'.$keen_name.'", "2": "'.$keen_email.'", "3": "'.$keen_phone.'", "4": "'.$keen_relationship.'", "5": "'.$ref1_name.'", "6": "'.$ref1_email.'", "7": "'.$ref1_phone.'", "8": "'.$ref1_relationship.'", "9": "'.$ref2_name.'", "10": "'.$ref2_email.'", "11": "'.$ref2_phone.'", "12": "'.$ref2_relationship.'"}';

        ////-----save customer sec data
        $update = updatedb('o_customers',"sec_data='$sec', status=1","uid='$customer_id'");
        if($update == 1){
            $result_ = 1;
            $details_ = '"Profile updated successfully"';
            $result_code = 111;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");

        }
        else{
            $result_ = 0;
            $details_ = '"Error updating profile"';
            $result_code = 121;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        }





