<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if($userd == null){
    echo errormes("Your session is invalid. Please re-login");
    exit();
}

$groups = array('FA','IDC','CC','EDC');


/////---------End of session check

$group = $_POST['group'];

if(!in_array($group, $groups)){
    die(errormes("Please select a group"));
}

///////----------Validation
if(input_length($group, 2) == 0){
    echo errormes("Group not selected");
    exit();
}
$proceed = 0;


///------We un-allocate all,
///
$total = countotal('o_loans',"allocation='$group' AND disbursed=1 AND paid=0 AND status!=0","uid");
$upd = updatedb('o_loans',"current_agent=1","allocation='$group' AND disbursed=1 AND paid=0 AND status!=0");
if($upd == 1){
    echo sucmes("Hard reshuffle for $total $group accounts successful");
    $proceed = 1;
}
else{
    echo errormes("Error doing a hard reshuffle");
}
///
/// We re-allocate all

?>

<script>

    if('<?php echo $proceed; ?>' == "1"){
        setTimeout(function () {
            reload();
        },1000);
    }
</script>
