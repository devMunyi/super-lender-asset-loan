<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}

$permi = permission($userd['uid'],'o_users',"0","create_");
if($permi != 1){
    exit(errormes("You don't have permission to create user"));
}

$name = sanitizeAndEscape(trim($_POST['name']) ?? '', $con);
$email = sanitizeAndEscape($_POST['email'] ?? '', $con);
$phone = make_phone_valid($_POST['phone'] ?? '');
$join_date = $fulldate;
$national_id = sanitizeAndEscape($_POST['national_id'] ?? '', $con);
$pass1 = sanitizeAndEscape($_POST['password'] ?? '', $con);
$user_group = $_POST['user_group'];
$branch = $_POST['branch'];
$status = $_POST['status'];
$pair_ = $_POST['pair_'] ? $_POST['pair_'] : 0;
$tag = $_POST['tag'];

$company = company_settings();
$company_id = $company['company_id'];

//echo errormes($name);
///////----------Validation
if((input_available($name)) == 0)
{
    exit(errormes("Name is invalid/required"));
}
if((validate_phone($phone)) == 0)
{
    exit(errormes("Mobile Number is invalid/required"));
}
if((emailOk($email)) == 0)
{
    exit(errormes("Email invalid/required"));
}
else{
    $user_exists = checkrowexists('o_users',"email='$email'");
    if($user_exists == 1){
        exit(errormes("Email is in use"));

    }
}
////////////-------------
if((input_length($national_id, 4)) == 0)
{
    exit(errormes("National ID is required"));
}
else{
    $id_exists = checkrowexists('o_users',"national_id='$national_id'");
    if($id_exists == 1){
        exit(errormes("National ID is in use"));

    }
}


/////-----




if($status < 1){
    exit(errormes("Status required"));
}

if($user_group < 1){
    exit(errormes("User Group required"));
}
if((validate_phone($phone)) == 0)
{
    exit(errormes("Phone Number invalid/required"));
}
else{
    $phone_exists = checkrowexists('o_users',"phone='$phone'");
    if($phone_exists == 1){
        exit(errormes("Phone is in use"));

    }
}

    if((input_length($pass1, 6)) == 0){
        if($phone_exists == 1){
            exit(errormes("Password is too short < 6"));
    
        }
        else{

        }
    }

    if($user_group == 7){
        $tag = 'LO';
        if($pair_ < 1){
           exit(errormes("Please specify the LO pair. 1,2,3 e.t.c."));
        }
        else{
            ///----Check if there is same pair in the branch
            $exists = checkrowexists('o_users',"user_group='7' AND status=1 AND branch='$branch' AND pair_=$pair_");
            if($exists == 1){
               exit(errormes("There is already an LO under pair $pair_"));
            }
        }
    }
    else{
        if($tag == 'LO'){
         //   exit(errormes("Please tag the user correctly"));
        }
    }
if($user_group == 8){
    $tag = 'CO';
    if($pair_ < 1){
        exit(errormes("Please specify pair. 1,2,3 e.t.c."));
    }
    else{
        ///----Check if there is same pair in the branch
        $exists = checkrowexists('o_users',"user_group='5' AND status=1 AND branch='$branch' AND pair_=$pair_");
        if($exists == 1){
         //   exit(errormes("There is already an CO under pair $pair_"));
        }
    }
}
else{
    if($tag == 'CO'){
      //  exit(errormes("Please tag the user correctly"));
    }
}

//////-----------End of validation
$epass = passencrypt($pass1);
$hash = substr($epass, 0, 64);
$salt = substr($epass, 64, 96);

$fds = array('name','email','phone','national_id','join_date','pass1','user_group','tag','pair','branch','company','status');
$vals = array("$name","$email","$phone","$national_id","$join_date","$hash","$user_group","$tag","$pair_","$branch","$company_id", 3);
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
    // include_once("../../configs/auth.inc");
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

<script>

    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
          gotourl("staff?staff=<?php echo encurl($sid); ?>")
        },2000);
    }
</script>




