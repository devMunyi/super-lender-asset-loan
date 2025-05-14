<?php
$agents = table_to_obj('o_users',"status=1","100000","uid","name");
$branches = table_to_obj('o_branches',"status=1","100000","uid","name");

//$conversation_methods_p

$conversations = fetchtable('o_customer_conversations',"status= 1 AND conversation_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'","uid","asc","100000000","uid, branch, agent_id, loan_id, conversation_method, next_steps, flag, outcome");
while($c = mysqli_fetch_array($conversations)){
    $uid = $c['uid'];
    $branch = $c['branch'];
    $agent_id = $c['agent_id'];
    $loan_id = $c['loan_id'];
    $conversation_method = $c['conversation_method'];
    $next_steps = $c['next_steps'];
    $flag = $c['flag'];
    $outcome = $c['outcome'];




}

?>
<table class="table table-condensed table-striped" id="example2">
    <thead>
    <tr><th>UID</th>
        <th>Agent Name</th>
        <th>Branch</th>
        <th>Conversation Method</th>
        <th>Next Steps</th>
        <th>Flag</th>
        <th>Outcome</th>
    </tr>
    </thead>
    <tbody>
    <?php
    foreach ($agents as $agent_id => $agent_name) {
       // $branch_name  = $branches[];
       echo " <tr><td>$agent_id</td>
        <td>$agent_name</td>
        <td>$branch_name</td>
        <td>Conversation Method</td>
        <td>Next Steps</td>
        <td>Flag</td>
        <td>Outcome</td>
    </tr>";
    }
    ?>
    </tbody>
</table>
