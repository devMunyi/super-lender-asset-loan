<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
/////---------End of session check
$cid = $_POST['cid'];
$addon = $_POST['addon'];
$addon_amount = $_POST['addon_amount'];
$deduction = $_POST['deduction'];


if($cid < 1){
    die(errormes("Campaign ID invalid"));
}

$campaign_id = decurl($cid);
$camp = fetchonerow('o_campaigns',"uid='$campaign_id'","running_status");
if($camp['running_status'] > 1){
    die("The campaign is already running");
}

if($addon < 1){
    die(errormes("Please select and addon"));
}

if($addon_amount > 100 || $addon_amount < 1){
    die(errormes("Please enter a valid addon amount"));
}
if($deduction < 1){
    die(errormes("Please enter a valid deduction"));
}

///----Campaign already run check




$upd = updatedb('o_campaigns',"waiver_addon='$addon', waiver_amount='$addon_amount',waiver_deduction='$deduction'","uid='$campaign_id'");
if($upd == 1){
    echo sucmes("Waivers applied");
    $proceed = 1;
}
else{
    echo errormes("Error updating waivers");
}






mysqli_close($con);

?>


<script>
    if('<?php echo $proceed ?>'){
        setTimeout(function () {
            gotourl('broadcasts?campaign=<?php echo $cid ; ?>');
        }, 1500);

    }
</script>