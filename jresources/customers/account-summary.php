<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");


$mobile = $_POST['phone'];
if (validate_phone($mobile) == 1) {

    $cust = fetchonerow('o_customers',"primary_mobile='$mobile'","uid, full_name, physical_address, total_loans, current_agent, branch, loan_limit, status");
    $uid = $cust['uid'];

  //  $latest_photo = fetchrow('o_documents',"category='1' AND status=1 AND tbl='o_customers' AND rec='$uid'","stored_address");
    $passport_photo = fetchrow('o_documents', "category='1' AND tbl='o_customers' AND rec=$uid AND status=1", "stored_address");
    if (!$passport_photo) {
        $profile = "<img src='custom_icons/profile.png' class='img-bordered' width='200px'>";
    } else {

        $img = locateImageServer($passport_photo);
        $profile = "<img src='$img' class='img-bordered' width='200px'>";
    }



    $latest_interaction = fetchmax('o_customer_conversations',"customer_id='$uid'","uid, transcript, conversation_date");
    if($latest_interaction['uid'] > 0){
        $latest_interaction_text = $latest_interaction['transcript'];
        $latest_interaction_date = $latest_interaction['conversation_date'];
        $latest_int = "$latest_interaction_text [$latest_interaction_date]";

    }
    else{
        $latest_int = "<i>No interactions</i>";
    }

    $full_name = $cust['full_name'];
    $physical_address = $cust['physical_address'];
    $current_agent = $cust['current_agent'];
    $branch = $cust['branch'];
    $loan_limit = $cust['loan_limit'];
    $status = $cust['status'];

    $latest_loan = fetchmaxid('o_loans',"customer_id=$uid","uid, loan_amount, loan_balance, status, given_date, final_due_date, current_agent, current_lo, current_co");
    $loan_balance = $latest_loan['loan_balance'];
    $luid = $latest_loan['uid'];



    if($luid > 0) {
        $final_due_date = $latest_loan['final_due_date'];
        $loan_status = $latest_loan['status'];
        $total_loans = $cust['total_loans'];
        $current_agent = $latest_loan['current_agent'];
        $current_co = $latest_loan['current_co'];
        $current_lo = $latest_loan['current_lo'];

        $agent_names = table_to_obj('o_users',"uid='$current_agent' OR uid='$current_lo' OR uid='$current_co' ",10,'uid',"full_name");
        $loan_status_details = fetchonerow('o_loan_statuses', "uid='$loan_status'", "name, color_code");
        $loan_status_name =  $loan_status_details['name'];
        $color_code = $loan_status_details['color_code'];
        $latest_loan_details = "Balance: &emsp; <b>".number_format($loan_balance)."</b> <br/> Status: &emsp; <span class=\"label custom-color\" style=\"background-color: $color_code;\">$loan_status_name</span> <br/> Due: &emsp; <span class='font-italic font-bold'>$final_due_date</span>";

        $lo_name = $agent_names[$current_lo];
        $co_name = $agent_names[$current_co];
        $collector_name = $agent_names[$current_agent];

        $agents = "<b>LO:</b>&emsp; $lo_name <b>CO: &emsp;</b> $co_name <b>COLL:</b>&emsp; $collector_name";


    }
    else{
        $latest_loan_details = "<i>No loan</i>";
        $agent_name = fetchrow('o_users',"uid='$current_agent'","name");
        $agents = "<b>Current Agent:</b> $agent_name";
    }


    echo "<table class='table table-striped'>";
    echo "<tr><td rowspan='7'>$profile </td><td colspan='2' class='font-bold font-18'>$full_name <a class='pull-right font-13' title='Go to profile' href=\"customers?customer=".encurl($uid)."\">Full Profile<i class='fa fa-external-link'></i></a> </td><td></td></tr>";
    echo "<tr><td>Phone</td><td class='font-18'> $mobile <a onclick=\"copyTextToClipboard('$mobile')\" class='font-bold font-16'><i class='fa fa-copy'></i></a></td></tr>";
    echo "<tr><td>Loan Limit</td><td>$loan_limit</td></tr>";
    echo "<tr><td>Address</td><td> $physical_address</td></tr>";
    echo "<tr><td>Latest Loan</td><td>$latest_loan_details</td></tr>";
    echo "<tr><td>Agents</td><td>$agents</td></tr>";
    echo "<tr><td>Total Loans</td><td>$total_loans</td></tr>";

    echo "</table>";

    ?>


<?php

    echo "<table class='table table-striped table-condensed'>";
    echo "<tr><td><button style='display: block;' id='start_call_btn' class='btn btn-default font-bold' onclick='call_client($mobile,$uid)'><img src=\"custom_icons/call32.png\"> Call</button> <button id='end_call_btn' class='btn btn-danger font-bold' style='display: none;' onclick='end_call()'><img src=\"custom_icons/call32.png\"> End Call</button></td>
<td><button class='btn btn-default font-bold' title='SMS client'><img src=\"custom_icons/message32.png\"> SMS</button></td><td>
<button class=\"btn btn-warning font-bold\" onclick=\"make_call($mobile,$uid)\"><img src=\"custom_icons/call32.png\"> Call(V2)</button>
</td><td>
<button class='btn btn-default font-bold' title='Create Interaction' disabled><img src=\"custom_icons/interact32.png\"> Interact</button></td>
</tr>";
    ?>
      <tr><td colspan="4"><div id="call_logs" class="alert font-bold" style="background-color: #0a0a0a; color: #8CD0D3"></div></td></tr>
         <?php

    echo "</table>";

    echo "<hr/>";
    echo "<div class=\"box-comment comment-text well well-sm\">$latest_int</div>";
} else {
    echo errormes("Unable to load account data");
}
include_once("../../configs/close_connection.inc");

?>

