<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
$where_ =  $_POST['where_'] ?? '';
$offset_ =  $_POST['offset'] ?? 0;
$rpp_ =  $_POST['rpp'] ?? 10;
$page_no = $_POST['page_no'] ?? 1;
$orderby =  $_POST['orderby'] ?? "ss.uid";
$dir =  $_POST['dir'] ?? "DESC";
$search_ = sanitizeAndEscape($_POST['search_'] ?? "", $con);


$limit = "$offset_, $rpp_";
$offset_2 = $offset_ + $rpp_;
$limit2 = $offset_ + $rpp_;
$row = "";


////==== show only leads from the user's branch
$branchCondition = getBranchCondition($userd, 'o_customers');
$branchUserCondition = $branchCondition['branchUserCondition'] ?? $branchCondition['customerBranches'] ?? '';
$whereCondition = "$where_ $branchUserCondition";


if ($search_ != "" && !empty($search_)) {
    $whereCondition .= " AND (c.full_name LIKE '%$search_%' OR c.primary_mobile LIKE '%$search_%' OR b.name LIKE '%$search_%')";
} 


$spin_score_query = "SELECT ss.uid, c.full_name, c.primary_mobile as phone_number, b.name as branch, ss.result, ss.doc_reference_id, ss.score_type, ss.spin_status FROM o_spin_scoring ss INNER JOIN o_customers c ON ss.customer_id = c.uid INNER JOIN o_branches b ON c.branch = b.uid WHERE $whereCondition ORDER BY $orderby $dir LIMIT $limit";
$spin_score_result = mysqli_query($con, $spin_score_query);

$spin_score_count_query = "SELECT ss.uid FROM o_spin_scoring ss INNER JOIN o_customers c ON ss.customer_id = c.uid INNER JOIN o_branches b ON c.branch = b.uid WHERE $whereCondition ORDER BY $orderby $dir LIMIT 1000";
$spin_score_count_result = mysqli_query($con, $spin_score_count_query);
$alltotal = mysqli_num_rows($spin_score_count_result);


$spin_status_names = [
    1 => "Processing",
    2 => "Completed",
    3 => "Failed"
];

if ($alltotal > 0) {
    while ($l = mysqli_fetch_array($spin_score_result)) {
        $uid = $l['uid'];
        $uid_enc = encurl($uid);
        $full_name = $l['full_name'];
        $phone_number = $l['phone_number'];
        $branch_name =  $l['branch'] ?? "";
        $spin_status = $l['spin_status'];
        $spin_status_name = $spin_status_names[$spin_status] ?? 'Completed';
        $doc_reference_id = $l['doc_reference_id'] ?? "";
        $score_type = $l['score_type'] ?? "";
        $status_color = $spin_status == 3 ? "#ff0000" : ($spin_status == 1 ? "#ff8c00" : "#6cce05");

        $viewScoringBtn = "<button class='btn btn-sm btn-default' title='View Score' onclick='view_scoring($uid);'><i class='fa fa-eye text-green'></i> View Score</button>";
        $statusQueryBtn = "<button class='btn btn-sm btn-default' onclick='statusQuerySm(\"$score_type\", \"$doc_reference_id\", this)'>Query Status</button>";
        
        $status = "<span class='label custom-color' style='background-color:$status_color'> $spin_status_name</span>";


        $row .= "<tr><td>$uid</td>
                                <td><span>$full_name</span><br/> <span class='text-muted font-13 font-bold'>$phone_number</span>
                                </td>
                                <td><span>$branch_name</span></td>
                                <td>$status</td>
                                <td>$viewScoringBtn $statusQueryBtn</td>
                            </tr>";
    }
} else {
    $row = "<tr><td colspan='8'><i>No Records Found</i></td></tr>";
}

echo trim($row) . "<tr style='display: none;'><td><input type='text' id='_alltotal_' value='$alltotal'><input type='text' id='_pageno_' value='$page_no'></td></tr>";
