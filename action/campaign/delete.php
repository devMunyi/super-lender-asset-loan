<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
}

$campaign_id = $_POST['campaign_id'];

if($campaign_id > 0){

    //check if campaign is past and already run
    $exists = checkrowexists("o_campaigns", "uid = $campaign_id AND status = 1 AND running_status > 1");
    if($exists == 1){
        die(errormes("Campaign running or sent cannot be deleted"));
    }

    $update = updatedb('o_campaigns', "status=0", "uid= $campaign_id");
    if($update == 1)
    {
        echo sucmes('Success deleting campaign');
        $proceed = 1;


        // store event 
        $event = "Campaign Deleted by [".$userd['name']."(".$userd['email'].")].";
        store_event('o_campaigns', $campaign_id, $event, 1);

    }else{
        die(errormes('Unable to delete campaign'));
    }
}else{
    die(errormes("Campaign ID invalid"));
}

?>

<script>

    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
        	gotourl("broadcasts");
        },2000);
    }
</script>







