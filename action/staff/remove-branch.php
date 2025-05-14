<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
$rec_id = $_POST['rec_id'];



///////----------Validation
if($rec_id > 0){
    $staff_id = fetchrow('o_staff_branches',"uid='$rec_id'","agent");
  $upd = updatedb('o_staff_branches',"status=0","uid='$rec_id'");
    if($upd == 1){
        echo sucmes("Branch added");
        $proceed = 1;
    }
    else
    {
        echo errormes("Error adding branch");
    }
}
else
{
    die(errormes("Invalid record id"));
    exit();
}

?>

<script>

    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
            staff_branches('<?php echo encurl($staff_id); ?>');
        },100);
    }
</script>





