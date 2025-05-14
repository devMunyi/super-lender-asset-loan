<?php

session_start();
require_once("../configs/conn.inc");
require_once("../php_functions/functions.php");

$limit = $_GET['l'] ?? 1000;

$running_campaigns = fetchtable("o_campaigns", "running_status = 2", "uid", "DESC", $limit, "uid");
$total_campaigns_running = mysqli_num_rows($running_campaigns);
$total_campaigns_still_running = 0;
$total_campaigns_already_run = 0;

while ($c = mysqli_fetch_assoc($running_campaigns)) {
    $campaign_id = $c['uid'];

    $messages_queued = checkrowexists('o_sms_outgoing', "source_tbl='o_campaigns' AND source_record='$campaign_id' AND status=1");
    if ($messages_queued == 1) {
        $total_campaigns_still_running += 1;
        continue;
    }else{
        // Update the campaign status to 3 => Already Run
        $update_campaign = updatedb('o_campaigns', "running_status=3", "uid='$campaign_id'");
        if ($update_campaign == 1) {
            $total_campaigns_already_run += 1;
        }
    }
}

echo json_encode([
    'total_campaigns_running' => $total_campaigns_running,
    'total_campaigns_still_running' => $total_campaigns_still_running,
    'total_campaigns_already_run' => $total_campaigns_already_run
]);

include_once("../configs/close_connection.inc");