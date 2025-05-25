<?php
// action/asset_loans/cart-action.php
session_start();
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include_once '../../configs/20200902.php';
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}

$action = $_POST['action'];
$uid = $_POST['uid'];

if($uid > 0){}
else{
    exit(errormes("Item ID invalid"));
}

$item = fetchonerow('o_asset_cart',"uid='$uid'","quantity, status, asset_id");
$update = 0;

$asset_id = $item['asset_id'];
$asset = fetchonerow('o_assets',"uid='$asset_id'","selling_price");
$asset_price = $asset['selling_price'];

if($action == 'ADD'){
    $quantity = $item['quantity'];
    $new_total = $quantity + 1;

    $line_total = $asset_price * $new_total;
    $update = updatedb('o_asset_cart',"status=1, quantity=$new_total, unit_price=$asset_price, total_price=$line_total","uid='$uid'");
}
elseif ($action == 'MINUS'){
    $quantity = $item['quantity'];
    $new_total = $quantity - 1;
    if($new_total < 1){
        $new_total = 1;
    }

    $line_total = $asset_price * $new_total;
    $update = updatedb('o_asset_cart',"status=1, quantity=$new_total, unit_price=$asset_price, total_price=$line_total","uid='$uid'");
}
elseif ($action == 'REMOVE'){
    $update = updatedb('o_asset_cart',"status=0","uid='$uid'");
    if ($update == 1){
        echo sucmes("Removed");
    }
    else{
        echo errormes("Error removing item");
    }
}

if($update == 1){
   $value = $new_total;
  // echo sucmes("Done");
}
else{
    echo errormes("An error occurred");
}

$cart_total = totaltable('o_asset_cart',"status=1 AND loan_id=0","total_price");

?>

<script>
    if('<?php echo $value ?>') {
        $('#q<?php echo $uid ?>').text('<?php echo $value; ?>');
        $('#u<?php echo $uid ?>').text('<?php echo money($asset_price); ?>');
        $('#l<?php echo $uid ?>').text('<?php echo money($line_total); ?>');
        $('#cart_total').text('<?php echo money($cart_total); ?>');
    }
    if('<?php echo $action; ?>' === 'REMOVE'){
        $('#rec<?php echo $uid;?>').fadeOut('fast');
    }
</script>
