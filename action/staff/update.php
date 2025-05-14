<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}

$permi = permission($userd['uid'],'o_users',"0","update_");
if($permi != 1){
    exit(errormes("You don't have permission to update user"));
}

$staff_id = $_POST['sid'];
$dec_staff_id = decurl($staff_id);
$name = sanitizeAndEscape($_POST['name'], $con);
$email = sanitizeAndEscape($_POST['email'], $con);
$phone = make_phone_valid($_POST['phone'] ?? '');
$join_date = $fulldate;
$pass1 = sanitizeAndEscape($_POST['password'], $con);
$user_group = $_POST['user_group'];
$national_id = sanitizeAndEscape($_POST['national_id'], $con);
$branch = $_POST['branch'];
$status = $_POST['status'];
$tag = $_POST['tag'];
$pair_ = $_POST['pair_'];



$company = company_settings();
$company_id = $company['company_id'];

///////----------Validation
if($staff_id > 0){
    $staff = fetchonerow('o_users',"uid='$staff_id'","uid, email, user_group");
}
else
{
    exit(errormes("Staff ID is required"));
}
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
    $user_exists = checkrowexists('o_users',"email='$email' AND uid!='".decurl($staff_id)."'");
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
    $id_exists = checkrowexists('o_users',"national_id='$national_id' AND uid!=".decurl($staff_id)." AND status>0");
    if($id_exists == 1){
        exit(errormes("National ID is in use"));

    }
}


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
    $phone_exists = checkrowexists('o_users',"phone='$phone' AND uid!='".decurl($staff_id)."'");
    if($phone_exists == 1){
        exit(errormes("Phone is in use"));

    }
}
if((input_available($pass1)) == 1){
    if((input_length($pass1, 6)) == 0){

            exit(errormes("Password is too short < 6"));
    
           }
        else{
            $epass = passencrypt($pass1);
            $hash = substr($epass, 0, 64);
            $salt = substr($epass, 64, 96);
            $andpass = " ,pass1='$hash', pass_expiry='$date'";
            $updatepass = 1;
        }

}
//////-----------End of validation

if($user_group == 7){
    $tag = 'LO';
    if($pair_ < 1){
       exit(errormes("You have not specified the pair. 1,2,3 e.t.c."));
    }
    else{
        ///----Check if there is same pair in the branch
        $exists = checkrowexists('o_users',"user_group='4' AND status=1 AND branch='$branch' AND pair_=$pair_ AND uid!=$dec_staff_id");
        if($exists == 1){
         //   exit(errormes("There is already an LO under pair $pair_"));
        }
    }
}
else{
    if($tag == 'LO'){
      //  exit(errormes("Please tag the user correctly"));
    }
}
if($user_group == 8){
    $tag = 'CO';
    if($pair_ < 1){
        exit(errormes("You have not specified the pair. 1,2,3 e.t.c."));
    }
    else{
        ///----Check if there is same pair in the branch
        $exists = checkrowexists('o_users',"user_group='8' AND status=1 AND branch='$branch' AND pair_=$pair_ AND uid!=$dec_staff_id");
        if($exists == 1){
            exit(errormes("There is already an CO under pair $pair_"));
        }
    }
}
else{
    if($tag == 'CO'){
      //  exit(errormes("Please tag the user correctly"));
    }
}





if($status == 99){
    $status = 0;
}

$original_user = fetchonerow('o_users',"uid='".decurl($staff_id)."'","name, email, phone, national_id, user_group, tag, pair, branch, status, pass1");
$original_uid = $original_user['uid'];
$original_name = $original_user['name'];
$original_email = $original_user['email'];
$original_phone = $original_user['phone'];
$original_national_id = $original_user['national_id'];
$original_user_group = $original_user['user_group'];
$original_tag = $original_user['tag'];
$original_pair = $original_user['pair'];
$original_branch = $original_user['branch'];
$original_status = $original_user['status'];
$original_pass_hash = $original_user['pass1'];
$updatefds = "name='$name', email='$email', phone='$phone', national_id='$national_id',  user_group='$user_group', tag='$tag', pair='$pair_', branch='$branch', status='$status' $andpass";
$create = updatedb('o_users',"$updatefds","uid='".decurl($staff_id)."'");
if($create == 1)
{

    $event = "User updated by [".$userd['name']."(".$userd['email'].")]. Details -> ";
    $orginal_event = $event;

    if(!empty($updatepass) && $original_pass_hash != $hash){
        $event .= "Password Changed, ";
    }
    
    if($original_name != $name){
        $event .= "Name: $original_name -> $name, ";
    }

    if($original_email != $email){
        $event .= "Email: $original_email -> $email, ";
    }

    if($original_phone != $phone){
        $event .= "Phone: $original_phone -> $phone, ";
    }

    if($original_national_id != $national_id){
        $event .= "National ID: $original_national_id -> $national_id, ";
    }

    if($original_user_group != $user_group){
        $user_group_names = table_to_obj("o_user_groups", "uid IN ($original_user_group, $user_group)", "100", "uid", "name");
        $event .= "User Group: {$user_group_names[$original_user_group]} -> {$user_group_names[$user_group]}, ";
    }

    if($original_tag != $tag){
        $event .= "Tag: $original_tag -> $tag, ";
    }

    if($original_pair != $pair_){
        $event .= "Pair: $original_pair -> $pair_, ";
    }

    if($original_branch != $branch){
        $branch_names = table_to_obj("o_branches", "uid IN ($original_branch, $branch)", "100", "uid", "name");
        $event .= "Branch: {$branch_names[$original_branch]} -> {$branch_names[$branch]}, ";
    }

    if($original_status != $status){
        $status_names = table_to_obj("o_staff_statuses", "uid IN ($original_status, $status)", "100", "uid", "name");
        $event .= "Status: {$status_names[$original_status]} -> {$status_names[$status]}";
    }

    if($orginal_event == $event){
        $event = "User update triggered by [".$userd['name']."(".$userd['email'].")]. No changes captured";
    }

    // remove possible trailing comma from event replace with fullstop
    $event = rtrim(trim($event), ',') . '.';

    echo sucmes('Record Updated Successfully');
    store_event('o_users', decurl($staff_id),"$event");
    ///--------Create email
    // include_once("../../configs/auth.inc");
    if($company_id > 0){
        $up_ =  create_company_member($email, $company_id);
      //  echo errormes("Update company $company_id, $email,$up_ <br/>");
    }
    $proceed = 1;
    if($updatepass == 1){
        $savesalt = updatedb('o_passes', "pass='$salt'", "user='".decurl($staff_id)."'");
    }

   //if($status != 1){
       $session_keydest = updatedb('o_tokens',"status=0, expiry_date='$fulldate'","userid='".decurl($staff_id)."' AND status=1");
   //}
}
else
{
    echo errormes('Unable to Update Record');
    $proceed = 0;
}



?>

<script>

    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
           gotourl("staff?staff=<?php echo $staff_id; ?>")
        },2000);
    }
</script>




