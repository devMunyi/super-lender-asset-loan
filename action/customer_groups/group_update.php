<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
include_once("../../php_functions/functions_v2.php");

$userd = session_details_v2();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}
$gid = intval($_POST['gid']);

// $group_name = sanitizeAndEscape($_POST['group_name'], $con);
// $group_description = sanitizeAndEscape($_POST['group_description'], $con);

$group_name = trim($_POST['group_name']);
$group_description = trim($_POST['group_description']);
$group_phone = $_POST['group_phone'];
$leader_name = $_POST['leader_name'];
$group_till = $_POST['group_till'];
$group_acc = $_POST['group_acc'];
$group_branch = $_POST['group_branch'] ?? 0;
$meeting_day = $_POST['meeting_day'] ?? 0;
$meeting_time = trim($_POST['meeting_time'] ?? '');
$meeting_venue = sanitizeAndEscape($_POST['meeting_venue'] ?? '', $con);

if ($gid > 0) {
    $g = decurl($gid);
} else {
    die(errormes("Group not selected"));
    exit();
}


$events = "Customer group updated at [$fulldate] by [" . $userd['name'] . "{" . $userd['uid'] . "}" . "$username]";
$status = 1;


///////////-------------------- Start Validation
if ((input_available($group_name)) == 0) {
    exit(errormes("Group name is invalid/required"));
}

if($group_branch == 0){
    exit(errormes("Please select a branch"));
}

$where = "group_name = ? AND uid = ?";
$vals = ["$group_name", $g];
$result = checkrowexists_v2('o_customer_groups', $where, $vals);
$group_exists = $result["payload"];
$message = $result["message"];
if ($group_exists == 1) {
    exit(errormes("Group name already exists."));
} elseif ($group_exists == -1) {
    exit(errormes("Unable to update customer group $message."));
}

///////////=================== End Validation

$fds = "group_name = ?, group_description = ?";
$where = "uid = ?";
$vals = ["$group_name", "$group_description", $g];

///////////===================Validation


$update = updatedb('o_customer_groups',"group_name='$group_name', branch='$group_branch', group_description='$group_description', chair_name='$leader_name', meeting_day=$meeting_day, meeting_time='$meeting_time', meeting_venue='$meeting_venue', group_phone='$group_phone', till='$group_till', account_number='$group_acc'","uid='$g'");
if($update == 1)
{
    echo sucmes('Customer group updated Successfully');
    $g_id = $gid;
    $proceed = 1;
    store_event_v2('o_customer_groups', decurl($g_id), "$events");
} else {
    echo errormes('Unable to update customer group => ' . $update);
}


?>

<script>
    if ('<?php echo $proceed; ?>' === "1") {
        setTimeout(function() {
            gotourl("groups?group=<?php echo $g_id; ?>&members&updated");
        }, 1500);
    }
</script>