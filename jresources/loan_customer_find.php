<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");
$search = $_POST['key'];

$userd = session_details();
$branchCondition = getBranchCondition($userd, 'o_customers');
$branchUserCondition = $branchCondition['branchUserCondition'] ?? "";

$branches = table_to_obj("o_branches", "status=1", "1000", "uid", "name");

echo "<table class='table table-striped table-condensed table-hover'>";

$o_customers_ = fetchtable('o_customers',"(full_name LIKE '%$search%' OR primary_mobile LIKE '%$search%' OR email_address LIKE '%$search%' OR national_id LIKE '%$search%') $branchUserCondition", "uid", "desc", "0,5", "uid ,full_name ,primary_mobile, branch, loan_limit ,national_id ,status ");
while($e = mysqli_fetch_array($o_customers_))
{
    $uid = $e['uid'];
    $full_name = $e['full_name'];    $f_name = preg_replace('/[^A-Za-z0-9. -]/', '', $full_name);
    $primary_mobile = $e['primary_mobile'];
    $branch_name = $branches[$e['branch']] ?? "";
    if($branch_name != ""){
        $branch_name = "($branch_name)";
    }
    $email_address = $e['email_address'];
    $national_id = $e['national_id'];
    $loan_limit = $e['loan_limit'];
    $status = $e['status'];
    echo "<tr><td class='shadow p-3 mb-5 bg-white rounded'><a class='pointer' onclick=\"select_client('$f_name (ID: $national_id)','$uid');\"><span class='font-bold font-16 text-blue'>$full_name $branch_name</span> <br/>
    <span class='text-black font-italic'>Phone:</span> <span class='font-bold text-black'>$primary_mobile</span> <i class='fa fa-arrows-h'></i> <span class='text-black font-italic'> Limit:</span> <span class='font-bold text-black'>".money($loan_limit)."</span> </a></td></tr>";
}
echo "</table>";
