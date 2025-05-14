<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");


$userd = session_details();
$pairing  = permission($userd['uid'],'o_team_leaders',"0","delete_");
if($pairing == 0) {
    die(errormes("You don't have permission remove agent"));
    exit();
}
$record_id = $_POST['record_id'];
/////----------Session Check

if($userd == null){
    echo errormes("Your session is invalid. Please re-login");
    exit();
}

/////---------End of session check
$remove = updatedb('o_team_leaders',"status=0","uid='$record_id'");
if($remove == 1){
    echo sucmes("Success! Removed");
}
else{
    echo errormes("Error removing agent");
}

///////----------Validation
if($record_id < 1){
    echo errormes("Record ID is required");
    exit();
}


?>

<script>

    if('<?php echo $remove; ?>' === "1"){
        setTimeout(function () {
          modal_hide();
            load_std('/jresources/staff/team-members.php','#team_members','leader=0');
        },1000);
    }
</script>
