<?php
session_start();
include_once("../../configs/20200902.php");
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();

$where_ =  $_POST['where_'] ?? '';
$offset_ =  $_POST['offset'] ?? 0;
$rpp_ =  $_POST['rpp'] ?? 10;
$page_no = $_POST['page_no'] ?? 1;
$orderby =  $_POST['orderby'] ?? "uid";
$dir =  $_POST['dir'] ?? "DESC";
$search_ = trim($_POST['search_'] ?? '');
$customer = $_POST['customer'] ?? 0;
$limit = "$offset_, $rpp_";
$cust_array = array();
$andsearch = "";
$noRows = noRowSpan(9);
$rows = "";

/////-------Filter for branches
$read_all = permission($userd['uid'], 'o_customer_conversations', "0", "read_");

if ($read_all == 1) {
    $anduserbranch  = "";
    $andagent = "";
    $andbranch = "";
} else {
    $user_branch = $userd['branch'];
    $anduserbranch = " AND branch='$user_branch' ";

    //////-----Check users who view multiple branches
    $staff_branches = table_to_array('o_staff_branches', "agent=" . $userd['uid'] . " AND status=1", "1000", "branch", "uid", "asc");
    if (sizeof($staff_branches) > 0) {
        ///------Staff has been set to view multiple branches
        if(intval($userd['branch']) > 0){
            array_push($staff_branches, $userd['branch']);
        }
        
        // remove duplicates
        $staff_branches = array_unique($staff_branches);
        $staff_branches_list = implode(",", $staff_branches);
        $anduserbranch = " AND branch in ($staff_branches_list)";
    }
}


if (input_available($search_) == 1) {

    ////--------By customer
    $customers = fetchtable("o_customers", "full_name LIKE '%$search_%' $anduserbranch", "uid", "asc", 20, "uid");

    $customer_lists = mysqli_num_rows($customers);
    if ($customer_lists > 0) {
        while ($cu = mysqli_fetch_array($customers)) {
            $customer_uid = $cu['uid'];
            array_push($cust_array, $customer_uid);
        }
        $customer_list = implode(",", $cust_array);
        $orcustomer = " OR `customer_id` IN ($customer_list)";
    } else {
        $orcustomer = "";
    }

    ////------ search by agent
    $agent_array = array();
    $agents = fetchtable("o_users", "name LIKE '%$search_%' $anduserbranch", "uid", "asc", 20, "uid");

    $agent_lists = mysqli_num_rows($agents);
    if ($agent_lists > 0) {
        while ($cu = mysqli_fetch_array($agents)) {
            $agent_uid = $cu['uid'];
            array_push($agent_array, $agent_uid);
        }
        $agent_list = implode(",", $agent_array);
        $oragent = " OR `agent_id` IN ($agent_list)";
    } else {
        $oragent = "";
    }


    //------- search by branch
    if ($orcustomer != "" || $oragent != "") {
        $andsearch = " AND ($orcustomer $oragent)";

        // strip out the first occurrence of OR from the string
        //$andsearch = preg_replace('/OR/', '', $andsearch, 1);
    }
}

if (empty($andsearch) && !empty($search_)) {
    $rows .= "$noRows";
} else {
    if ($customer > 0) {
        $andcustomer = " AND customer_id='$customer'";
    } else {
        $andcustomer = " ";
    }

    //-----------------------------Reused Query
 //echo $where_." $andcustomer $anduserbranch $andsearch <br/>";
    //default or all interactions
    $o_conversations = fetchtable('o_customer_conversations', "$where_ AND status > 0 $andcustomer $anduserbranch $andsearch", "$orderby", "$dir", "$limit", "uid, branch, customer_id, agent_id, loan_id, conversation_method, conversation_date, next_interaction, transcript, flag, tag,next_steps, outcome, call_duration, call_direction, recording_url");

    $customer_uids = table_to_array('o_customer_conversations', "$where_ AND status > 0 $andcustomer $anduserbranch $andsearch", "$limit", "customer_id", "$orderby", "$dir");

    $customer_list = implode(',', $customer_uids);
    ///----------Paging Option
    $alltotal = countotal_withlimit("o_customer_conversations", "$where_ AND status > 0  $andcustomer $anduserbranch $andsearch", "uid", "1000");
    ///==========Paging Option

    $cust_names = table_to_obj('o_customers', "uid in ($customer_list)", "100", "uid", "full_name");
    $agent_names = table_to_obj('o_users', "uid > 0 $andbranch AND status=1", "1000", "uid", "name");

    $branch_names = table_to_obj('o_branches', "uid > 0", "1000", "uid", "name");
    $outcome_names = table_to_obj('o_conversation_outcome', "uid > 0", "10000", "uid", "name");
    $conv_method_ = table_to_obj2('o_conversation_methods', "uid > 0", 30, "uid", array("name", "details"));

    if ($alltotal > 0) {
        while ($i = mysqli_fetch_array($o_conversations)) {
            $uid = $i['uid'];
            $customer_id = $i['customer_id'];  $goto = "<a title='Go to customer' href=\"customers?customer=".encurl($customer_id)."\"><i class='fa fa-external-link'></i></a>";
            $customer_name = $cust_names[$customer_id] ?? '';
            $agent_id = $i['agent_id'];
            $agent_name = $agent_names[$agent_id] ?? '';
            $loan_id = $i['loan_id'];
            $conversation_method = $i['conversation_method'];
            $cm_arr = $conv_method_[$conversation_method] ?? [];
            $c_meth_name = $cm_arr['name'];
            $c_meth_details = $cm_arr['details'];
            $conversation_date = $i['conversation_date'];
            $conversation_date_only = date("Y-m-d", strtotime($conversation_date));
            $conversation_time_only = date("h:iA", strtotime($conversation_date));
            $next_interaction = $i['next_interaction'];
            $transcript = $i['transcript'];
            $flag = $i['flag'];
            $next_steps = $i['next_steps'];
            $outcome = $i['outcome'];
            $tag = $i['tag'];
            $outc = $outcome_names[$outcome] ?? '';
            $cust_branch_ = $i['branch'];
            $cust_branch = $branch_names[$cust_branch_] ?? '';
            $call_duration = $i['call_duration'] ?? 0;
            $call_duration = $call_duration . "s";

            $call_direction = $i['call_direction'] ?? '';
            $recording_url = $i['recording_url'] ?? '';

            if(input_length($tag, 1) == 1){
                $t = explode('.',$tag);
                $typ = $t[0];
                $dt = $t[1];
                $tag_det = "<label class='label label-default'>$dt($typ)</label>";
            }
            else{
                $tag_det = "";
            }

            if ($conversation_method == 3) {
                $transcript .= "<br>";
                if ($recording_url) {
                    $transcript .= "<small class='text-muted'>Call Direction:</small> $call_direction</div>";
                    $transcript .= ", <small class='text-muted'>Duration:</small> <span class=''>$call_duration</span>";
                    $transcript .= "<span style='width:100px;height:25px;'>
                    <audio controls style='width:250px;height:35px;'>
                        <source src='$recording_url' type='audio/mpeg'>
                        <a href='$recording_url' target='_blank'>Recording <i class='fa fa-play-circle text-blue'></i></a>
                    </audio>
                </span>";
                }
            }


            if ($customer_id > 0) {
                /* $l = fetchmaxid("o_loans", "customer_id = $customer_id AND status > 0", "uid, loan_balance");
                   $loan_id = encurl($l['uid']);
                   $loan_bal = $l['loan_balance'];
    
                   if($loan_bal < 0){
                    $loan_bal = 0;
                   }  */

                if ($loan_id < 1) {
                    $loan_id = "<b>Null</b>";
                }
            }
            $rows .= "<tr><td>$uid</td>
                        <td><span class=\"font-16\">$customer_name $goto<br/> <span class=\"text-muted font-13\">$cust_branch</span> <br/> " . flag($flag) . "</span>
                        </td>
                        <td><i class=\"fa " . $c_meth_details . "\"></i><br/><span class='font-13'>" . $c_meth_name . "</span></td>
                        <td><span>$conversation_date_only</span><br/> <span class=\"text-muted font-13\">" . fancydate($conversation_date_only) . "</span><br><span class=\"text-blue font-400\">$conversation_time_only</span></td>
                        <td>$agent_name</td>
                        <td><span>$transcript</span> $tag_det</td>
                        <td><span>$next_interaction</span><br/> <span class=\"text-muted font-12\">" . fancydate($next_interaction) . "</span></td>
                        <td><span>Loan ID: <span class=\"text-bold\">$loan_id</span></span><br/> <span class=\"text-muted font-12 font-bold\">Balance: <span class=\"text-danger\">" . money($loan_bal) . "</span></span></td>
                        
                        <td><span title =\"click to view this interaction's details\"><a class='pointer' onclick=\"view_interaction($uid);\"><span class=\"fa fa-eye text-green\"></span></a></span><h4 title =\"click to view this customer's other interactions\"><a href='interactions?customer=" . encurl($customer_id) . "'><i class=\"fa fa-reorder text-blue\"></i></a></h4></td>
                        </tr>";
        }
    } else {
        $rows .= "<tr><td colspan='9'><i>No Records Found</i></td></tr>";
    }
}


echo trim($rows) . "<tr style = 'display: none'><td><input type='text' id='_alltotal_' value=\"$alltotal\"/></td><td><input type='text' id='_pageno_' value=\"$page_no\"></td></tr>";

// echo "<tr>filters =====> $andcustomer $anduserbranch $andsearch</tr>";
include_once("../../configs/close_connection.inc");
