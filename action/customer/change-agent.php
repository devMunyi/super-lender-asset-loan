<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}

$cid = $_POST['cid'];
$agent_id = $_POST['agent_id'];
$agent_de = fetchonerow('o_users',"uid='$agent_id'","name, status, user_group");
$agent_name = $agent_de['name'];
$agent_status = $agent_de['status'];
$agent_group = $agent_de['user_group'];
if($cid < 1){
    die(errormes("Customer invalid"));
}

if($agent_id < 1){
    die(errormes("Agent invalid"));
}

if($agent_status != 1){
    die(errormes("Agent is inactive"));
}

echo json_encode($allowed_agent_groups);
if(isset($allowed_agent_groups)){
    if(!in_array($agent_group, $allowed_agent_groups)){
        die(errormes("Agent group not allowed"));
    }
}else {
    if($agent_group != 7){
        die(errormes("Agent is not an LO"));
    }
}



$agent_pair= fetchrow('o_pairing',"lo='$agent_id' AND status=1","co");
if($agent_pair < 1){
    $agent_pair = $agent_id;
}




/////-------------User is LO

$proceed = 0;



        $update_cust = updatedb('o_customers', "current_agent='$agent_id'", "uid='$cid'");
        if($update_cust == 1) {
            ////-----Change all customer loans
            $up = updatedb('o_loans',"current_lo='$agent_id', current_co='$agent_pair'","customer_id='$cid'");
            echo sucmes("Agent changed");
            $events = "Customer agent changed to $agent_name ($agent_id)";
            store_event('o_customers', $cid,"$events");
            $proceed = 1;
        }
        else{
            echo errormes("Error changing agent");
        }






?>

<script>

    if('<?php echo $proceed; ?>' === "1"){
        update_limit_popup('<?php echo $cid; ?>','EDIT');
        setTimeout(function (){
            reload();
        }, 2000);
    }
</script>
