<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}
$staff_id = $userd['uid'];
$group_id = $_POST['group_id'];
$user_id = $_POST['user_id'];
$tbl = $_POST['tbl'];
$rec = $_POST['rec'];
$act = $_POST['act'];
$value = $_POST['val'];
$opt = $_POST['opt'];

$custom_permissions = array('APPROVE', 'REJECT', 'RESEND', 'CANCEL', 'BLOCK', 'UNBLOCK','SHARP_INCREMENT', 'FREEZE', 'UNFREEZE', 'CONVERT_LEAD', 'WRITE_OFF', 'ENABLE_2FA', 'DISABLE_2FA', 'DELETE_PASSKEY', 'DOWNLOAD');

if($group_id > 0 || $user_id > 0){}
else{
    exit(errormes("Please select group or user"));
}

if((input_available($tbl)) == 0){
    exit(errormes("Please select table"));
}

if($act == 'CUSTOM'){
    $and_custom = " AND custom_action = '$value'";
}
else{
    $and_custom = "";
}

$current_record = fetchmax('o_permissions',"group_id='$group_id' AND user_id='$user_id' AND tbl='$tbl' AND rec='$rec' $and_custom AND status = 1","uid, general_, create_, read_, update_, delete_, custom_action, status");
$uid = $current_record['uid'];
$custom_action_ = $current_record['custom_action'];

$all_custom = table_to_array('o_permissions',"group_id='$group_id' AND user_id='$user_id' AND tbl='$tbl' AND rec='$rec' AND status=1","10000","custom_action");

//die();

if($uid > 0){

    if((input_available($act))){
        if($act == 'CUSTOM') {
            $current_id = fetchrow('o_permissions',"group_id='$group_id' AND user_id='$user_id' AND tbl='$tbl' AND rec='$rec' AND custom_action='$value'","uid");
            if($current_id > 0){
                $up = updatedb('o_permissions', "custom_action='$value', status=$opt, added_by='$staff_id'", "uid='$current_id'");
            }
            else{
                $fds = array('group_id','user_id','tbl','rec','general_','create_','read_','update_','delete_','custom_action','added_by','last_updated_date','status');
                $vals = array("$group_id",false_zero($user_id),"$tbl",false_zero($rec),0,0,0,0,0,"$value",$staff_id,"$fulldate", $opt);
                $up = addtodb('o_permissions',$fds,$vals);
                echo errormes($up);
            }

        }
        else{
            $up = updatedb('o_permissions', "$act='$value', status=1,  added_by='$staff_id'", "uid='$uid'");
        }
       if($up == 1){
           $success = 1;
       }
    }
    $general = zerotone($current_record['general_']);
    $create = zerotone($current_record['create_']);
    $read = zerotone($current_record['read_']);
    $update = zerotone($current_record['update_']);
    $delete = zerotone($current_record['delete_']);
    $current_status = $current_record['status'];
    if($current_status > 0) {
        $custom = $current_record['custom_action'];
    }

    echo "<tr><td><a onclick=\"permissions('$group_id', '$user_id', '$tbl', '$rec', 'general_', $general);\" class=\"pointer\">".toggleico(($general))."</a> General Action</td></tr>";
    echo "<tr><td><a onclick=\"permissions('$group_id', '$user_id', '$tbl', '$rec', 'create_', $create);\" class=\"pointer\">".toggleico(($create))."</a> Create</td></tr>";
    echo "<tr><td><a onclick=\"permissions('$group_id', '$user_id', '$tbl', '$rec', 'read_', $read);\" class=\"pointer\">".toggleico(($read))."</a> Read</td></tr>";
    echo "<tr><td><a onclick=\"permissions('$group_id', '$user_id', '$tbl', '$rec', 'update_', $update);\" class=\"pointer\">".toggleico(($update))."</a> Update</td></tr>";
    echo "<tr><td><a onclick=\"permissions('$group_id', '$user_id', '$tbl', '$rec', 'delete_', $delete);\" class=\"pointer\">".toggleico(($delete))."</a> Delete</td></tr>";






}
else{
    $custom = "";
    if((input_available($act))){
        if($act == 'general_'){
            $general_ = $value;
        }
        if($act == 'create_'){
            $create_ = $value;
        }
        if($act == 'read_'){
            $read_ = $value;
        }
        if($act == 'update_'){
            $update_ = $value;
        }
        if($act == 'delete_'){
            $delete_ = $value;
        }
        if($act == 'custom_action'){
            $custom_action = $value;
        }

        //die(errormes($value));
        if($user_id < 1){
            $user_id = 0;
        }
        if($rec < 1){
            $rec = 0;
        }

        $fds = array('group_id','user_id','tbl','rec','general_','create_','read_','update_','delete_','custom_action','added_by','last_updated_date','status');
        $vals = array("$group_id","$user_id","$tbl","$rec",false_zero($general_),false_zero($create_),false_zero($read_),false_zero($update_),false_zero($delete_),"$value",$staff_id,"$fulldate", 1);
        $create = addtodb('o_permissions',$fds,$vals);
        if($create == 1){
                $success = 1;
        }

        echo errormes($create);


    }
    $general = $create = $read = $update = $delete = 0;
    echo "<tr><td><a onclick=\"permissions('$group_id', '$user_id', '$tbl', '$rec', 'general_', 1);\" class=\"pointer\"><i class=\"fa fa-times text-red\"></i></a> General Action</td></tr>";
    echo "<tr><td><a onclick=\"permissions('$group_id', '$user_id', '$tbl', '$rec', 'create_', 1);\" class=\"pointer\"><i class=\"fa fa-times text-red\"></i></a> Create</td></tr>";
    echo "<tr><td><a onclick=\"permissions('$group_id', '$user_id', '$tbl', '$rec', 'read_', 1);\" class=\"pointer\"><i class=\"fa fa-times text-red\"></i></a> Read</td></tr>";
    echo "<tr><td><a onclick=\"permissions('$group_id', '$user_id', '$tbl', '$rec', 'update_', 1);\" class=\"pointer\"><i class=\"fa fa-times text-red\"></i></a> Update</td></tr>";
    echo "<tr><td><a onclick=\"permissions('$group_id', '$user_id', '$tbl', '$rec', 'delete_', 1);\" class=\"pointer\"><i class=\"fa fa-times text-red\"></i></a> Delete</td></tr>";

}
echo "<tr><td></td></tr>";

for($i=0; $i<sizeof($custom_permissions); ++$i) {
    $perm = $custom_permissions[$i];
    if (in_array("$perm", $all_custom)) {
        ////----Already has permission
        $act_custom = "<a onclick=\"permissions('$group_id', '$user_id', '$tbl', '$rec', 'CUSTOM', '$perm', 0);\" class=\"pointer\">" . toggleico((0)) . "</a>";
    }
    else{
        ////----Doesn't have permission
        $act_custom = "<a onclick=\"permissions('$group_id', '$user_id', '$tbl', '$rec', 'CUSTOM', '$perm', 1);\" class=\"pointer\">" . toggleico((1)) . "</a>";
    }
    echo "<tr><td>$act_custom $perm</td></tr>";
}




$g = "($group_id, $user_id, '$tbl', $rec, '$act', '$value')";


?>
<script>
    if('<?php echo $success ?>'){
        permissions('<?php echo $group_id; ?>', '<?php echo $user_id; ?>', '<?php echo $tbl; ?>', '<?php echo $rec ?>', '', '');
    }
</script>

