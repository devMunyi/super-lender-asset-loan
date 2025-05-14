<?php
session_start();
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include_once '../../configs/20200902.php';
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
if ($has_archive == 1) {
    include_once("../../configs/archive_conn.php");
}

$userd = session_details();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}

$create_loan = permission($userd['uid'], 'o_loans', "0", "create_");
if ($create_loan != 1) {
    exit(errormes("You don't have permission to create loan"));
}

$customer_id = $_POST['customer_id'];
$loan_amount = $_POST['loan_amount'];
// echo "amount => ".$loan_amount;
//$asset_id = $_POST['asset_id']; ----Replaced by cart
$period = $_POST['period'];
$added_by = $userd['uid'];
$application_mode = $_POST['application_mode'] ?? 'MANUAL';
$loan_type = $_POST['loan_type'] ?? 4;
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

$result_ = give_loan($customer_id, $product_id, $loan_amount, $application_mode);
$result = intval($result_);
if ($result > 0) {
    // echo sucmes("Asset loan created successfully");
    $loan_id = $result;
    //  echo errormes($loan_id);
    // update loan_type and asset_id
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

    if ('<?php echo $proceed; ?>' === "1") {
        setTimeout(function () {
            gotourl('loans?loan=<?php echo encurl($loan_id); ?>&just-created');
        }, 56500);
    }
</script>