<?php

session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();

$user_id = $_POST['user_id'] ?? 0;

if ($user_id == 0) {
    echo "<tr><td colspan='3'><i>User id was not parsed</i></td></tr>";
    exit();
} else {
    $user_id = decurl($user_id);
}

$view_events = permission($userd['uid'], 'o_events', "0", "read_");
if ($view_events == 1) {

    $o_events_ = fetchtable('o_events', "event_by=$user_id AND status = 1", "uid", "DESC", "1000", "uid, event_details, event_date ");
    if (mysqli_num_rows($o_events_) > 0) {
        while ($k = mysqli_fetch_array($o_events_)) {
            $uid = $k['uid'];
            // Remove <span> elements along with their contents
            $cleaned_event_details = preg_replace('/<span\b[^>]*>.*?<\/span>/is', '', $k['event_details']);

            // Remove only the <i> tags but retain their inner content
            $cleaned_event_details = preg_replace('/<\/?i\b[^>]*>/i', '', $cleaned_event_details);

            // Sanitize for HTML output
            $event_details = htmlspecialchars($cleaned_event_details);
            $event_date = $k['event_date'];
            echo "<tr><td><span class='font-16 text-bold'>$event_date</span> " . fancydate($event_date) . "</td><td>$event_details</td><td>$uid</td> </tr>";
        }
    } else {
        echo "<tr><td colspan='3'> <i>No Records Found</i></td></tr>";
    }
}

// include close connection
include_once("../../configs/close_connection.inc");
