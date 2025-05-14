<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$cid = $_POST['cid'];
$mode = $_POST['mode'];
$userd = session_details();
if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
$limit = 20;
//$view_limits = permission($userd['uid'], 'o_customer_limits', "0", "view_");
//if ($view_limits == 1) {
//    $limit = 20;
//} else {
//    $limit = 1;
//}

$update_limit = permission($userd['uid'], 'o_customer_limits', "0", "update_");
if ($update_limit == 1) {
    if ($mode == 'EDIT') {
?>
        <table class="table">
            <tr>
                <td><input type="number" id="new_limit" class="form-control" placeholder="New Limit"> </td>
            </tr>
            <tr>
                <td>
                    <textarea class="form-control" id="limit_reason" placeholder="Reason for this Limit"></textarea>
                </td>
            </tr>
            <tr>
                <td><button class="btn bg-blue-gradient" onclick="give_limit('<?php echo $cid; ?>');">Give Limit</button></td>
            </tr>
        </table>
<?php
    }
} else {
    //echo notice("Y")
}

echo "<h4>Limit History (Latest First)</h4>";
echo "<table class='table table-bordered'>";
if ($cid > 0) {
    echo "<tr><th>ID</th><th>Amount</th><th>Date Given</th><th>Given By</th><th>Comments</th></tr>";
    $all_limit_users = implode(',', table_to_array('o_customer_limits', "customer_uid='$cid'", "$limit", "given_by"));
    $staff_names = table_to_obj('o_users', "uid in ($all_limit_users)", "100000", "uid", "name");

    $limits = fetchtable('o_customer_limits', "customer_uid='$cid'", "uid", "desc", "$limit", "uid, amount, given_date, comments, given_by");
    while ($l = mysqli_fetch_array($limits)) {
        $uid = $l['uid'];
        $amount = $l['amount'];
        $given_date = $l['given_date'];
        $comments = $l['comments'];
        $given_by = $l['given_by'];

        echo "<tr><td>$uid</td><td>$amount</td><td>$given_date</td><td>" . $staff_names[$given_by] . " ($given_by)</td><td>$comments</td></tr>";
    }
} else {
    die(errormes("Customer Id invalid"));
    exit();
}
echo "</table>";
include_once("../../configs/close_connection.inc");
?>