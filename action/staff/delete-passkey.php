<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
}

$permi = permission($userd['uid'], 'o_users', "0", "DELETE_PASSKEY");
if ($permi != 1) {
    exit(errormes("You don't have permission to delete user passkey!"));
}


$email = trim($_POST['email']);
if (empty($email)) {
    exit(errormes("Username is required!"));
}

$user_det = fetchmaxid("o_users", "email='$email'", "uid, email");
if (empty($user_det['uid'])) {
    exit(errormes("User not found"));
}

try {

    $del_resp = delete_passkey(strtolower($user_det['email']));
    $deleted_count = intval($del_resp['deleted'] ?? 0);
    $message = $del_resp['message'] ?? "Could not delete passkey!";

    if ($deleted_count > 0) {
        echo sucmes("Succcess! Passkey Deleted.");
        $proceed = 1;
        $event = "Passkey Deleted by [" . $userd['name'] . "(" . $userd['email'] . ")";
        store_event('o_users', $user_det['uid'], "$event");
    } else {
        exit(errormes($message));
    }
} catch (Exception $e) {
    $message = $e->getMessage();
    exit(errormes($message));
}
?>


<script>
    if ('<?php echo $proceed; ?>' === "1") {
        setTimeout(function() {
            gotourl("staff?staff=<?php echo encurl($user_det['uid']); ?>");
        }, 2000);
    }
</script>
