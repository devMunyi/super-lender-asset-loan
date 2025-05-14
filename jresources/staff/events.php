<?php

session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();

$staff_id = $_POST['staff_id'] ?? 0;
$start_date = $_POST['start_date'] ?? datesub(date('Y-m-d'), 0, 0, 1);
$end_date = $_POST['end_date'] ?? date('Y-m-d');

$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';

if ($staff_id == 0) {
    echo "<tr><td colspan='6'> <i>Staff id was not parsed</i></td></tr>";
    exit();
} else {
    $staff_id = decurl($staff_id);
}

$rows = "";
$o_events_ = fetchtable('o_events', "event_by='$sid' AND event_date  BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'", "uid", "desc", "10000", "uid, tbl, fld, event_details, event_date");
while ($d = mysqli_fetch_assoc($o_events_)) {
    $uid = $d['uid'];
    $tbl = $d['tbl'] ? $d['tbl'] : 'o_NULL';
    $tbl = str_replace('o_', "", $tbl);
    $tbl = ucfirst($tbl);
    $tbl = rtrim($tbl, 'es');
    $tbl = rtrim($tbl, 's');
    $fld = $d['fld'];
    $event_details = $d['event_details'];
    $event_date = $d['event_date'];

    $rows .= "<tr><td>$uid</td><td>$event_details</td><td>$tbl</td><td>$fld</td><td>$event_date</td></tr>";
}

echo $rows;
