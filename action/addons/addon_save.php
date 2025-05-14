<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");


$name = $_POST['name'];
$description = $_POST['description'];
$amount = $_POST['amount'];
$amount_type = $_POST['amount_type'];
$loan_stage = $_POST['loan_stage'];
$automatic = $_POST['automatic'];
$addon_on = $_POST['addon_on'];
$starting_day = $_POST['starting_day'];
$ending_day = $_POST['ending_day'];
$apply_frequency = $_POST['apply_frequency'];
$notify_user = $_POST['notify_user'];
$applicable_loan = $_POST['applicable_loan'];
$paid_upfront = $_POST['paid_upfront'];
$deducted_upfront = $_POST['deducted_upfront'];
$status = 1;

if((input_length($name, 2)) == 0)
{
    die(errormes("AddOn Name is required"));
    exit();
}
else{
    $addon_exists = checkrowexists('o_addons',"name='$name'");
    if($addon_exists == 1){
        die(errormes("AddOn Name Exists"));
        exit();
    }
}
if($amount > 0){}
else{
    die(errormes("AddOn Amount should be more than 0"));
    exit();
}



$fds = array('name','description','amount','amount_type','loan_stage','automatic','addon_on','from_day','to_day','apply_frequency','notify_user','applicable_loan','paid_upfront','deducted_upfront','status');
$vals = array("$name","$description","$amount","$amount_type","$loan_stage","$automatic","$addon_on","$starting_day","$ending_day","$apply_frequency","$notify_user","$applicable_loan","$paid_upfront","$deducted_upfront","$status");
$create = addtodb('o_addons',$fds,$vals);
if($create == 1)
{    echo sucmes('AddOn Added Successfully');
    $proceed = 1;
}
else
{
    echo errormes('Unable Add AddOn');
}


?>
<script>
    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
            reload();
        },1500);
    }
</script>

