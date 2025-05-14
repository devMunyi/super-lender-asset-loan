<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");


$userd = session_details();
$pairing  = permission($userd['uid'],'o_pairing',"0","create_");
if($pairing == 0) {
    die(errormes("You don't have permission pair"));
    exit();
}

/////----------Session Check
$userd = session_details();
if($userd == null){
    echo errormes("Your session is invalid. Please re-login");
    exit();
}
/////---------End of session check

$lo = $_POST['lo'];
$co = $_POST['co'];


///////----------Validation
if($lo < 1){
    echo errormes("LO is required");
    exit();
}
if($co < 1){
    echo errormes("CO is required");
    exit();
}
if($lo == $co){
    echo errormes("Please select different users");
    exit();
}

$lo_already_paired = checkrowexists('o_pairing',"lo='$lo' AND status=1");
$co_already_paired = checkrowexists('o_pairing',"co='$co' AND status=1");
$lo_branch = fetchrow('o_users',"uid='$lo'","branch");
$co_branch = fetchrow('o_users',"uid='$co'","branch");
if($lo_branch != $co_branch){
    echo errormes("Users not in the same branch");
    exit();
}

if($lo_already_paired == 1){
    echo errormes("This LO already has a pair");
    exit();
}
if($co_already_paired == 1){
    echo errormes("This CO already has a pair");
    exit();
}


$fds = array('lo','co','branch','paired_date','status');
$vals = array("$lo","$co","$lo_branch","$fulldate","1");
$proceed = addtodb('o_pairing', $fds, $vals);
if($proceed == 1){
    echo sucmes("Staff paired successfully");
}
else{
    echo errormes("Error pairing staff");
}

?>

<script>

    if('<?php echo $proceed; ?>' == "1"){
        setTimeout(function () {
          modal_hide();
            load_std('/extensions/sp-pairing-2.php','#dynamic_load','b=<?php echo encurl($lo_branch); ?>');
        },1000);
    }
</script>
