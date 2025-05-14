<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

$loan_id = $_POST['loan_id'];
$comment = $_POST['comment'];
$posted_stage_id = intval(trim($_POST['stage_id']));
if(empty($posted_stage_id)){
    exit(errormes("Invalid Stage ID!"));
}

$posted_stage_id = decurl($posted_stage_id);

$loan_details = fetchonerow('o_loans', "uid='" . decurl($loan_id) . "'", "account_number, product_id, loan_amount, disbursed_amount, disbursed, loan_stage, added_by, product_id, current_branch, status");
$mobile_number = $loan_details['account_number'];
$disbursed_amount = $loan_details['disbursed_amount'];
$disbursed = $loan_details['disbursed'];
$current_stage = $loan_details['loan_stage'];
$product_id = $loan_details['product_id'];
$current_branch = $loan_details['current_branch'];
$added_by = $loan_details['added_by'];

//// ==== approval validation
$lstatus = intval($loan_details['status']);
if ($lstatus != 1) {
    $status_name = fetchrow("o_loan_statuses", "uid=$lstatus", "name");
    exit(errormes("Loan is not approved. Current status is $status_name"));
}


if($posted_stage_id < $current_stage){
    exit(errormes("Loan Stage Already Approved!"));
}

$allowed_stages = table_to_array("o_product_stages", "product_id='$product_id' AND status=1", 100, "stage_id");
if(!in_array($posted_stage_id, $allowed_stages)){
    exit(errormes("Invalid Request!"));
}

$stage_action_permission  = permission($userd['uid'], 'o_loan_stages', "$current_stage", "general_");
if ($stage_action_permission == 0) {
    die(errormes("You don't have permission to approve loan"));
    exit();
}

if ($prevent_own_loan_approval == 1) {
    if ($userd['uid'] != 1) {
        if ($userd['uid'] == $added_by) {
            die(errormes("You cannot approve a loan you created"));
            exit();
        }
    }
}

$product_id = $loan_details['product_id'];
$product_details = fetchonerow('o_loan_products', "uid='$product_id'", "name, disburse_method");
$disburse_method = $product_details['disburse_method'];    /////----The method of disbursing this loan

$final_stage = fetchrow('o_product_stages', "product_id='$product_id' AND status=1 AND is_final_stage=1", "stage_id");
$status = 1;

///////----------------Validation
if ($loan_id > 0) {
    $next_stage = loan_next_stage(decurl($loan_id));
} else {

    die(errormes("Loan code needed"));
    exit();
}

if ($next_stage['stage_details']['uid'] > 0) {
    $stage_id = $next_stage['stage_details']['uid'];
    $update_loan_stage = updatedb('o_loans', "loan_stage='$stage_id'", "uid=" . decurl($loan_id));
    if ($update_loan_stage == 1) {
        $proceed = 1;
        echo sucmes("Loan moved to next stage");
        $event = "Loan moved to the next stage[" . $next_stage['stage_details']['name'] . "] by [" . $userd['name'] . "(" . $userd['email'] . ")] on [$fulldate] with comment [<i>$comment</i>]";
        store_event('o_loans', decurl($loan_id), "$event");

        ////-----Notify people who approve next stage
        $groups_array = array();
        $users_array = array();
        $next_approvers = fetchtable('o_permissions', "tbl='o_product_stages' AND rec='$stage_id' AND (general_=1 OR custom_action='APPROVE') AND status=1", "uid", "asc", "100", "group_id, user_id");
        while ($nx = mysqli_fetch_array($next_approvers)) {
            $group_id = $nx['user_id'];
            $user_id = $nx['user_id'];

            if ($group_id > 0) {
                array_push($groups_array, $group_id);
            }
            if ($user_id > 0) {
                // notify($userd['uid'],"$user_id","Loan Stage Approval","New loan requires your approval","loans?loan=".$loan_id);
                array_push($users_array, $group_id);
            }
            ////----Notify group and take care of branches
        }


        if (!empty($users_array)) {
            $user_list = implode(',', $users_array);
            $anduser = " AND uid in (" . $user_list . ")";
        } else {
            $anduser = "";
        }
        ///------The above code is not implemented yet and we are only considering group permissions below

        /// -------------This group has permission to approve
        if (!empty($groups_array)) {
            $groups_viewing_all_branches = table_to_array('o_permissions', "tbl='o_loans' AND read_=1 AND status=1", 100, "group_id", "uid", "asc");
            // echo "Array has values.";
            $group_list = implode(',', $groups_array);
            $members = fetchtable('o_users', "user_group IN ($group_list) AND status=1", "uid", "asc", "100", "group_id, uid, branch");
            while ($nx = mysqli_fetch_array($members)) {
                $staff_id = $nx['uid'];
                $group_id = $nx['group_id'];
                $branch_id = $nx['branch'];
                ////------This staff can receive notification, check if they are in all branches view
                if (in_array($group_id, $groups_viewing_all_branches)) {
                    /////----They receive notifications for all branches
                    notify($userd['uid'], "$staff_id", "Loan Stage Approval", "New loan requires your approval", "loans?loan=" . $loan_id);
                } else {
                    /////-----Can only receive for current branch
                    if ($branch_id == $current_branch) {
                        /////-----User is in the same branch
                        notify($userd['uid'], "$staff_id", "Loan Stage Approval", "New loan requires your approval", "loans?loan=" . $loan_id);
                    }
                }
            }
        } else {
            // echo "Array is empty.";
        }
    }
} else {
    die(errormes("Something is wrong with the next stage. Please check the product settings"));
}

///////------------End of validation



?>
<script>
    modal_hide();
    if ('<?php echo $proceed; ?>') {
        setTimeout(function() {
            loan_stages('<?php echo $loan_id; ?>');
        }, 400);
    }
</script>