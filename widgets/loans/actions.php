<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
/////----------Session Check
$userd = session_details();
if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
$loan_id = $_POST['loan_id'];
if($loan_id < 1){
    die(errormes("Loan id invalid"));
    exit();
}
echo "<h4>Change Loan Status</h4>";
$loan = fetchonerow('o_loans', "uid='" . decurl($loan_id) . "'", "status");
$loan_status = $loan['status'];

?>

<?php
$loan_action = permission($userd['uid'], 'o_loans', "0", "general_");
if ($loan_action == 1) {
    $statuses = fetchtable('o_loan_statuses', "status=1 AND uid!='$loan_status'", "name", "asc", "100", "uid, name, color_code");
    while ($st = mysqli_fetch_array($statuses)) {
        $state_id = $st['uid'];
        $state_name = $st['name'];
        $color_code = $st['color_code'];
        echo "<button onclick=\"change_loan_statusV2('$loan_id','$state_id','$state_name');\" class='btn btn-lg btn-default custom-color' style='background-color: $color_code ; margin: 5px;'> $state_name</button>";
    }
} else {
    echo errormes("You don't have permission to change loan status");
}

$delete_perm = permission($userd['uid'], 'o_loans', "0", "delete_");
if ($delete_perm == 1) {
?>
    <hr />
    <button onclick="change_loan_statusV2('<?php echo $loan_id ?>',0, 'Delete this loan')" class="btn btn-danger btn-md pull-right"><i class="fa fa-trash"></i>Delete Loan</button>
    <br />
<?php
}

/////---------End of session check

?>