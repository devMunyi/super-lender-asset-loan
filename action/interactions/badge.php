<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$client_id = $_POST['client_id'];
$badge = $_POST['badge'];


$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

///---Check permission to add or remove badge
$badge_action  = permission($userd['uid'], 'o_badges', "0", "create");
if ($badge_action == 0) {
    exit(errormes("You don't have permission to add/remove badges"));
}
/// -----

if($client_id > 0){
    $badge_name = fetchrow('o_badges',"uid=$badge","title");
    $upd = updatedb('o_customers',"badge_id='$badge'","uid='$client_id'");
    if($upd == 1){
        $event = "Customer badged with  $badge_name ($badge) by [".$userd['name']."(".$userd['email'].")]";
        store_event('o_customers', $client_id,"$event");
        if($badge > 0) {
            echo sucmes("Success adding badge $badge_name");
        }
        else{
            echo sucmes("Success removing badge");
        }

        $proceed = 1;
    }
    else{
        if($badge > 0) {
            echo errormes("Error adding badge $badge_name");
        }
        else{
            echo errormes("Error removing badge");
        }
    }
}

?>
<script>
    if ('<?php echo $proceed; ?>' === "1") {
        setTimeout(function() {
            gotourl("customers?customer=<?php echo encurl($client_id); ?>");
        }, 1500);
    }
</script>