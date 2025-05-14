<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

$gid = $_POST['gid'];
$cid = $_POST['cid'];
$staff_id = $userd['uid'];



///////////--------------------Validation
if($gid > 0){}
else
{
    die(errormes("Group not selected"));
    exit();
}
if($cid > 0){}
else{
    die(errormes("Customer not selected"));
    exit();
}


$customer_exists_other_group = checkrowexists('o_group_members',"customer_id='$cid' AND group_id!='$gid' AND status=1");
if($customer_exists_other_group == 1){
    die(errormes("Customer exists in another group"));
    exit();
}

$customer_exists = checkrowexists('o_group_members',"group_id='$gid' AND customer_id='$cid'");
if($customer_exists == 1){
   ///------Activate
    $save = updatedb('o_group_members',"status=1, added_date='$fulldate', added_by='$staff_id'","group_id='$gid' AND customer_id='$cid'");
}
else{
    $fds = array('group_id','customer_id','added_date','added_by','status');
    $vals = array("$gid","$cid","$fulldate","$staff_id","1");
    $save = addtodb('o_group_members', $fds, $vals);
}

if($save == 1){
    echo sucmes("Success adding member");
}
else{
    echo errormes("Error adding member");
}


?>

<script>

    if('<?php echo $save; ?>' === "1"){
        setTimeout(function () {
            group_members_list('<?php echo $gid; ?>');
            clear_search();
        },500);
    }
</script>
