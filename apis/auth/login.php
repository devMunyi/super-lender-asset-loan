<?php

// error_reporting(E_ALL);
// ini_set('display_errors', 1);

$expected_http_method = 'POST';
include_once("../../configs/cors.php");
include_once("../../vendor/autoload.php");
include_once("../../configs/conn.inc");
include_once("../../configs/jwt.php");
include_once("../../php_functions/jwtAuthUtils.php");
include_once("../../php_functions/functions.php");



$data = json_decode(file_get_contents('php://input'), true);
$email = sanitizeAndEscape($data['email_or_phone'], $con);   ///////////Email could be phone
$password = trim($data['password']);
$user_type = sanitizeAndEscape($data['user_type'], $con);
$deviceid = $_POST['deviceid'] ?? "";
$browsername = $_POST['browsername'] ?? "";
$IPAddress = $_POST['IPAddress'] ?? "";
$OS = $_POST['OS'] ?? "";

$email_valid = emailOk($email);
$password_availed = input_available($password);

if($email_valid == 0){
    ////-----Check if its a number
    if((validate_phone(make_phone_valid($email))) == 1){

    }
    else {
        sendApiResponse(401, "Email or Mobile number is invalid");
    }
}
if($password_availed == 0){
    sendApiResponse(400, "Password is Required!");
}



$branches = table_to_obj('o_branches', "uid > 0", "1000", "uid", "name");
if($password_availed == 1){

    $userrecord = fetchonerow("o_users","email='$email'","uid, name, user_group, status, pass1, company, branch");
    $userid = $userrecord['uid'];
    $status =$userrecord['status'];
    $user_name = $userrecord['name'];
    $user_group_id = $userrecord['user_group'];
    $user_branch_id = $userrecord['branch'];
    $user_branch = $branches[$user_branch_id] ?? "";

    $group_names = table_to_obj('o_user_groups', "uid > 0", "100", "uid", "name");
    $user_group = $group_names[$user_group_id] ?? "";

    if($userid > 0)
    {
        if($status != 1)
        {
            sendApiResponse(403, "Account is disabled. Please contact us.");         
        }
        else
        {
            ///////--------------Company verification
            $company = $userrecord['company'];
            $comp = company_details($company);
            $comp_id = $comp['uid'];
            if($comp_id > 0){
                $_SESSION['company_details'] = $comp;
            }
            else{
                sendApiResponse(401, "Company details missing from your profile $company");
            }


            ////////////Password verification
            $databasepass = $userrecord['pass1'];
            $thesalt = fetchrow('o_passes',"user='$userid'",'pass');

            ////apendsalt to inputted password
            $fullpass= $thesalt.$password;
            $encpass=hash('SHA256', $fullpass);
            ////fetch user pass from db


            $payload = null;
            if($encpass == $databasepass)
            {
                // generate bearer token expiring after an hour
                $payload = generateBearerToken($jwt_key, ['uid' => $userid, 'username' => $user_name, 'email' => $email, 'user_group' => $user_group, 'branch' => $user_branch,]);
    

                // echo $token;

                if($payload){
                    $signature = getJwtSignature($payload['refresh_token']);
                    generateToken2($userid, $signature, $deviceid, $browsername, $IPAddress, $OS);
                    sendApiResponse(200, "Access token generated successfully!", 'OK', $payload);
                }else {
                    sendApiResponse(500, "Internal error occured while generating access token. Please retry! $payload");
                }
            }
            else
            {
                sendApiResponse(401, "Password mismatch!", "FAILED", $payload);
            }
        }
    }
    else
    {      
        sendApiResponse(401, "Email or Mobile number does not exist.");
    }

}
?>