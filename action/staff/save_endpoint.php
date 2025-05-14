<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");
ini_set('display_errors', 1); ini_set('display_startup_errors', 1);

// $userd = session_details();
// if($userd == null){
//     die(errormes("Your session is invalid. Please re-login"));
//     exit();
// }

$data = json_decode(file_get_contents('php://input'), true);
$name = sanitizeAndEscape($data['name'], $con);
$email = sanitizeAndEscape($data['email'], $con);
$phone = make_phone_valid(sanitizeAndEscape($data['phone'], $con));
$national_id = sanitizeAndEscape($data['national_id'], $con);
$pass1 = sanitizeAndEscape($data['password'], $con);
$join_date = $fulldate;
$user_group = $data['user_group'] ?? 0;
$branch = $data['branch'] ?? 0;
$status = $data['status'] ?? 0;
$pair_ = $data['pair_'] ? $data['pair_'] : 0;
$tag = $data['tag'] ?? 1;

$company = company_settings();
$company_id = $company['company_id'];


///////----------Validation
if((input_available($name)) == 0)
{
    die(errormes("Name is invalid/required"));
    exit();
}
if((validate_phone($phone)) == 0)
{
    die(errormes("Mobile Number is invalid/required"));
    exit();
}
if((emailOk($email)) == 0)
{
    die(errormes("Email invalid/required"));
    exit();
}
else{
    $user_exists = checkrowexists('o_users',"email='$email'");
    if($user_exists == 1){
        die(errormes("Email is in use"));
        exit();
    }
}
////////////-------------
if((input_length($national_id, 4)) == 0)
{
    die(errormes("National ID is required"));
    exit();
}
else{
    $id_exists = checkrowexists('o_users',"national_id='$national_id'");
    if($id_exists == 1){
        die(errormes("National ID is in use"));
        exit();
    }
}


/////-----




if($status < 1){
    die(errormes("Status required"));
    exit();
}

if($user_group < 1){
    die(errormes("User Group required"));
    exit();
}
if((validate_phone($phone)) == 0)
{
    die(errormes("Phone Number invalid/required"));
    exit();
}
else{
    $phone_exists = checkrowexists('o_users',"phone='$phone'");
    if($phone_exists == 1){
        die(errormes("Phone is in use"));
        exit();
    }
}

    if((input_length($pass1, 6)) == 0){
        if($phone_exists == 1){
            die(errormes("Password is too short < 6"));
            exit();
        }
        else{

        }
    }

    if($user_group == 4){
        $tag = 'LO';
        if($pair_ < 1){
         //  die(errormes("You have not specified the pair. 1,2,3 e.t.c."));
        }
        else{
            ///----Check if there is same pair in the branch
            $exists = checkrowexists('o_users',"user_group='4' AND status=1 AND branch='$branch' AND pair_=$pair_");
            if($exists == 1){
            //   die(errormes("There is already an LO under pair $pair_"));
            }
        }
    }
    else{
        if($tag == 'LO'){
         //   die(errormes("Please tag the user correctly"));
        }
    }
if($user_group == 5){
    $tag = 'CO';
    if($pair_ < 1){
     //   die(errormes("You have not specified the pair. 1,2,3 e.t.c."));
    }
    else{
        ///----Check if there is same pair in the branch
        $exists = checkrowexists('o_users',"user_group='5' AND status=1 AND branch='$branch' AND pair_=$pair_");
        if($exists == 1){
         //   die(errormes("There is already an CO under pair $pair_"));
        }
    }
}
else{
    if($tag == 'CO'){
      //  die(errormes("Please tag the user correctly"));
    }
}

//////-----------End of validation
$epass = passencrypt($pass1);
$hash = substr($epass, 0, 64);
$salt = substr($epass, 64, 96);

$fds = array('name','email','phone','national_id','join_date','pass1','user_group','tag','pair','branch','company','status');
$vals = array("$name","$email","$phone","$national_id","$join_date","$hash","$user_group","$tag","$pair_","$branch","$company_id","$status");
$create = addtodb('o_users',$fds,$vals);
if($create == 1)
{
    $userid = fetchrow('o_users', "email='$email'", "uid");
    $fdss = array('user', 'pass');
    $valss = array("$userid", "$salt");
    $savesalt = addtodb('o_passes', $fdss, $valss);
    echo sucmes('Record Created Successfully');
    $proceed = 1;
    $last_staff = fetchmax('o_users',"email='$email'","uid","uid");
    $sid = $last_staff['uid'];

    $event = "Staff created by [".$userd['name']."(".$userd['email'].")] on [$fulldate] with details. Name: $name, Email: $email, Phone: $phone, National_id: $national_id, User_group: $user_group, tag: $tag, pair: $pair_, branch: $branch, status: $status, Pass: $hash, company_id: $company_id ";
    store_event('o_users', $sid,"$event");

    /////-----------------Update Company info
    include_once("../../configs/auth.inc");
  //  echo errormes("Company ID $company_id <br/>");
    if($company_id > 0){
      $create =  create_company_member($email, $company_id);
    //  echo errormes("Create company $create <br/>");
    }

}
else
{
    echo errormes('Unable to Update Record'.$create);
    $proceed = 0;
}

?>




