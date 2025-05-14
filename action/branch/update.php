<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}


// permission check
$permi = permission($userd['uid'],'o_branches',"0","update_");
if($permi != 1){
    exit(errormes("You don't have permission to update branch!"));
}

$branch_id = intval($_POST['bid']);
$name = trim($_POST['name']);
$address = trim($_POST['address']);
$region_id = intval($_POST['region_id']);


///////----------Validation
if($branch_id > 0){
    $branch_id_dec = decurl($branch_id);
}else{
    exit(errormes("Branch ID is required"));
}

if((input_available($name)) == 0)
{
    exit(errormes("Name is invalid/required"));
}else {
    $branch_exists = checkrowexists('o_branches',"name='$name' AND uid !=$branch_id_dec");
    if($branch_exists == 1){
        exit(errormes("Branch Name $name Already Exists"));
    }
}

////////////-------------


$fds = array('name','address','region_id');
$vals = array("$name","$address","$region_id");

$updatefds = "name='$name', address='$address', region_id=$region_id";
$update = updatedb('o_branches',"$updatefds","uid=$branch_id_dec");
if($update == 1)
{
    echo sucmes('Record Updated Successfully');
    $event = "Branch Updated by [".$userd['name']."(".$userd['email'].")] on [$fulldate] with details. Name: $name, Region: $region_id, Address: $address";
    store_event('o_branches', $branch_id_dec, "$event");
    $proceed = 1;
}else{
    exit(errormes("Unable to Update Record: $update"));
    $proceed = 0;
}



?>

<script>

    if('<?php echo $proceed; ?>' == "1"){
        setTimeout(function () {
           gotourl("branches?branch=<?php echo $branch_id; ?>")
        },2000);
    }
</script>





