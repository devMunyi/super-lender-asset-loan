<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$customer_id = intval($_POST['customer_id']);    $customer_id_dec = decurl($customer_id);
$added_date = $fulldate;
$guarantor_name = sanitizeAndEscape($_POST['guarantor_name'], $con);
$national_id = sanitizeAndEscape($_POST['national_id'], $con);
$mobile_no = make_phone_valid(sanitizeAndEscape($_POST['mobile_no'], $con));
$physical_address = sanitizeAndEscape($_POST['physical_address'], $con);
$amount_guaranteed = doubleval($_POST['amount_guaranteed']);
$relationship = intval($_POST['relationship']);
$status = 1;


//input validations
if(input_available($guarantor_name) == 0){
    exit(errormes("Guarantor name is required"));
}elseif((input_length($guarantor_name, 3)) == 0){
    exit(errormes("Guarantor name is too short"));
}


if($customer_id > 0){
    //////---------Check if contact type exists
    $exists = checkrowexists("o_customer_guarantors","guarantor_name = \"$guarantor_name\" AND national_id = \"$national_id\" AND mobile_no = \"$mobile_no\" AND customer_id=\"$customer_id_dec\" AND  status=1");
    if($exists == 1){
        exit(errormes('This guarantor is already added'));
    }
}



if(input_available($national_id) == 0){
   exit(errormes("National ID is required"));
}elseif(input_length($national_id, 5) == 0){
    exit(errormes("National ID is invalid"));
}else{
    //check if National ID is unique
    $id_exists = checkrowexists("o_customer_guarantors", "uid > 0 AND status > 0 AND national_id = \"$national_id\"");
    if($id_exists == 1){
      exit(errormes("National ID In Use by Another Guarantor"));
    }
}

// Guarantor's mobile number is invalid 2547112554974

if(validate_phone($mobile_no) == 0){
    exit(errormes("Guarantor's mobile number is invalid".$mobile_no));
}else{
    //check if mobile number is unique
    $total_users = countotal('o_customer_guarantors',"uid > 0 AND status > 0 AND mobile_no = \"$mobile_no\"","uid");
  //  $mobile_exists = checkrowexists("o_customer_guarantors", "uid > 0 AND status > 0 AND mobile_no = \"$mobile_no\"");
    if($total_users > 2){
        exit(errormes("Guarantor already in use by $total_users users"));
    }
}


if($relationship > 0){

}else{
    exit(errormes("Guarantor's relationship is required"));
}



$fds = array('customer_id','added_date','guarantor_name','national_id','mobile_no','physical_address', 'amount_guaranteed', 'relationship','status');
$vals = array($customer_id_dec, "$added_date","$guarantor_name","$national_id","$mobile_no","$physical_address", $amount_guaranteed, $relationship, $status);
$create = addtodb('o_customer_guarantors',$fds,$vals);
if($create == 1)
{
    echo sucmes('Guarantor Added Successfully');
    $proceed = 1;

}
else
{
    exit(errormes('Unable to Add Guarantor '.$create));
}

mysqli_close($con);

?>
<script>
    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
            reload();
        },100);
        guarantor_list('<?php echo $customer_id; ?>','EDIT');
        clear_form('guarantor_form');
    }
</script>
