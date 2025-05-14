<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");




$userd = session_details();
$pairing  = permission($userd['uid'],'o_pairing',"0","delete_");
if($pairing == 0) {
    die(errormes("You don't have permission break pair"));
    exit();
}

/////----------Session Check
$userd = session_details();
if($userd == null){
    echo errormes("Your session is invalid. Please re-login");
    exit();
}
/////---------End of session check
$pid = $_POST['pid'];
$pair_branch = fetchrow('o_pairing',"uid='$pid'","branch");


///////----------Validation
if($pid < 1){
    echo errormes("Invalid PID");
    exit();
}


$proceed = updatedb('o_pairing', "status=0", "uid=$pid");
if($proceed == 1){
    echo sucmes("Pair broken successfully");
}
else{
    echo errormes("Error breaking pair");
}

?>

<script>

    if('<?php echo $proceed; ?>' == "1"){
        setTimeout(function () {
            load_std('/extensions/sp-pairing-1.php','#dynamic_load','b=<?php echo encurl($pair_branch);  ?>');
        },1000);
    }
</script>
