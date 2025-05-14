<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$b = $_POST['b'];
$user_id = $_POST['user_id'];
$mode = $_POST['user_type'];

$benc = encurl($b);

$staff_names = table_to_obj('o_users',"status=1 AND branch='$b'","1000","uid","name");

$all_active_los =  table_to_array('o_users',"status=1 AND user_group = 7 AND branch='$b'","10000","uid");
$all_active_cos = table_to_array('o_users',"status=1 AND user_group = 8 AND branch='$b'","10000","uid");



$pairs = fetchtable('o_pairing',"branch='$b' AND status=1","uid","asc","10000","uid, lo, co");
while($p = mysqli_fetch_array($pairs)){
    $loo = $p['lo'];
    $coo = $p['co'];

    if((in_array($loo, $all_active_los)) == false){
        die(errormes("Please fix the pairing first <a class='btn btn-sm btn-primary' onclick=\"load_std('/extensions/sp-pairing-1.php','#dynamic_load','b=$benc'); modal_hide();\">Fix</a>"));
        continue;
    }
    else  if((in_array($coo, $all_active_cos)) == false) {
        die(errormes("Please fix the pairing first <a class='btn btn-sm btn-primary' onclick=\"load_std('/extensions/sp-pairing-1.php','#dynamic_load','b=$benc'); modal_hide();\">Fix</a>"));
        continue;
    }
}




if($user_id > 0) {
    $user = fetchonerow('o_users', "uid='$user_id'", "uid, name, branch, status, user_group");
    $name = $user['name'];
    $branch_name = fetchrow('o_branches', "uid='" . $user['branch'] . "'", "name");
    $status = fetchrow('o_staff_statuses', "uid='" . $user['status'] . "'", "name");
    $group = fetchrow('o_user_groups', "uid='" . $user['user_group'] . "'", "name");

    $user_details = "<b>$name</b> from <b>$branch_name</b> branch, status <b>$status</b> and Group <b>$group</b>";
}
else{
    $user_details = "<b>NO user</b>";
}


$userd = session_details();
if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
$total_los = 0;

if($mode == 'LO'){
$total_accounts = countotal('o_loans',"current_lo='$user_id' AND current_branch='$b' AND disbursed=1 AND status!=0","uid");

//    $all_loans = json_encode(table_to_array("o_loans","current_lo='$user_id' AND current_branch='$b'","100000","uid"));
//    echo "<textarea>$all_loans</textarea>";

echo "<h4>You have $total_accounts loans assigned to $user_details as LO, please allocate to a pair below</h4>";
echo "<table class='table table-bordered'>";
    $all_pairing = fetchtable('o_pairing', "branch='$b' AND status=1", "uid", "asc", "100000", "uid, lo, co");
    while ($ap = mysqli_fetch_array($all_pairing)) {
        $pid = $ap['uid'];
        $lo = $ap['lo'];
        $co = $ap['co'];
        $total_los+=1;
        $pair_button = "<button onclick=\"assign_bulk_accounts('$lo','$co','LO','$user_id','$b')\"; class='btn btn-success'>Assign</button>";

        echo "<tr><td>".$staff_names[$lo]."($lo)</td> <td>".$staff_names[$co]."($co)</td> <td>$pair_button</td></tr>";


    }
    echo "</table>";
?>

<?php



}
else if($mode == 'CO'){

    $total_accounts = countotal('o_loans',"current_co='$user_id' AND current_branch='$b' AND disbursed=1 AND status!=0","uid");
    echo "<h4>You have $total_accounts loans assigned to $user_details as CO, please allocate to a pair below</h4>";

    echo "<table class='table table-bordered'>";
    $all_pairing = fetchtable('o_pairing', "branch='$b' AND status=1", "uid", "asc", "100000", "uid, lo, co");
    while ($ap = mysqli_fetch_array($all_pairing)) {
        $pid = $ap['uid'];
        $lo = $ap['lo'];
        $co = $ap['co'];
        $total_los+=1;
        $pair_button = "<button onclick=\"assign_bulk_accounts('$lo','$co','CO','$user_id','$b')\"; class='btn btn-success'>Assign</button>";

        echo "<tr><td>".$staff_names[$lo]."($lo)</td> <td>".$staff_names[$co]."($co)</td> <td>$pair_button</td></tr>";


    }

    ?>
<?php

}
else{
    echo errormes("Mode not selected");
}
if($total_los > 0) {
   // $share_equal = "<button onclick=\"assign_bulk_dist('$mode','$user_id','$b')\"; class='btn btn-success'>Assign</button>";
    echo "<tr><td colspan='3'>Share equally</td> <td>$share_equal</td></tr>";
}
echo "</table>";

?>
<div class="well bg-warning">
<h4>Notes</h4>
<ol>
    <li>The loans needs to be assigned to a pair rather than an individual</li>
    <li>The loan pair need to be correct and not interchanged</li>
    <li>The pair need to be an LO and a CO respectively</li>
    <li>The pair needs to be active</li>
    <li>The pair needs to be in the same branch</li>
</ol>
</div>
