<?php
session_start();
include_once("../../configs/conn.inc");
include_once("../../php_functions/functions.php");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
}

$product_id = $_POST['product_id'];
$delete_permision  = permission($userd['uid'], 'o_loan_products', $product_id, "delete_");
if ($delete_permision == 0) {
    exit(errormes("You don't have permission to delete product"));
}

if($product_id > 0){
    $update = updatedb('o_loan_products', "status=0", "uid= $product_id");
    if($update == 1)
    {
        echo sucmes('Success deleting product');
        $proceed = 1;
    }else{
        die(errormes('Unable to delete product'));
    }
}else{
    die(errormes("Product ID invalid"));
}

?>

<script>

    if('<?php echo $proceed; ?>' === '1'){
        setTimeout(function () {
        	gotourl("loan-products");
        },2000);
    }
</script>







