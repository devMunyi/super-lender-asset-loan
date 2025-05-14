<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

$leader = $_GET['leader'];
$userd = session_details();

$remove_permission  = permission($userd['uid'],'o_team_leaders',"0","delete_");


$agent_names = table_to_obj('o_users',"user_group in (15,16,17,23,12,13,14,21)","1000","uid","name");
$group_names = table_to_obj('o_user_groups',"uid>0","1000","uid","name");
$staff_groups = table_to_obj('o_users',"uid>0","100000","uid","user_group");

$teams = fetchtable('o_team_leaders',"status=1","leader_id","asc","10000","uid, leader_id, agent_id");
while($t = mysqli_fetch_array($teams)){
    $uid = $t['uid'];
    $leader_id = $t['leader_id'];
    $leader_name = $agent_names[$leader_id];
    $leader_group = $staff_groups[$leader_id];
    $leader_group_name = $group_names[$leader_group];

    $agent_id = $t['agent_id'];
    $agent_name = $agent_names[$agent_id];
    $agent_group = $staff_groups[$agent_id];
    $agent_group_name = $group_names[$agent_group];

    if($remove_permission == 1) {
        $remove = "<a onclick=\"remove_leader_agent($uid);\" class='text-red'><i class='fa fa-times-circle'></i></a>";
    }
    else{
        $remove = "";
    }





    echo "<tr><td>$uid</td><td>$leader_name ($leader_id) </td> <td>$agent_name ($agent_id)  </td><td>$leader_group_name,$agent_group_name </td><td>$remove</td></tr>";

}

?>