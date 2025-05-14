<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$term = $_POST['search_term'];
$userd = session_details();

$module = $_POST["module"];
$uid = $_POST["uid"];
$act = $_POST["act"];
$search_key = $_POST["search_key"];
$reload = 0;
$skip = 0;

$branchCondition = getBranchCondition($userd, 'o_customers');
$branchUserCondition = $branchCondition['branchUserCondition'];
$branchLoanCondition = $branchCondition['branchLoanCondition'];

if($module == 'o_loans'){

    $lid = decurl($uid);
    $loan_details = fetchonerow('o_loans',"uid='$lid'","status");
    $state = $loan_details["status"];
    if($act == 'PREV'){
        $and_uid = " AND status='$state' AND uid < $lid";
    }
    else if($act == 'NEXT'){
        $and_uid = " AND status='$state' AND uid > $lid";
    }
    else if($act == 'SEARCH'){
        $and_uid = " AND (uid='$search_key' OR account_number='".make_phone_valid($search_key)."')";
    }
    else if($act == 'ALL'){
        if($state == 1){
           /////----Created
            $new_url = "loans?approvals";
        }
        elseif ($state == 2){
           /////----Pending Disbu
            $new_url = "loans?approvals";
        }
        elseif ($state == 7){
            ////----Overdue
            $new_url = "defaulters";
        }
        else{
            $new_url = "loans";
        }
        $reload = 1;
        $skip = 1;
    }
    if($skip == 0) {
        $new_record = fetchrow('o_loans', "uid>0 $and_uid $branchLoanCondition", "uid");
        if ($new_record > 0) {
            $new_url = "loans?loan=" . encurl($new_record);
            $reload = 1;
        } else {
            $message = errormes("No record");
        }
    }

}

include_once("../configs/close_connection.inc");

?>
<script>
    if ('<?php echo $reload; ?>' === '1') {
        gotourl('<?php echo $new_url; ?>');

    }
    else{
        feedback("ERROR","TOAST",".feedback","<?php echo $message; ?>",2);
    }
</script>
