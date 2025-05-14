<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);
session_start();
$_SESSION['db_name'] = 'zidicash_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

////------APPLY LOAN


$session_code = $_POST['session_id'];
$device_id = $_POST['device_id'];


if((input_length($device_id, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid device Id"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    die();
    exit();
}
if((input_length($session_code, 10)) == 0){
    $result_ = 0;
    $details_ = '"Invalid Session Code"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    die();
    exit();
}

$session_d = fetchonerow('o_customer_sessions',"device_id='$device_id' AND session_code='$session_code' AND ending_date >= '$fulldate' AND status=1","uid, customer_id");
if($session_d['uid'] < 1){
    $result_ = 0;
    $details_ = '"Session Invalid"';
    $result_code = 107;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    die();
    exit();
}

$customer_id = $session_d['customer_id'];
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $file_type = $_POST['file_type'];
  ////////////////ACTUAL UPLOAD
    $title  = fetchrow('o_customer_document_categories',"uid='$file_type'","name");
    $description = "Uploaded from App";
    $category = $file_type;
    $rec = $customer_id;
    $file_name = $_FILES['file']['name'];
    $file_size = $_FILES['file']['size'];
    $file_tmp = $_FILES['file']['tmp_name'];
    if($category == 1) {
        $make_thumbnail = 1;
    } else{
        $make_thumbnail = 1;
    }
    $added_by = 0;
    $added_date = $fulldate;
    $tbl = 'o_customers';
// $rec = $_POST['rec'];
    $status = 1;
    $resize_photos = 1;
    $upload_location = '../uploads_/';

    $allowed_formats = fetchrow("o_customer_document_categories", "uid=$category", "formats");
    $allowed_formats_array = explode(",", $allowed_formats);

    if ($file_size > 100) {
        if ((file_type($file_name, $allowed_formats_array)) == 0) {
            $result_ = 0;
            $details_ = '"This file format is not allowed. Only '.$allowed_formats.'"';
            $result_code = 102;
            echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
            store_event('o_customers', 0,"$result_, $details_, $result_code");
            die();
            exit();
        }
    } else {
        $result_ = 0;
        $details_ = '"Invalid image. Please upload a different file"';
        $result_code = 102;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        store_event('o_customers', 0,"$result_, $details_, $result_code");
        die();
        exit();
    }

    $upload = upload_file($file_name, $file_tmp, $upload_location);
    if ($upload === 0) {
        $result_ = 0;
        $details_ = '"Error uploading image"';
        $result_code = 102;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        store_event('o_customers', 0,"$result_, $details_, $result_code");
        die();
        exit();
    }
    $file_name_only = pathinfo($upload, PATHINFO_FILENAME);
    $file_extension = strtolower(pathinfo($upload, PATHINFO_EXTENSION));

    if ($make_thumbnail == 1 && $file_extension != 'pdf') {
        makeThumbnails($upload_location, $upload, 100, 100, "thumb_" . $file_name_only);
    }
    $stored_address = $upload;

    $fds = array('title', 'description', 'category', 'added_by', 'added_date', 'tbl', 'rec', 'stored_address', 'status');
    $vals = array("$title", "$description", "$category", "$added_by", "$added_date", "$tbl", "$rec", "$stored_address", "$status");
    $create = addtodb('o_documents', $fds, $vals);
    if ($create == 1) {
        $result_ = 1;
        $details_ = '"Success Submitting '.$title.'"';
        $result_code = 111;

        ///----Check if all files have been supplied
        $total = countotal('o_documents',"status=1 AND tbl='o_customers' AND rec='$rec'","uid");
        if($total >= 5){
            $cust = fetchonerow('o_customers',"uid='$rec'","primary_mobile, full_name");
            $phone = $cust['primary_mobile'];
            $full_name = $cust['full_name'];
          /////-----Notify product manager
            $fds = array('phone','message_body','queued_date','sent_date','created_by','status');
            $vals = array(254702332796,"A Client ($full_name, $phone) has uploaded all documents", "$fulldate","$fulldate","1","1");
            $save_ = addtodb('o_sms_outgoing', $fds, $vals);

            ////---Notify the customer


            $fds = array('phone','message_body','queued_date','sent_date','created_by','status');
            $vals = array($phone,"Dear $full_name, we have received your documents. Please be patient as we review them. This typically take less than 30 minutes", "$fulldate","$fulldate","1","1");
            $save_ = addtodb('o_sms_outgoing', $fds, $vals);

        }
        /// ---Check if all files have been supplied


        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        store_event('o_customers', 0,"$result_, $details_, $result_code");
        die();
        exit();

        $proceed = 1;
        ////----Check the optional file resize flag
        if ($resize_photos == 1) {
            $imagePath = "../../uploads_/$stored_address";
            $res = resizeAndCompressImage($imagePath);
            if ($res == 1) {
               // echo sucmes("Resized");
            }
        }
        /// ----End of check file resize

    } else {
        $result_ = 0;
        $details_ = '"System error uploading file, please retry"';
        $result_code = 102;
        echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
        store_event('o_customers', 0,"$result_, $details_, $result_code");
        die();
        exit();
    }

  /////////////////END OF ACTUAL UPLOAD


} else {
    $result_ = 0;
    $details_ = '"Error uploading image"';
    $result_code = 102;
    echo json_encode("{\"result_\":$result_,\"details_\":$details_, \"result_code\":$result_code}");
    store_event('o_customers', 0,"$result_, $details_, $result_code");
    die();
    exit();
}
