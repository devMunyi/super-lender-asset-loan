<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();

$gid = $_POST['gid'];


$flag_names = table_to_obj('o_flags', "uid>0", "100", "uid", "name");
$flag_codes = table_to_obj('o_flags', "uid>0", "100", "uid", "color_code");
$members_array = array(0);
$members_limits = array();
$members_added_date = array();
//-----------------------------Reused Query
$members_ = fetchtable('o_group_members',"group_id = '$gid' AND status=1","uid","asc","100","uid, customer_id, loan_limit, date(added_date) as added_date");
while($m = mysqli_fetch_array($members_)){
    $customer_id = $m['customer_id'];
    $loan_limit = $m['loan_limit'];
    $added_date = $m['added_date'];

    $members_added_date[$customer_id] = $added_date;
    $members_limits[$customer_id] = $loan_limit;
   array_push($members_array, $customer_id);
}

$all_members_list = implode(',', $members_array);
$delete_permission = permission($userd['uid'],'o_group_members',"0","delete_");

$total_members = mysqli_num_rows($members_);
if($total_members > 0) {
    $all_customers = fetchtable('o_customers', "uid in ($all_members_list)", "full_name", "asc", "100", "uid, full_name, primary_mobile, email_address, national_id, gender, loan_limit");
    while ($r = mysqli_fetch_array($all_customers)) {
        $uid = $r['uid'];
        $full_name = $r['full_name'];
        $primary_mobile = $r['primary_mobile'];
        $national_id = $r['national_id'];
        $loan_limit = $r['loan_limit'];
        $added_date = $members_added_date[$uid];
        $member_limit = $members_limits[$uid];

        if($delete_permission == 1){
            $act = "<a title='Remove member' class='btn btn-danger btn-sm' onclick=\"delete_member($uid,  $gid);\"><i class='fa fa-times'></i> Remove</a>";
        }

        echo " <tr id='$gid$uid'><td>$uid</td><td>$full_name</td><td>$primary_mobile</td><td>$national_id</td><td>$added_date</td><td>$loan_limit</td><td>$act</td></tr>";
    }
}
else{
    echo " <tr><td class='font-16' colspan='7'><i>No members added ... </i></td></tr>";
}


include_once("../configs/close_connection.inc");
