<?php
session_start();
include_once '../configs/20200902.php';
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");
include_once("../configs/archive_conn.php");

$proceed = 0;

$action = $_POST['action'];
if ($action == 'OPEN') {
    $userd = session_details();
    if ($userd == null) {
        die(errormes("Your session is invalid. Please re-login"));
        exit();
    }


    $staff_id = $userd['uid'];
    $tok = $_SESSION['o-token'];

    $_SESSION['archives'] = 1;


    //include_once ("../configs/conn.inc");
    if ($staff_id > 0) {

        $exists = checkrowexists('o_users', "uid = '$staff_id'");
        if ($exists == 1) {
            $login = login_to_archives($staff_id, $tok);
            if ($login == 1) {
                echo sucmes("Viewing archive data");
                $proceed = 1;
            } else {
                echo errormes("Unable to open archives");
            }
        } else {
            echo errormes("User does not exist in archives");
        }
    }

    if ($login != 1) {
        unset($_SESSION['archives']);
        $proceed = 1;
    }
} else {
    echo sucmes("Viewing live data");
    unset($_SESSION['archives']);
}
?>
<script>
    if ('<?php echo $proceed; ?>') {
        setTimeout(function() {
            reload();
        }, 1000);
    }
</script>