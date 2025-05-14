<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$where_ = $_POST['where_'] ?? '';
$sort_option = $_POST['sort_option'] ?? '';
$offset_ = $_POST['offset'] ?? 0;
$rpp_ = $_POST['rpp'] ?? 10;
$page_no = $_POST['page_no'] ?? 1;
$orderby = $_POST['orderby'] ?? "uid";
$dir = $_POST['dir'] ?? "DESC";
$search_ = sanitizeAndEscape($_POST['search_'] ?? '', $con);

$limit = "$offset_, $rpp_";
$rows = "";

// Construct search conditions
$andsearch = "";
if (input_available($search_) == 1) {
    $audience_array = [];
    $audience = fetchtable2("o_campaign_target_customers", "name LIKE \"%$search_%\"", "uid", "asc", "uid");
    $audience_count = mysqli_num_rows($audience);
    if ($audience_count > 0) {
        while ($aud_list = mysqli_fetch_array($audience)) {
            $audience_array[] = $aud_list['uid'];
        }
        $target_audience_list = implode(", ", $audience_array);
        $ortargetaudience = "OR target_customers IN ($target_audience_list)";
    }
    $andsearch = " AND (name LIKE \"%$search_%\" OR running_date LIKE \"%$search_%\" OR target_customers LIKE \"%$search_%\" $ortargetaudience)";
}

// Define common base query
$query_conditions = "$where_ AND status = 1 $andsearch";
$date_condition = '';

switch ($sort_option) {
    case "today_campaigns":
        // Running today campaigns
        $date_condition = " AND DATE(running_date) = '$date'";
        break;
    case "sort_2":
        // Past campaigns
        $date_condition = " AND DATEDIFF(running_date, '$date') < 0";
        break;
    case "sort_3":
        // Upcoming campaigns
        $date_condition = " AND DATEDIFF(running_date, '$date') > 0";
        break;
    case "sort_4":
        // Repetitive campaigns
        $query_conditions .= " AND repetitive = 1";
        break;
    default:
        // All campaigns
       
}

// Finalize query conditions
$query_conditions .= $date_condition;

// Fetch campaigns and total count
$o_campaigns_ = fetchtable("o_campaigns", $query_conditions, "$orderby", "$dir", "$limit", "uid, name, target_customers, total_customers, running_date, running_status");
$alltotal = countotal_withlimit("o_campaigns", $query_conditions, "uid", "1000");

$runningCampaignDet = table_to_obj2("o_campaign_running_statuses", "uid > 0", 1000, "uid", array('name', 'color_code'));
if ($alltotal > 0) {
    while ($l = mysqli_fetch_array($o_campaigns_)) {
        $uid = $l['uid'];
        $uid_enc = encurl($uid);
        $campaign_name = $l["name"];
        $target_customers = $l["target_customers"];
        $total_customers = $l["total_customers"];
        $running_date = $l["running_date"];
        $running_date = date("Y-m-d", strtotime($running_date));
        $running_time = date("h:i A", strtotime($l["running_date"]));
        $running_state_ = $l["running_status"];
        $runningCampaignName = $runningCampaignDet[$running_state_]['name'];
        $runningCampaignColorCode = $runningCampaignDet[$running_state_]['color_code'];

        $row .= "<tr><td>$uid</td>
                            <td><span class='font-16'>$campaign_name</span></td>
                            <td><span><a target='_blank' href='campaign_uploads/$target_customers'><i class='fa fa-file-excel-o'></i></a> </span> <span class='text-black font-13 font-italic'>$total_customers numbers</span></td>
                            <td><span>$running_date</span><br/> <span class=\"text-orange font-13 font-bold\">" . fancydate($running_date) . '<span class=\'text-blue font-400\'>' . $running_time . '</span>' . "</span></td>

                            <td><span class = 'label custom-color' style = 'background-color:" . $runningCampaignColorCode . "'>" . $runningCampaignName . "</td>
                       
                            <td><span><a href='?campaign=$uid_enc'><span class='fa fa-eye text-green'></span></td>
                        </tr>
                ";
    }
} else {
    $row = "<tr><td colspan='8'><i>No Records Found</i></td></tr>";
}

echo   trim($row) . "<tr style='display: none;'><td><input type='text' id='_alltotal_' value='$alltotal'><input type='text' id='_pageno_' value='$page_no'></td></tr>";
include_once("../configs/close_connection.inc");