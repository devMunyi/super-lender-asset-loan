<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$userd = session_details();
$staff_id = intval($userd["uid"]);

// ensure staff id is not 0
if($staff_id == 0){
    exit();
}

//$details = SYSTEM, LOANS, PAYMENTS, CUSTOMERS, INTERACTIONS, SUPPORT, MANAGER, STAFF
$details_array = array("SYSTEM"=>"fa fa-television","LOANS"=>"fa fa-money","PAYMENTS"=>"fa fa-credit-card","CUSTOMERS"=>"fa fa-users","INTERACTIONS"=>"fa fa-comments-o","SUPPORT"=>"fa fa-headphones","MANAGERS"=>"fa fa-eye","STAFF"=>"fa fa-male");

$all_new = fetchtable("o_notifications","status = 1 AND staff_id = $staff_id","uid","asc","5","uid, sent_date, source_details, title, details, link");
while($a = mysqli_fetch_array($all_new)){


    $uid = $a['uid'];
    $title = $a['title'];
    $details = $a['details'];
    $link = $a['link'];
    $sent_date = $a['sent_date'];
    $source_details = $a['source_details'];

    $det = $details_array[$source_details];



    echo "<div class=\"notification\" id=\"notification_$uid\">
        <a class=\"close-btn\" onclick=\"remove_element('#notification_$uid'); updateCounter();\"><i class='fa fa-times'></i></a>
        <a href='$link'><div class='font-bold font-16'><i class='$det pull-left'></i> $title</div>
        <div>$details</div> </a>
    </div>";

    ////////-----When its loaded, mark it as read
    updatedb('o_notifications',"status=2","uid=$uid");

}