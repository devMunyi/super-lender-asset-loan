<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if ($userd == null) {
    echo errormes("Your session is invalid. Please re-login");
    exit();
}
//---Copy targets from one month to another
$copy_from = $_POST["copy_from"];
$copy_to = $_POST['copy_to'];
$target_group = 'BRANCH';
$target_type = 'DISBURSEMENTS';

$copy_from_date = first_date_of_month($copy_from);
$copy_to_date = first_date_of_month($copy_to);
$copy_to_date_last = last_date_of_month($copy_to);

//---Check if they are the same month
if($copy_from_date == $copy_to_date){
    echo errormes("Dates must not be the same");
}


///----Check existing new records
$existing_group_ids = table_to_array("o_targets","status=1 AND starting_date='$copy_to_date' AND target_type='$target_type' AND target_group='$target_group'","1000","group_id");





$targets = fetchtable('o_targets',"status=1 AND starting_date='$copy_from_date' AND target_type='$target_type' AND target_group='$target_group' ","uid","asc","1000");
while($t = mysqli_fetch_array($targets)){
    $uid = $t['uid'];
    $target_group = $t['target_group'];
    $target_type = $t['target_type'];
    $group_id = $t['group_id'];
    $amount = $t['amount'];
    $amount_type = $t['amount_type'];
    $working_days = $t['working_days'];

    if(in_array($group_id, $existing_group_ids)){
       echo "Branch $group_id skipped <br/>";
    }
    else{
        $fds = array('target_type', 'target_group', 'starting_date','ending_date','group_id', 'amount', 'amount_type', 'working_days', 'status');
         $vals = array("DISBURSEMENTS", "BRANCH", "$copy_to_date","$copy_to_date_last","$group_id", "$amount", "$amount_type", "$working_days", "1");
            $proceed = addtodb('o_targets', $fds, $vals);
            $success+=$proceed;
            $all+=1;
            if ($proceed == 1) {
                //echo sucmes("Target Added successfully");
            } else {
               // echo errormes("Error Adding Target");
            }
        }




}

echo sucmes("$success/$all Created successfully. Duplicates:".sizeof($existing_group_ids));