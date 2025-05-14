<?php
session_start();
include_once("../../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();

$customer = decurl($_POST['cid']);
if ($customer < 1) {
    $customer = decurl($_GET['cid']);
}



if ($customer > 0) {
    $andcustomer = " AND customer_id='$customer'";
} else {
    die("No customer selected");
}

//-----------------------------Reused Query


//default or all interactions
$o_conversations = fetchtable('o_customer_conversations', "status > 0 $andcustomer", "uid", "desc", "20", "uid, customer_id, agent_id, loan_id, conversation_method, conversation_date, next_interaction, transcript, flag, next_steps, outcome");
///----------Paging Option
$alltotal = mysqli_num_rows($o_conversations);

$agent_names = table_to_obj('o_users', "uid > 0", "100000000", "uid", "name");
$branch_names = table_to_obj('o_branches', "uid > 0", "1000000", "uid", "name");
$outcome_names = table_to_obj('o_conversation_outcome', "uid > 0", "10000", "uid", "name");
$conv_method_ = table_to_obj2('o_conversation_methods', "uid > 0", "1000000", "uid", array("name", "details"));
$customer_name = fetchrow('o_customers', "uid='$customer'", "full_name");
//$loan_bal = fetchmaxid('o_loans',"")



if ($alltotal > 0) {
    $row = "";
    while ($i = mysqli_fetch_array($o_conversations)) {
        $uid = $i['uid'];
        $customer_id = $i['customer_id'];   //$customer_name = fetchrow('o_customers',"uid='$customer_id'","full_name");

        $agent_id = $i['agent_id'];
        $agent_name = $agent_names[$agent_id];
        $loan_id = $i['loan_id'];
        $conversation_method = $i['conversation_method'];
        $meth = $conv_method_[$conversation_method];
        $conversation_date = $i['conversation_date'];
        $conversation_date_only = date("Y-m-d", strtotime($conversation_date));
        $conversation_time_only = date("h:iA", strtotime($conversation_date));
        $next_interaction = $i['next_interaction'];
        $transcript = $i['transcript'];
        $flag = $i['flag'];
        $next_steps = $i['next_steps'];
        $outcome = $i['outcome'] ?? 0;
        $outc = $outcome_names[$outcome];
        $loan_bal = 0;


        $row = $row . "<tr><td>$uid</td>
                    
                    <td><i class=\"fa " . $meth['details'] . "\"></i><br/><span class='font-13'>" . $meth['name'] . "</span></td>
                    <td><span>$conversation_date_only</span><br/> <span class=\"text-muted font-13\">" . fancydate($conversation_date_only) . "</span><br><span class=\"text-blue font-400\">$conversation_time_only</span></td>
                    <td>$agent_name</td>
                    <td><span>$transcript</span></td>
                    <td><span>$next_interaction</span><br/> <span class=\"text-muted font-12\">" . fancydate($next_interaction) . "</span></td>
                     <td><span>Loan ID: <span class=\"text-bold\">$loan_id</span></span><br/> <span class=\"text-muted font-12 font-bold\">Balance: <span class=\"text-danger\">" . money($loan_bal) . "</span></span></td>
                     <td><span title =\"click to view this interaction's details\"><a class='pointer' onclick=\"view_interaction($uid);\"><span class=\"fa fa-eye text-green\"></span></a></span></td>
                   
                    </tr>";

        //////------Paging Variable ---
        //$page_total = $page_total + 1;
        /////=======Paging Variable ---

    }
} else {
    $row = "<tr><td colspan='7'><i>No Records Found</i></td></tr>";
}
?>
<table class="table table-striped table-hover table-condensed">
    <thead>
        <tr>
            <th>ID</th>
            <th>Interaction Mode</th>
            <th>Date</th>
            <th>Agent</th>
            <th>Outcome</th>
            <th>Next Interaction</th>
            <th>Loan ID</th>
            <th>Act</th>


        </tr>
    </thead>
    <?php
    echo $row;

    ?>
</table>

<button class="btn btn-success bg-green-gradient" onclick="modal_view('/forms/interaction_add_form.php','customer_id=<?php echo $customer; ?>','New Interaction'); modal_show();"><i class="fa fa-plus"></i> New Interaction</button>
<a href="interactions?customer=<?php echo encurl($customer); ?>">Go to all interactions.</a>
<?php

include_once("../../configs/close_connection.inc");
?>