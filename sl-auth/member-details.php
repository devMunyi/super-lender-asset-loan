<?php
include_once 'connect.inc';


$email = $_POST['member_email'];
if((strlen($email)) > 0){
    $member = array();
    $query="SELECT * FROM members WHERE member_email='$email' "; //echo "<tr><td>".$query."</td></tr>";
    $result=mysqli_query($con1, $query);
    $roww=mysqli_fetch_array($result);

    $member['uid'] = $roww['uid'];
    $member['member_email'] = $roww['member_email'];
    $member['member_company'] = $roww['member_company'];
    $member['added_date'] = $roww['added_date'];
    $member['status'] = $roww['status'];

    echo json_encode($roww);
}
else{
    echo 0;
}
