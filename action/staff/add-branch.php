<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}
$staff_id = $_POST['aid'];
$dec_staff_id = decurl($staff_id);
$branch = $_POST['branch'];


///////----------Validation
if($dec_staff_id > 0){

}
else
{
    die(errormes("Staff ID is required"));
    exit();
}

if($branch == 0)
{
    die(errormes("Branch required"));
    exit();
}


    $branch_det = fetchonerow('o_staff_branches',"agent='$dec_staff_id' AND branch=$branch","uid, status");
    if($branch_det['uid'] > 0) {
        if($branch_det['status'] == 1){
            echo errormes("Branch exists");
        }
        else{
            $upd = updatedb('o_staff_branches',"status=1,  added_date='$fulldate'","uid='".$branch_det['uid']."'");
            if($upd == 1){
                echo sucmes("Branch added");
                $proceed = 1;
            }
            else
            {
                echo errormes("Error adding branch");
            }
        }


    }
    else{
        ///-----Create
        $fds = array('agent','branch','added_date','status');
        $vals = array("$dec_staff_id","$branch","$fulldate","1");
        $add = addtodb('o_staff_branches',$fds, $vals);
        if($add == 1){
            echo sucmes("Branch added");
            $proceed = 1;
        }
        else
        {
            echo errormes("Error adding branch");
        }

    }

?>

<script>

    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
         staff_branches('<?php echo $staff_id; ?>');
        },100);
    }
</script>





