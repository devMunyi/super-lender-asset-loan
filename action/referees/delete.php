<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();

if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}

$delete_permission  = permission($userd['uid'],'o_customer_referees',0,"delete_");
if($delete_permission != 1) {
    exit(errormes("You don't have permission to delete referee"));
}
$ref_id = $_POST['ref_id'];
if($ref_id > 0){
    $update = updatedb('o_customer_referees', "status=0", "uid=".decurl($ref_id));
    if($update == 1)
    {
        echo sucmes('Success deleting referee');
        $proceed = 1;

    }
    else
    {
        echo errormes('Unable to delete referee');
    }
}
else{
    exit(errormes("Document Id invalid"));
}

?>

<script>

    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
        reload();
        },400);
        other_list('o_customers','<?php echo $_POST['record']; ?>','EDIT');
    }
</script>







