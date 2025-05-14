<?php
session_start();
// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
include_once '../../configs/20200902.php';
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}


$aid = $_POST['aid'];

if($aid > 0){}
else{
    exit(errormes("Item ID invalid"));
}

$item = fetchonerow('o_asset_cart',"asset_id='$aid'","uid, quantity, status");

$asset = fetchonerow('o_assets',"uid='$aid'","selling_price");
$asset_price = $asset['selling_price'];

//die (sucmes($item['status']));

if($item['uid'] > 0){
    if($item['status'] == 1){
        echo sucmes("Already exists".$item['uid']);
        die();
    }
    elseif($item['status'] == 0){
        $add = updatedb('o_asset_cart',"status=1, quantity=1, loan_id=0, unit_price='$asset_price', total_price='$asset_price'","asset_id='$aid' AND status=0");
        if($add == 1){
            echo sucmes("Added successfully");
        }
        else{
            echo errormes("Error adding".$add);
        }
    }
    else{
        $create_proceed = 1;
    }

}
else{
    $create_proceed = 1;
}

if($create_proceed == 1){
    $fds = array('asset_id','added_by','added_date','quantity','unit_price','total_price','status');
    $vals = array("$aid",$userd['uid'], "$fulldate","1","$asset_price","$asset_price","1");
    $add = addtodb('o_asset_cart', $fds, $vals);
    if($add == 1){
        echo sucmes("Added successfully");
    }
    else{
        echo errormes("Error adding".$add);
    }
}

?>

<script>

</script>
