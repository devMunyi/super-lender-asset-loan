<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
}

$email = trim($_POST['email']);
$action =  strtolower(trim($_POST['action']));

if(empty($email)){
    exit(errormes("Username is required!"));
}

if(!in_array($action, ['enable', 'disable'])){
    exit(errormes("Invalid action!"));
}

$act_permi = ($action == 'enable') ? 'ENABLE_2FA' : 'DISABLE_2FA';
$permi = permission($userd['uid'], 'o_users', "0", "$act_permi");
if($permi != 1){
    exit(errormes("You don't have permission to $action user 2FA!"));
}


$user_det = fetchmaxid("o_users", "email='$email'", "uid, two_fa_enforced");

if(empty($user_det['uid'])){
    exit(errormes("User not found"));
}

$two_fa_enforced = $user_det['two_fa_enforced'];
if($action == 'enable' && $two_fa_enforced == 1){
    exit(errormes("2FA is already enabled for this user!"));
}


if($action == 'disable' && $two_fa_enforced == 0){
    exit(errormes("2FA is already disabled for this user!"));
}


$two_fa_enforced = ($action == 'enable') ? 1 : 0;
$update = updatedb('o_users', "two_fa_enforced=$two_fa_enforced", "uid= ".$user_det['uid']);

$actioned = ($action == 'enable') ? 'Enabled' : 'Disabled';

if($update == 1)
{
    echo sucmes("Succcess! 2FA $actioned.");
    $proceed = 1;
    $event = "2FA ". $actioned ." by [".$userd['name']."(".$userd['email'].")";
    store_event('o_users', $user_det['uid'],"$event");
}
else
{
    exit(errormes("Could not $action 2FA!"));
}

?>

<script>

    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
        	gotourl("staff?staff=<?php echo encurl($user_det['uid']); ?>");
        },2000);
    }
</script>