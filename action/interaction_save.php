<?php
session_start();
include_once ("../php_functions/functions.php");
include_once("../php_functions/functions_v2.php");
include_once ("../configs/conn.inc");

$userd = session_details_v2();

$customer_id = $_POST['customer_id'];
$agent_id = $userd['uid'];
//$loan_id = 1;  ///////////////------------Current active loan
$transcript = urldecode($_POST['transcript']);
$conversation_method = $_POST['conversation_method'];
$conversation_date = $fulldate;
$next_interaction = $_POST['next_interaction'];
$default_reason = $_POST['default_reason'];
$next_steps = $_POST['next_steps'];
$flag = $_POST['flag'];
$conversation_purpose = $_POST['conversation_purpose'];
$promised_amount = $_POST['promised_amount'];
$status = 1;
$branch = 0;

$status_tag = "";
///////----------Validation
if(($customer_id > 0)){
    // $l = fetchmaxid("o_loans", "customer_id = $customer_id AND status > 0", "uid");
    $l = fetchmaxid_v2('o_loans',"customer_id = ? AND status > 0", [$customer_id], "uid, status");
    // $cust_d = fetchonerow('o_customers',"uid='$customer_id'","branch");
    $cust_d = fetchonerow_v2('o_customers',"uid = ?", [$customer_id], "branch, status");
    $branch = $cust_d['branch'];
    $loan_id = $l['uid'];
    $loan_status = $l['status'];
    $customer_status = $cust_d['status'];
    $status_tag = "CLIENT.".fetchrow('o_customer_statuses',"code='$customer_status'", "name");


    if($loan_id > 0){
        $loan_id = $l['uid'];
/////Tags, Interactions now have tags e.g. LEAD, ACTIVE, DEFAULTER etc
        $status_tag = "LOAN.".fetchrow('o_loan_statuses',"uid='$loan_status'",  "name");

    }else{
        $loan_id = 0;
    }
}
else{
    die(errormes("Please select customer"));
    exit();
}



if((input_length($transcript, 5)) == 0)
{
    die(errormes("Conversation details details too short"));
    exit();
}
if($conversation_method > 0){}
else{
    die(errormes("Conversation Method required"));
    exit();
}
if($flag > 0){}
else{
    die(errormes("Please select an outcome"));
    exit();
}
if($next_interaction == 0){
    die(errormes("Next Interaction date is Invalid".datediff3($next_interaction, $date)));
    exit();
}



//////-----------End of validation
$fds = array('customer_id', 'branch', 'agent_id', 'loan_id', 'transcript', 'conversation_method','conversation_date', 'next_interaction', 'next_steps','promised_amount','conversation_purpose','default_reason', 'flag','tag', 'status');
$vals = array($customer_id, $branch, $agent_id, $loan_id, "$transcript", $conversation_method,"$conversation_date","$next_interaction", $next_steps, "$promised_amount","$conversation_purpose","$default_reason",$flag, "$status_tag",$status);
// $create = addtodb('o_customer_conversations',$fds,$vals);
$create = addtodb_v2('o_customer_conversations', $fds, $vals);
if($create == 1)
{
    echo sucmes('Conversation Created Successfully');
    $proceed = 1;
    ///----Also update customer
    $update = updatedb('o_customers',"flag='$flag'","uid='$customer_id'");

}
else
{
    echo errormes("Unable Update Record $create");
}

?>

<script>
    if('<?php echo $proceed; ?>' === "1"){
        setTimeout(function () {

            modal_hide('#mainModal');

            // check if comments_list id exists before calling interactions_load
            if(document.getElementById('comments_list')){
                interactions_load('<?php echo encurl($customer_id); ?>','#comments_list');
            }

            if(document.getElementById('interactions_')){
                load_interactions()
            }

            if(document.getElementById('customer_interactions')){
                specific_customer_interactions()
            }

        },100);
    }
</script>
