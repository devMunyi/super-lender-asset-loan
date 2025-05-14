<?php
session_start();

include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
$added_by = intval($userd['uid']);
$tbl = $_POST['tbl'];
$fds = $_POST['fds'];
$customer_id = decurl($_POST['record']);
$sec = [];
$vals = explode(',',$fds);

for($i=0; $i<sizeof($vals); ++$i){
    $v = $vals[$i];
    $input = urldecode($_POST['in_'.$vals[$i]]);

   ////----We have both field id and field name
    $sec[$v] = stripslashes($input);
        ////----Create

}

$cust = fetchonerow('o_customers',"uid='$customer_id'","status, primary_product");

$customer_status = $cust['status'] ?? 0;
$primary_product = $cust['primary_product'] ?? 0;

$state = fetchonerow('o_customer_statuses',"code='$customer_status'","name");
$status_name = $state['name'];

////-----Check if a user has permission to update a customer when in specific status
$update_customer_in_status = permission($userd['uid'],'o_customer_statuses',"$customer_status","update_");
if($update_customer_in_status != 1){
    exit(errormes("You can not update a customer who is $status_name"));
}

$sec_ = sanitizeAndEscape(json_encode($sec), $con);
$save = updatedb('o_customers',"sec_data=\"$sec_\"","uid=$customer_id");

if($save == 1){
    $proceed = 1;
    $user_phone = $userd['phone'] ?? 0;
    $user_national_id = $userd['national_id'] ?? 0;
    $user_id = $userd['uid'] ?? 0;
    $user_name = $userd['name'] ?? 0;
    echo sucmes("Success saving data ");
    store_event('o_customers', $customer_id, "Customer sec_data field updated by $user_name($user_id) [Phone: $user_phone, National ID: $user_national_id]");


    /////-------Check the after save script
    $scr = after_script($primary_product, 'SEC_UPDATE');
    if($scr !== 0){
        include_once "../../$scr";
    }

    ////-------End of check after save script
}
else{
    echo errormes("Error saving data $save");
}




?>
<script>
    if("<?php echo $proceed; ?>"){
        setTimeout(function () {
            reload();
        },2000);
        //other_list('o_customers','<?php // echo $_POST['record']; ?>','EDIT');
        //clear_form('other_frm');
    }
</script>
