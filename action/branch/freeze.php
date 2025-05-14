<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}

$permi = permission($userd['uid'],'o_branches',"0","FREEZE");
if($permi != 1){
    exit(errormes("You don't have permission to freeze branch!"));
}

$branch_id_enc = intval($_POST['branch_id']);
$freeze  = $_POST['freeze'] ?? '';

if(empty($freeze)){
    exit(errormes("Freeze status required"));
}

if($branch_id_enc > 0){
    $branch_id = decurl($branch_id_enc);
    $update = updatedb('o_branches', "freeze='$freeze'", "uid= $branch_id");
    if($update == 1)
    {
        echo sucmes('Success freezing branch');
        $proceed = 1;
        $event = "Branch Freezed by [".$userd['name']."(".$userd['email'].")] on [$fulldate]";
        store_event('o_branches', $branch_id,"$event");
    }
    else
    {
        exit(errormes('Unable to freeze branch'));
    }
}else{
    exit(errormes("Branch ID invalid $branch_id_enc"));
}

?>

<script>

    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
        	gotourl("branches?branch=<?php echo $branch_id_enc; ?>");
        },2000);
    }
</script>







