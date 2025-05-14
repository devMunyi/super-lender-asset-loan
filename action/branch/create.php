<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}

// permission check
$permi = permission($userd['uid'],'o_branches',"0","create_");
if($permi != 1){
    exit(errormes("You don't have permission to create branch!"));
}

$name = trim($_POST['name']);
$address = trim($_POST['address']);
$region_id = intval($_POST['region_id']);


///////----------Validation
if((input_available($name)) == 0)
{
    exit(errormes("Name is invalid/required"));
}else {
    $name_exists = checkrowexists('o_branches',"name='$name'");
    if($name_exists == 1){
        exit(errormes("Branch Name $name Already Exists!"));
    }
}

////////////-------------

$fds = array('name','address','region_id', 'added_date');
$vals = array("$name","$address","$region_id", "$fulldate");
$create = addtodb('o_branches', $fds, $vals);
if($create == 1)
{
    echo sucmes('Record Created Successfully');
    $proceed = 1;
    $last_branch = fetchmax('o_branches',"name='$name'","uid","uid");
    $bid = $last_branch['uid'];

    $event = "Branch Created by [".$userd['name']."(".$userd['email'].")] on [$fulldate] with details. Name: $name, Region: $region_id, Address: $address";
    store_event('o_branches', $bid, "$event");

}else{
    exit(errormes('Unable to Save'.$create));
    $proceed = 0;
}



?>

<script>

    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
          gotourl("branches?branch=<?php echo encurl($bid); ?>")
        },2000);
    }
</script>





