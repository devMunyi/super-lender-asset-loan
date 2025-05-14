<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$customer = decurl($_POST['customer']);
$limit = "$offset_, $rpp_";
$offset_2 = $offset_ + $rpp_;
$limit2 = $offset_+$rpp_;
$rows = "";


$agent_names = table_to_obj('o_users',"uid > 0","1000000","uid","name");
$branch_names = table_to_obj('o_branches',"uid > 0","1000000","uid","name");
$outcome_names = table_to_obj('o_conversation_outcome',"uid > 0","10000","uid","name");
$conv_method_ = table_to_obj2('o_conversation_methods',"uid > 0","1000000","uid",array("name", "details"));
$sp_customer_name = fetchrow('o_customers',"uid='$customer'","full_name");
$o_flag_details = table_to_obj2('o_flags',"uid > 0","1000000","uid",array("name", "color_code"));


if($customer > 0) {
    //query for specific customer list
        $o_specific_conversations_ = fetchtable('o_customer_conversations',"customer_id = $customer AND status > 0", "uid", "desc", "0,20", "uid, customer_id, agent_id, loan_id, conversation_method, conversation_date, next_interaction, transcript, flag, next_steps, outcome");
        
        mysqli_num_rows($o_specific_conversations_) > 0 ? $row = "" : $row = "<tr><td colspan='8'><i>No Records Found</i></td></tr>";

        while ($sp_i = mysqli_fetch_array($o_specific_conversations_)) {
            $sp_uid = $sp_i['uid'];
            $sp_customer_id = $sp_i['customer_id'];

            $sp_agent_id = $sp_i['agent_id'];          $sp_agent_name = $agent_names[$sp_agent_id];
            $sp_loan_id = $sp_i['loan_id'];
            $sp_conversation_method = $sp_i['conversation_method'];  $sp_meth = $conv_method_[$sp_conversation_method];
            $sp_conversation_date = $sp_i['conversation_date'];
            $conversation_date_only = date("Y-m-d", strtotime($sp_conversation_date));
            $conversation_time_only = date("h:iA", strtotime($sp_conversation_date));
            $sp_next_interaction = $sp_i['next_interaction'];
            $sp_transcript = $sp_i['transcript'];
            $sp_flag = $sp_i['flag'];
            $flag_name = $o_flag_details[$sp_flag]['name'];
            $flag_color = $o_flag_details[$sp_flag]['color_code'];
            $flag_span = "<span class='font-13 font-bold' style='$flag_color'><i class='fa fa-flag'></i>$flag_name</span>";
            $sp_next_steps = $sp_i['next_steps'];
            $sp_outcome = $sp_i['outcome'];                        $sp_outc = $outcome_names[$sp_outcome];
                    
            $row = $row."<tr><td>$sp_uid</td>
            <td><span class=\"font-16\">$sp_customer_name <br/>$flag_span</span>
            </td>
            <td><i class=\"fa ".$sp_meth['details']."\"></i><br/><span class='font-13'>".$sp_meth['name']."</span></td>
            <td><span>$conversation_date_only</span><br/> <span class=\"text-muted font-12\">" . fancydate($conversation_date_only) . "</span><br><span class=\"text-blue font-400\">$conversation_time_only</span></td>
            <td>$sp_agent_name</td>
            <td><span>$sp_transcript</span></td>
            <td><span>$sp_next_interaction</span><br/> <span class=\"text-muted font-12\">".fancydate($sp_next_interaction)."</span></td>
            <td><span>Loan: $sp_loan_id</span><br/> <span class=\"text-muted font-12 font-bold\">Balance: $bal</span></td>
            <td><span><a class='pointer' onclick=\"view_interaction($sp_uid);\"><span class=\"fa fa-eye text-green\"></span></a></span></td>
            </tr>";
        }
}else {
    $row = "<tr><td colspan='8'><i>No Records Found</i></td></tr>";
}
echo trim($row)."<tr style = 'display: none'><td><input type='text' id='_alltotal_' value=\"$alltotal\"/></td><td><input type='text' id='_pageno_' value=\"$page_no\"></td></tr>";
include_once ("../../configs/close_connection.inc");
?>

