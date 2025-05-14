<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

/////-------Permission
$addon_action = permission($userd['uid'],'o_loan_addons',"0","update_");
if($addon_action != 1){
    die(errormes("You don't have permission to edit addon value"));
    exit();
}
/////--------


$addon_id = $_POST['uid'];
$addon_amount = $_POST['amount'];



if($addon_id > 0){

    $loan_id = fetchrow('o_loan_addons',"uid='$addon_id'","loan_id");
    $addon = fetchrow('o_loan_addons',"uid='$addon_id'","addon_id");
    $addon_details = fetchonerow('o_addons',"uid='$addon'","name");
}
else{
    die(errormes("Invalid Addon Id"));
    exit();
}

  $loan_id = fetchrow('o_loan_addons',"uid='$addon_id'","loan_id");

    $update = updatedb('o_loan_addons',"addon_amount=$addon_amount, added_date='$fulldate'","uid='$addon_id'");
    if($update == 1){
        echo sucmes("Amount updated");
        recalculate_loan($loan_id, true);
        $proceed = 1;
        store_event('o_loans', $loan_id,"Amount for addon [".$addon_details['name']."($addon)] updated to $addon_amount by [".$userd['name']."(".$userd['email'].")]");

    }
    else{
        echo errormes("Unable to update amount".$update);
    }


?>

<script>
    if('<?php echo $proceed ?>'){
        modal_hide();
        loan_addons('<?php echo encurl($loan_id); ?>');

    }
</script>
