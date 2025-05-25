<?php

$expected_http_method = 'GET';
include_once("../../vendor/autoload.php"); // auto created when installing a dependency with composer or run composer update if have composer.json file
// include_once ("../../configs/allowed-ips-or-origins.php");
include_once("../../configs/conn.inc");
include_once("../../configs/jwt.php");
include_once("../../php_functions/jwtAuthUtils.php");
include_once("../../php_functions/jwtAuthenticator.php");
include_once("../../php_functions/functions.php");


$data = json_decode(file_get_contents('php://input'), true);


$customer_id = $_POST['customer_id'];
$period = $_POST['period'];
$added_by = $userd['uid'];
$application_mode = 'WAPP';
$loan_type = 4;
$product_id = $_POST['product_id'];

//echo errormes("$product_id");

// validations
if (input_available($customer_id) == 0) {
    echo errormes("Please select customer");
    exit();
}

if (input_available($loan_amount) == 0) {
    echo errormes("Amount is required");
    exit();
}


if (input_available($period) == 0) {
    //  echo errormes("Period is required");
    // exit();
}

$user_ = fetchonerow('o_customers', "uid=$customer_id", "primary_product");
$product_id = enforceInteger($user_['primary_product'] ?? 0);
if($product_id == 0) {
    echo errormes("Customer has no subscribed product");
    exit();
}



// if ($lock_product == 1 && $primary_product != $product_id) {
//     $product_name = fetchrow('o_loan_products', "uid=$primary_product AND status = 1", "name");
//     exit(errormes("Please select allowed product: $product_name"));
// }

$result_ = give_loan($customer_id, $product_id, $loan_amount, $application_mode);
$result = intval($result_);



if ($result > 0) {
    // echo sucmes("Asset loan created successfully");
    $loan_id = $result;
    $updaterep = updatedb('o_loans', "loan_type = 4", "uid = '$loan_id'");
    if ($updaterep == 1) {
        $update_cart = updatedb('o_asset_cart', "client_id='$customer_id', loan_id='$loan_id', status=2", "loan_id=0 AND status=1");
        ///////////------------------ update stock
        $assets = fetchtable('o_asset_cart', "client_id='$customer_id' AND loan_id='$loan_id' AND status=2", "uid", "asc", "1000", "asset_id");
        while ($a = mysqli_fetch_array($assets)) {
            $asset_id = $a['asset_id'];
            $update = updatedb('o_assets', "stock = stock - 1", "uid=$asset_id");
            if ($update == 1) {
                // echo sucmes('Stock sold successfully.');
                // $event = "Stock sold and updated by ".$userd['name']."(".$userd['uid'].")";
                // store_event('o_assets', $asset_id, "$event");
                // $proceed = 1;
            } else {
                //  echo errormes('Unable to update stock'.$update.$asset_id);
            }
        }


    } else {
        echo errormes('Unable to Update loan type and asset id');
    }

    echo sucmes('Loan Created Successfully');

    // store create loan event
    $event = "Loan created by " . $userd['name'] . "(" . $userd['uid'] . ")";
    store_event('o_assets', $asset_id, "$event");

    // proceed flag
    $proceed = 1;

} else {
    echo errormes($result_);
    exit();
}
?>

<script>

    if ('<?php echo $proceed; ?>' == "1") {

        // clear cart counter localstorage
        localStorage.setItem('cartCounterValue', 0);
        setTimeout(function () {
            gotourl('loans?loan=<?php echo encurl($loan_id); ?>&just-created');
        }, 2000);
    }
</script>