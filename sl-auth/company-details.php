<?php
include_once 'connect.inc';


$company_id = $_POST['company_id'];
if($company_id > 0){
    $company = array();
    $query="SELECT * FROM company WHERE uid='$company_id' "; //echo "<tr><td>".$query."</td></tr>";
    $result=mysqli_query($con1, $query);
    $roww=mysqli_fetch_array($result);

    $company['uid'] = $roww['uid'];
    $company['name'] = $roww['name'];
    $company['logo'] = $roww['logo'];
    $company['added_by'] = $roww['added_by'];
    $company['added_date'] = $roww['added_date'];
    $company['db_name'] = $roww['db_name'];
    $company['status'] = $roww['status'];
    echo json_encode($company);
}
else{
    echo 0;
}
