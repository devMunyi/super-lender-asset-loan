<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");


$userd = session_details();
$pairing  = permission($userd['uid'],'o_team_leaders',"0","create_");
if($pairing == 0) {
    die(errormes("You don't have permission add team members"));
    exit();
}

/////----------Session Check

if($userd == null){
    echo errormes("Your session is invalid. Please re-login");
    exit();
}
$userid = $userd['uid'];
/////---------End of session check

$lo = $_POST['lo'];
$co = $_POST['co'];


///////----------Validation
if($lo < 1){
    echo errormes("Team leader is required");
    exit();
}
if($co < 1){
    echo errormes("Member is required");
    exit();
}
if($lo == $co){
    echo errormes("Please select different users");
    exit();
}



////-----Check existing
$manager_pair = fetchonerow('o_team_leaders',"leader_id='$lo' AND agent_id='$co'","uid, status");
if($manager_pair['uid'] > 0){
    ///---Already added
    if($manager_pair['status'] == 1){
        echo sucmes("Already added");
    }
    else{
        $upd = updatedb('o_team_leaders',"status=1, agent_id='$userid', added_date='$fulldate'","uid=".$manager_pair['uid']);
        if($upd == 1){
            echo sucmes("Added");
            $proceed = 1;
        }
        else{
            echo errormes("Not added");
        }

    }

}
else
{
    ///---Not added
    $fds = array('leader_id','agent_id','added_by','added_date','status');
    $vals = array("$lo","$co","$userid","$fulldate","1");
    $create = addtodb('o_team_leaders',$fds, $vals);
    if($create == 1){
        echo sucmes("Added");
        $proceed = 1;
    }
    else{
        echo errormes("Not added");
    }
}

?>

<script>

    if('<?php echo $proceed; ?>' == "1"){
        setTimeout(function () {
         // modal_hide();
            load_std('/jresources/staff/team-members.php','#team_members','leader=<?php echo encurl($lo); ?>');
        },1000);
    }
</script>
