<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$iid = $_POST['iid'];
if($iid > 0){
    $u = fetchonerow("o_customer_conversations","uid='$iid' AND status=1","*");
    $uid = $u['uid'];
    $customer_id = $u['customer_id']; $customer_name = fetchrow('o_customers',"uid='$customer_id'","full_name");
    $agent_id = $u['agent_id'];  $agent_name = fetchrow('o_users',"uid='$agent_id'","name");
    $loan_id = $u['loan_id'];
    $transcript = $u['transcript'];
    $conversation_method = $u['conversation_method'];   $meth = fetchonerow('o_conversation_methods',"uid='$conversation_method'","name, details");
    $conversation_date = $u['conversation_date'];
    $next_interaction = $u['next_interaction'];
    $promised_amount = $u['promised_amount'];
    $default_reason = $u['default_reason'];
    $default_r = fetchrow('o_default_reasons',"uid='$default_reason'","name") ?? 'N/A';
    $conversation_purpose = $u['conversation_purpose'];    $purpose = fetchonerow('o_conversation_purpose',"uid='$conversation_purpose'","name");
    $next_steps = $u['next_steps'];
    $tag = $u['tag'];
    $flag = $u['flag'];      $flag_d = flag($flag);

    $outcome = $u['outcome']; $outc = fetchrow('o_conversation_outcome',"uid='$outcome'","name");
    $status = $u['status'];

    if(input_length($tag, 1) == 1){
        $t = explode('.',$tag);
        $typ = $t[0];
        $dt = $t[1];
        $tag_det = "<label class='label label-default'>$dt($typ)</label>";
    }
    else{
        $tag_det = "";
    }

    echo "<table class='table table-bordered'>";
        echo "<tr><td>ID</td><td>$uid</td></tr>";
        echo "<tr><td>Customer</td><td>$customer_name</td></tr>";
        echo "<tr><td>Agent</td><td>$agent_name</td></tr>";
        echo "<tr><td>Loan ID</td><td>$loan_id</td></tr>";
        echo "<tr><td>Details</td><td>$transcript</td></tr>";
        echo "<tr><td>Conversation Method</td><td>".$meth['name']."</td></tr>";
        echo "<tr><td>Conversation Purpose</td><td>".$purpose['name']."</td></tr>";
        echo "<tr><td>Conversation Outcome</td><td>".$flag_d."</td></tr>";
        echo "<tr><td>Tag</td><td>".$tag_det."</td></tr>";
        echo "<tr><td>Default Reason</td><td>".$default_r."</td></tr>";
        echo "<tr><td>Promised Amount</td><td>".$promised_amount."</td></tr>";
        echo "<tr><td>Conversation Date</td><td>$conversation_date ".fancydate($conversation_date)."</td></tr>";
        echo "<tr><td>Next Interaction</td><td>$next_interaction".fancydate($next_interaction)."</td></tr>";
        echo "<tr><td>Next Steps</td><td>".next_step($next_steps)."</td></tr>";
       // echo "<tr><td>Flag</td><td>$flag_d</td></tr>";

    echo "</table>";

}
else{
    echo errormes("Interaction Invalid");
}