<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();

if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}

$delete_permission  = permission($userd['uid'],'o_customer_guarantors',0,"delete_");
if($delete_permission != 1) {
    exit(errormes("You don't have permission to delete guarantor"));
}
$guarantor_id = intval($_POST['guarantor_id']);
if($guarantor_id > 0){
    $update = updatedb('o_customer_guarantors', "status=0", "uid=".decurl($guarantor_id));
    if($update == 1)
    {
        echo sucmes('Success deleting guarantor');
        $proceed = 1;

    }
    else
    {
        exit(errormes('Unable to delete guarantor'));
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







