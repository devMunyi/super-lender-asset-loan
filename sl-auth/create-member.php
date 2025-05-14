<?php
include_once 'connect.inc';

$email = $_POST['email'];
$company = $_POST['company'];

if((strlen($email)) > 4 && ($company > 0)){
    $insertq="INSERT into members (member_email, member_company, added_date, status)  VALUES ('$email','$company',NOW(), 1)";  //echo $insertq;

    if(!mysqli_query($con1,$insertq))
    {
        echo mysqli_error($con1);  //var_dump($e);
    }
    else
    {
        echo 1;
    }
}
else{
    echo "Email or Company Missing";
}

