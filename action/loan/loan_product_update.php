<?php
session_start();
include_once("../../configs/conn.inc");
include_once("../../php_functions/functions.php");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

$product_id = $_POST['product_id'];
$name = $_POST['name'];
$description = $_POST['description'];
$period = $_POST['period'];
$period_units = $_POST['period_units'];
$min_amount = $_POST['min_amount'];
$max_amount = $_POST['max_amount'];
$pay_frequency = $_POST['pay_frequency'];
$percent_breakdown = $_POST['percent_breakdown'];
$added_date = $fulldate;
$status = 1;

$update_permision  = permission($userd['uid'], 'o_loan_products', $product_id, "update_");
if ($update_permision == 0) {
    exit(errormes("You don't have permission to update product"));
}

///////----------------Validation
if((input_length($name, 2)) == 1){
    if((checkrowexists('o_loan_products',"name='$name' AND uid != $uid")) == 1){
        exit(errormes("Product with similar name exists"));
    }
}
else{
    exit(errormes("Product name is too short"));
}
if($period > 0){}
else{
    exit(errormes("Period is required"));
}
if($period_units == '0'){
    exit(errormes("Period units required"));
}

if($min_amount > 10){}
else{
    exit(errormes("Min Amount is required"));
}
if($max_amount > 0){}
else{
    exit(errormes("Max Amount required"));
}


///////------------End of validation



$upadate = updatedb('o_loan_products', "name='$name', description='$description', period='$period', period_units='$period_units', min_amount='$min_amount', max_amount='$max_amount', pay_frequency='$pay_frequency', percent_breakdown='$percent_breakdown', added_date='$added_date', status='$status'", "uid=$product_id");
if($upadate == 1)
{
    $product_id = fetchrow('o_loan_products',"name='$name'","uid");
    echo sucmes('Record Updated Successfully');
    $proceed = 1;

}
else
{
    echo errormes('Unable Update Record');
}

?>
<script>

    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
            gotourl("loan-products?product=<?php echo encurl($product_id); ?>");
        },1500);
    }
</script>

