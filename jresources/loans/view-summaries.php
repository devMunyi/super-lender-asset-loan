<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();


// Function to get the search condition

$read_all = permission($userd['uid'], 'o_loans', "0", "read_");
$month_start = first_date_of_month(datesub($date,0, 3,0));
if ($read_all == 1) {
    $anduserbranch = $andloanbranch = "";
} else {
    $user_branch = $userd['branch'];
    $anduserbranch = " AND branch='$user_branch'";
    $andloanbranch = " AND current_branch='$user_branch'";

    //////-----Check users who view multiple branches
    $staff_branches = table_to_array('o_staff_branches', "agent=" . $userd['uid'] . " AND status=1", "1000", "branch", "uid", "asc");
    if (sizeof($staff_branches) > 0) {
        ///------Staff has been set to view multiple branches
        array_push($staff_branches, $userd['branch']);
        $staff_branches_list = implode(",", $staff_branches);
        $anduserbranch = " AND branch in ($staff_branches_list)";
        $andloanbranch = " AND current_branch in ($staff_branches_list)";
    }
}
$loan_statuses = table_to_obj('o_loan_statuses',"uid > 0","100","uid","name");
$status_totals = array();
foreach ($loan_statuses as $status_id => $status_name) {
    $status_totals[$status_id] = 0;
}

$o_loans_ = fetchtable('o_loans', "uid > 0 $andloanbranch AND given_date BETWEEN '$month_start' AND '$date'", "uid", "asc", "1000000", "status");
while($o_l = mysqli_fetch_array($o_loans_)) {
    $status = $o_l['status'];
    $status_totals = obj_add($status_totals, $status, 1);
}

echo "<table class='table table-bordered font-16 table-striped table-hover table-condensed'>";
foreach ($loan_statuses as $status_id => $state_name) {

   $total_loans = $status_totals[$status_id];
    echo "<tr><td>$state_name</td><td>$total_loans</td></tr>";

}
echo "</table><br>";


include_once("../../configs/close_connection.inc");
