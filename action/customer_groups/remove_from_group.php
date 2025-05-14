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



$customer_exists = checkrowexists('o_group_members',"group_id='$gid' AND customer_id='$cid'");
if($customer_exists == 1){
   ///------Activate
    $save = updatedb('o_group_members',"status=0, added_date='$fulldate', added_by='$staff_id'","group_id='$gid' AND customer_id='$cid'");
    if($save == 1){
        echo sucmes("Success removing member");
    }
    else{
        echo errormes("Error removing member");
    }
}
else{
    echo errormes("Member not found");
}




?>

<script>

    if('<?php echo $save; ?>' === "1"){
        setTimeout(function () {
           // group_members_list('<?php echo $gid; ?>');
            $('#<?php echo $gid.$cid; ?>').fadeOut('fast');
        },500);
    }
</script>
