<?php


session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");
include_once ("../../vendor/autoload.php");
// use React\EventLoop\Loop;
// use React\Promise\Promise;

$userd = session_details();

$customer_id = $_POST['customer_id'];    $customer_id_dec = decurl($customer_id);
$added_date = $fulldate;
$referee_name = sanitizeAndEscape($_POST['referee_name'], $con);
$id_no = $_POST['id_no'];
$mobile_no = make_phone_valid($_POST['mobile_no']);
$physical_address = sanitizeAndEscape($_POST['physical_address'], $con);
$email_address = $_POST['email_address'];
$relationship = $_POST['relationship'];
$ref_id = decurl($_POST['ref_id']);
$status = 1;

$cust_det = fetchonerow('o_customers',"uid='$customer_id_dec'","status");
$customer_status = $cust_det['status'];
$state = fetchonerow('o_customer_statuses', "code='$customer_status'", "name");
$status_name = $state['name'];

$update_this_status = permission($userd['uid'],'o_customer_statuses', $customer_status, "update_");
$update_permission  = permission($userd['uid'],'o_customer_referees',0,"update_");

// $loop = Loop::get();
// $cust_det_promise = new Promise(function ($resolve) use ($customer_id_dec) {
//     $resolve(fetchonerow('o_customers',"uid='$customer_id_dec'","status"));
// });

// $cust_det_promise->then(function ($cust_det) use (&$customer_status, &$status_name) {
//     $customer_status = $cust_det['status'];
//     $state = fetchonerow('o_customer_statuses', "code='$customer_status'", "name");
//     $status_name = $state['name'];
// });

// $promise1 = new Promise(function ($resolve) use ($userd, $customer_status) {
//     $resolve(permission($userd['uid'], 'o_customer_statuses', $customer_status, "update_"));
// });

// $promise2 = new Promise(function ($resolve) use ($userd) {
//     $resolve(permission($userd['uid'], 'o_customer_referees', 0, "update_"));
// });

// \React\Promise\all([$promise1, $promise2])->then(function ($results) use (&$update_this_status, &$update_permission) {
//     [$update_this_status, $update_permission] = $results;
// });

// $loop->run();

if($update_permission == 1 && $update_this_status == 1) {
}
else{
    die(errormes("You don't have permission to update referee where client is ". $status_name));
    exit();
}

//input validations
if(input_available($referee_name) == 0){
    echo errormes("Referee name is required");
    die();
}elseif((input_length($referee_name, 3)) == 0){
    echo errormes("Referee name is too short");
    die();
}


if(input_available($email_address) == 0){
   // echo errormes("Email is required");
   // die();
}elseif(emailOk($email_address) == 0){
   // echo errormes("Email is invalid");
   // die();
}else{
    //check if email is unique
    $email_exists = checkrowexists("o_customer_referees", "uid > 0 AND status > 0 AND email_address = \"$email_address\" AND uid != $ref_id");
    if($email_exists == 1){
      //  die(errormes("Email In Use by Another Referee"));
    }
}

/*
if(input_available($id_no) == 0){
    echo errormes("National Id is required");
    die();
}elseif(input_length($id_no, 5) == 0){
    echo errormes("National ID is invalid");
    die();
}else{
    //check if National ID is unique
    $id_exists = checkrowexists("o_customer_referees", "uid > 0 AND status > 0 AND id_no = \"$id_no\" AND uid != $ref_id");
    if($id_exists == 1){
        die(errormes("National ID In Use by Another Referee"));
    }
}
*/

if(validate_phone($mobile_no) == 0){
    echo errormes("Referee's mobile number is invalid");
    die();
}else{
    //check if mobile number is unique
    $total_users = countotal('o_customer_referees',"uid > 0 AND status > 0 AND mobile_no = \"$mobile_no\" AND uid != $ref_id","uid");
  //  $mobile_exists = checkrowexists("o_customer_referees", "uid > 0 AND status > 0 AND mobile_no = \"$mobile_no\" AND uid != $ref_id");
    if($total_users > 2){
        die(errormes("Referee used by another $total_users customers"));
    }
}


if($relationship > 0){

}else{
    echo errormes("Referee's relationship is required");
    die();
}


if($customer_id > 0){
    //////---------Check if contact type exists
    $exists = checkrowexists("o_customer_referees","referee_name = \"$referee_name\" AND email_address = \"$email_address\" AND id_no = \"$id_no\" AND mobile_no = \"$mobile_no\" AND (customer_id != \"$customer_id_dec\" OR uid != $ref_id) AND  status=1");
    if($exists == 1){
        echo errormes('This referee is already added');
        die();
    }
}



$update_flds = "referee_name=\"$referee_name\", id_no= \"$id_no\", mobile_no=\"$mobile_no\", physical_address=\"$physical_address\", email_address=\"$email_address\", relationship=\"$relationship\"";
$create = updatedb('o_customer_referees', $update_flds,"uid=".$ref_id);
if($create == 1)
{
    $event = "Referee details updated to $update_flds";
    store_event('o_customer_referees', $ref_id,"$event");
    echo sucmes('Referee Updated Successfully');
    $proceed = 1;

}
else
{
    echo errormes('Unable to Update Referee');
}


?>
<script>
    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
        reload();
        },400);
        referee_list('<?php echo $customer_id; ?>','EDIT');
    }
</script>
