<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");
$file_id = $_POST['file_id'];
$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}

$customer_id = fetchrow('o_documents',"uid='".decurl($file_id)."'","rec");
$customer_status = fetchrow('o_customers',"uid='$customer_id'","status");



if($customer_status == 3){
    ////-----If its a lead, check if a user can save a lead, then permit them to delete a photo
    $update_addon = permission($userd['uid'],'o_customers',"0","create_");
    if($update_addon != 1){
        exit(errormes("You don't have permission to delete a file"));
    }
}else {
    $delete_perm  = permission($userd['uid'],'o_documents',"0","delete_");
    if($delete_perm != 1) {
        exit(errormes("You don't have permission to delete a file"));
    }
}








////////////////------------------Files are not really deleted,

if($file_id > 0){
    $update = updatedb('o_documents', "status=0", "uid=".decurl($file_id));
    if($update == 1)
    {
        echo sucmes('Success deleting file');
        $proceed = 1;

    }
    else
    {
        echo errormes('Unable to delete file');
    }
}
else{
    die(errormes("File Id invalid"));
    exit();
}

?>

<script>
    if('<?php echo $proceed; ?>'){
        setTimeout(function () {
            $('#fil<?php echo $file_id; ?>').fadeOut('fast');
            modal_hide();
            reload();
        },400);
        
    }
</script>







