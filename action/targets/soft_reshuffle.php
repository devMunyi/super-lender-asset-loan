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




/////---------End of session check

$group = $_POST['group'];



///////----------Validation
if(input_length($group, 2) == 0){
    echo errormes("Group not selected");
    exit();
}
$proceed = 0;

echo  sucmes("Hard refresh $group");
///------Move accounts among agents,
///

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
