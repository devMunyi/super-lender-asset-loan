<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

$permi = permission($userd['uid'],'o_users',"0","BLOCK");
if($permi != 1){
    exit(errormes("You don't have permission to block user"));
}

$member_id = $_POST['member_id'];

if($member_id > 0){
    $update = updatedb('o_users', "status=2", "uid= $member_id");
    if($update == 1)
    {
        echo sucmes('Success blocking member');
        $proceed = 1;
        $event = "Staff blocked by [".$userd['name']."(".$userd['email'].")] on [$fulldate]";
        store_event('o_users', $member_id,"$event");
    }
    else
    {
        die(errormes('Unable to delete member'));
        die();
    }
}else{
    die(errormes("Member ID invalid"));
    exit();
}

?>

<script>

    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
        	gotourl("staff?staff=<?php echo encurl($member_id); ?>");
        },2000);
    }
</script>







