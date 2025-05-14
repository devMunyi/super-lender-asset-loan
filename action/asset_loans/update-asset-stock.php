<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}
/////---------End of session check
$asset_id = $_POST['asset_id'];
$added_by = $userd['uid'];
$stock = $_POST['stock'];


////////////////validation
if(input_available($stock) == 0){
    echo errormes("Stock is required");
    die();
}

if($stock < 0){
    echo errormes("Invalid stock entry");
    die();
}

///////////------------------ update
$update = updatedb('o_assets', "stock = $stock", "uid=$asset_id"); 
if($update == 1){
    echo sucmes('Stock updated successfully.');
    $event = "Stock updated by ".$userd['name']."(".$userd['uid'].")";
    store_event('o_assets', $asset_id, "$event");
    $proceed = 1;
}
else{
    echo errormes('Unable to update stock'.$update.$asset_id);
}

?>


<script>
    if('<?php echo $proceed ?>'){
        setTimeout(function () {
            gotourl('assets.php?cat=asset&asset=<?php echo encurl($asset_id); ?>');
        }, 1500);

    }
</script>