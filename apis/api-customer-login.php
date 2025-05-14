<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST, GET, PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
$data = json_decode(file_get_contents('php://input'), true);


$name = $_GET['name'];
$email= $_GET['email'];
$full_name = mysqli_real_escape_string($con, trim($data['full_name']));
$dob = trim($data['dob']);
$national_id = trim($data['national_id']);
$pin = trim($data['pin']);
$primary_phone = make_phone_valid(trim($data['primary_phone']));
$join_date = $fulldate;

$full_name_ok = input_between(3,50, $full_name);
$dob_ok = datevalid($dob);
$phone_ok = validate_phone($primary_phone);
$national_id_ok = input_between(6,12, $national_id);
$pin_ok = input_length($pin, 4);


$valid = $full_name_ok + $dob_ok + $phone_ok + $national_id_ok + $pin_ok;
if($valid < 5)
{
    /////-----There are errors
    $errors = "";
    if($full_name_ok == 0){
        $errors.="Name Invalid, ";
    }
    if($dob_ok == 0){
        $errors.="DOB Invalid/Required, ";
    }
    if($phone_ok == 0){
        $errors.="Mobile Number Invalid. Please start with 254 or 07 ";
    }
    if($national_id_ok == 0){
        $errors.="ID Invalid, ";
    }
    if($pin_ok == 0){
        $errors.="Pin Invalid/Required, ";
    }

    $result_ = 0;
    $details_ = "\"$errors\"";
}
else {


    $OTP = OTP();
    $verified = 0;
    $status = 1;

    $OTP_ = md5($OTP);
    $pin_ = md5($pin);

    $format_db = dateformat($dob);

    $sql = "INSERT INTO l_users(full_name ,dob ,national_id ,primary_phone ,join_date ,OTP, pin ,verified ,status ) VALUES ('$full_name','$format_db','$national_id','$primary_phone','$join_date','$OTP_','$pin_','$verified','$status')";
    if(!mysqli_query($con,$sql))
    {
        $number_exists = checkrowexists("l_users","primary_phone='$primary_phone'");
        $id_exists = checkrowexists("l_users","national_id='$national_id'");
        if($number_exists == 1){
            $details_ = '"Mobile number already exists"';
        }
        elseif($id_exists == 1){
            $details_ = '"National ID Exists"';
        }
        else{
            $details_ = '"Unknown error occurred. Please retry"';
        }
        $result_ = 0;

    }
    else
    {
        $result_ = 1;
        $details_ = "Success";
        $det = fetchonerow('l_users',"primary_phone='$primary_phone'","uid, full_name");
        $uid = $det['uid'];
        $full_name = $det['full_name'];
        $details_ = "{\"userid\":\"$uid\", \"full_name\":\"$full_name\"}";
        send_SMS($primary_phone, "Your OTP is $OTP. For any queries call +254715469243");
    }


    $con->close();
}

echo json_encode("{\"result_\":$result_,\"details_\":$details_}");

