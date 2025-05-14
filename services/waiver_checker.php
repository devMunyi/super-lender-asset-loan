<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$active_campaigns = fetchtable('o_campaigns',"status=1");

$loans = fetchtable('o_loans',"status=7 AND JSON_EXTRACT(other_info, '$.WAIVER_STATUS') = '1'","uid","asc","20","uid, other_info");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $other_info = $l['other_info'];
    $other = JSON_decode($other_info, true);

    $campaign_id = $other['CAMPAIGN_ID'];

    echo "$uid, $campaign_id <br/>";
}