<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();

$customer_id = intval($_POST['customer_id']);    $customer_id_dec = decurl($customer_id);
$added_date = $fulldate;
$guarantor_name = sanitizeAndEscape($_POST['guarantor_name'], $con);
$national_id = sanitizeAndEscape($_POST['national_id'], $con);
$mobile_no = make_phone_valid(sanitizeAndEscape($_POST['mobile_no'], $con));
$physical_address = sanitizeAndEscape($_POST['physical_address'], $con);
$amount_guaranteed = doubleval($_POST['amount_guaranteed']);
$relationship = intval($_POST['relationship']);
$guarantor_id = decurl(intval($_POST['guarantor_id']));
$status = 1;


$cust_det = fetchonerow('o_customers',"uid='$customer_id_dec'","status");
$statuses = table_to_obj('o_customer_statuses',"uid > 0","100","code","name");


$update_this_status = permission($userd['uid'],'o_customer_statuses',$cust_det['status'],"update_");
$update_permission  = permission($userd['uid'],'o_customer_guarantors',0,"update_");
if($update_permission == 1 && $update_this_status == 1) {
}
else{
    exit(errormes("You don't have permission to update guarantor where client is ". $statuses[$cust_det['status']]));
}

//input validations
if(input_available($guarantor_name) == 0){
    exit(errormes("Guarantor name is required"));
}elseif((input_length($guarantor_name, 3)) == 0){
    exit(errormes("Guarantor name is too short"));
}


if(input_available($national_id) == 0){
    exit(errormes("National ID is required"));
}elseif(input_length($national_id, 5) == 0){
    exit(errormes("National ID is invalid"));
}else{
    //check if National ID is unique
    $id_exists = checkrowexists("o_customer_guarantors", "uid > 0 AND status > 0 AND national_id = \"$national_id\" AND uid != $guarantor_id");
    if($id_exists == 1){
        exit(errormes("National ID In Use by Another Guarantor"));
    }
}

if(validate_phone($mobile_no) == 0){
    exit(errormes("Guarantor's mobile number is invalid"));
}else{
    //check if mobile number is unique
    $total_users = countotal('o_customer_guarantors',"uid > 0 AND status > 0 AND mobile_no = \"$mobile_no\" AND uid != $guarantor_id","uid");
  //  $mobile_exists = checkrowexists("o_customer_guarantors", "uid > 0 AND status > 0 AND mobile_no = \"$mobile_no\" AND uid != $guarantor_id");
    if($total_users > 2){
        exit(errormes("Guarantor used by another $total_users customers"));
    }
}


if($relationship > 0){

}else{
    exit(errormes("Guarantor's relationship is required"));
}


if($customer_id > 0){
    ////// ---------Check if guarantor exists
    $exists = checkrowexists("o_customer_guarantors","guarantor_name = \"$guarantor_name\" AND national_id = \"$national_id\" AND mobile_no = \"$mobile_no\" AND (customer_id != \"$customer_id_dec\" OR uid != $guarantor_id) AND  status=1");
    if($exists == 1){
        exit(errormes('This guarantor is already added'));
    }
}



$update_flds = "guarantor_name=\"$guarantor_name\", national_id= \"$national_id\", mobile_no=\"$mobile_no\", physical_address=\"$physical_address\", amount_guaranteed= $amount_guaranteed, relationship= $relationship";
$update = updatedb('o_customer_guarantors', $update_flds,"uid = $guarantor_id");
if($update == 1)
{
    $event = "Guarantor details updated to $update_flds";
    store_event('o_customer_guarantors', $guarantor_id,"$event");
    echo sucmes('Guarantor Updated Successfully');
    $proceed = 1;

}
else
{
    echo errormes('Unable to Update Guarantor');
}


?>
<script>
    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
        reload();
        },400);
        guarantor_list('<?php echo $customer_id; ?>','EDIT');
    }
</script>
