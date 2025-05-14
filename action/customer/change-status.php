<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$userd = session_details();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}

$statusCodeNames = table_to_obj('o_customer_statuses', "uid > 0", "1000", "code", "name");
$cid = intval($_POST['customer_id']);
$new_status_code = intval($_POST['status']);

if ($cid > 0) {
    $cid = decurl($cid);
} else {
    die(errormes("Customer invalid"));
}

$customer_current_info = fetchonerow('o_customers', "uid='$cid'", "primary_product, status");
$current_status_code = $customer_current_info['status'];
$primary_product = $customer_current_info['primary_product'];


//=== Check if the customer is already in the new status
if ($current_status_code == $new_status_code) {
    exit(notice("Customer is already in {$statusCodeNames[$new_status_code]} status"));
}


$general_perm = permission($userd['uid'], 'o_customer_statuses', "0", "general_");
if ($general_perm != 1) {
    // handle blocking permission
    if ($new_status_code == 2) {
        $block_customer = permission($userd['uid'], 'o_customers', "0", "BLOCK");
        if ($block_customer != 1) {
            exit(errormes("You don't have permission to block customer"));
        }
    }
    // handle unblocking permission
    elseif ($new_status_code == 1 && $current_status_code == 2) {
        $unblock_customer = permission($userd['uid'], 'o_customers', 0, "UNBLOCK");
        if ($unblock_customer != 1) {
            exit(errormes("You don't have permission to unblock customer"));
        }
    } else if ($current_status_code == 3 && $new_status_code == 1) {
        // converting Lead to active customer
        $update_customer_in_status = permission($userd['uid'], 'o_customers', 0, "CONVERT_LEAD");
        if ($update_customer_in_status != 1) {
            exit(errormes("You don't have permission to convert Lead to Active customer"));
        } 

        //=== check if national id and dob are required
        if ($national_id_disabled == 1 || $dob_disabled == 1) {
            $customerDet = fetchmaxid('o_customers', "uid='$cid'", "national_id, dob");

            if ($national_id_disabled == 1 && empty(trim($customerDet['national_id']))) {
                exit(errormes("National ID is required Before Converting Lead to Active Customer!"));
            }
            if ($dob_disabled == 1 && (empty(trim($customerDet['dob'])) && trim($customerDet['dob']) == '0000-00-00')) {
                exit(errormes("Date of Birth is required Before Converting Lead to Active Customer!"));
            }
        }
    } else {
        if ($current_status_code == $new_status_code) {
            exit(errormes("Customer is already in {$statusCodeNames[$new_status_code]} status"));
        } else {
            $update_customer_in_status = permission($userd['uid'], 'o_customer_statuses', "$current_status_code", "update_");
            if ($update_customer_in_status != 1) {
                exit(errormes("You don't have permission to change {$statusCodeNames[$current_status_code]} customer to {$statusCodeNames[$new_status_code]}"));
            }
        }
    }
}


// LEAD CONVERSION_VERIFIER
if ($current_status_code == 3 && $new_status_code == 1) {
    $customer_id = $cid;
    $scr = after_script($primary_product, "LEAD_CONVERSION_VERIFIER");
    if ($scr != '0') {
        include_once "../../$scr";
    }
}

$update = updatedb("o_customers", "status=$new_status_code", "uid='$cid'");
if ($update == 1) {
    echo sucmes("Success updating client");
    $events = "Customer status changed to." . $statusCodeNames[$new_status_code] . "($new_status_code) by " . $userd['name'] . "(" . $userd['uid'] . ")";
    store_event('o_customers', $cid, "$events");
    $proceed = 1;
} else {
    echo errormes("Error updating client");
}


mysqli_close($con);
?>

<script>
    if ('<?php echo $proceed; ?>' === "1") {
        setTimeout(function () {
            reload();
        }, 2000);
    }
</script>