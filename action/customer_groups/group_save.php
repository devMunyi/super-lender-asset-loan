<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../php_functions/functions_v2.php");
include_once("../../configs/conn.inc");

$userd = session_details_v2();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}


$group_name = sanitizeAndEscape($_POST['group_name'], $con);
$group_description = sanitizeAndEscape($_POST['group_description'], $con);
$group_phone = $_POST['group_phone'];
$leader_name = $_POST['leader_name'];
$group_till = $_POST['group_till'];
$group_acc = $_POST['group_acc'];
$group_branch = $_POST['group_branch'] ?? 0;
$meeting_day = $_POST['meeting_day'] ?? 0;
$meeting_time = trim($_POST['meeting_time'] ?? '');
$meeting_venue = sanitizeAndEscape($_POST['meeting_venue'] ?? '', $con);


$added_by = $userd['uid'];
$added_date = $fulldate;

$events = "Customer group created at [$fulldate] by [" . $userd['name'] . "{" . $userd['uid'] . "}" . "$username]";
$status = 1;


///////////--------------------Validation
if ((input_available($group_name)) == 0) {
    exit(errormes("Group name is invalid/required"));
}

$where = "group_name = ?";
$vals = ["$group_name"];

$group_exists = checkrowexists_v2('o_customer_groups', $where, $vals);

$group_exists = $result["payload"];
$message = $result["message"];

if ($group_exists == 1) {
    exit(errormes("Group name already exists."));
} elseif ($group_exists == -1) {
    exit(errormes("Unable to save customer group $message."));
}



///////////=================== End Validation

$fds = array('group_name', 'branch', 'group_description','chair_name', 'meeting_day', 'meeting_time', 'meeting_venue', 'group_phone','till','account_number','added_date','added_by','status');
$vals = array("$group_name", "$group_branch", "$group_description","$leader_name", $meeting_day, "$meeting_time", "$meeting_venue", "$group_phone","$group_till","$group_acc","$added_date","$added_by","$status");
$create = addtodb('o_customer_groups',$fds,$vals);

if ($create == 1) {
    echo sucmes('Customer group saved Successfully');

    $where = "group_name = ?";
    $vals = ["$group_name"];
    $g_id = encurl(fetchrow_v2('o_customer_groups', $where, $vals, "uid"));
    $proceed = 1;
    store_event_v2('o_customer_groups', decurl($g_id), "$events");
} else {
    echo errormes('Unable to Save customer group' . $create);
}


mysqli_close($con);

?>

<script>
    if ('<?php echo $proceed; ?>' === "1") {
        setTimeout(function() {
            gotourl("groups?group=<?php echo $g_id; ?>&members&just-created");
        }, 1500);
    }
</script>