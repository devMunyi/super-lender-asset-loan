<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
$contact_id = $_POST['contact_id'];
////----------------
$customer_id = fetchrow('o_customer_contacts',"uid=".decurl($contact_id),"customer_id");
$customer_status = fetchrow('o_customers',"uid='$customer_id'","status");
if($customer_status == 1){
    //----Check permissions
    $delete_contact = permission($userd['uid'],'o_customer_contacts',"0","delete_");
    if($delete_contact != 1){
        die(errormes("You don't have permission to delete contact"));
        exit();
    }

}
////----------------


if($contact_id > 0){
    $update = updatedb('o_customer_contacts', "status=0", "uid=".decurl($contact_id));
    if($update == 1)
    {
        echo sucmes('Success deleting contact');
        $proceed = 1;
    }
    else
    {
        die(errormes('Unable to delete contact'));
        exit();
    }
}
else{
    die(errormes("Contact ID invalid"));
    exit();
}

?>

<script>

    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {
        reload();
        },400);

        //$('#cont<?php echo $contact_id; ?>').fadeOut('fast');
    }
</script>







