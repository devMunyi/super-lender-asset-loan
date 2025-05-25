<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$customer_id = $_POST['customer_id'];
$contact_type = $_POST['contact_type'];
$value = trim($_POST['value']);
$status = 1;

$userd = session_details();

if ($customer_id > 0) {
    $customer_id_dec = decurl($customer_id);

}

$create_contact = permission($userd['uid'], 'o_customer_contacts', "0", "create_");
if ($create_contact != 1) {
    exit(errormes("You don't have permission to add contact"));
}

if (($contact_type) > 0) {

    if ((input_available($value)) == 0) {
        exit(errormes("Please enter value to fill details"));
    }

    //validate email
    if ($contact_type == 3) {
        if ((emailOk($value)) == 0) {
            exit(errormes("Email is invalid"));
        }
    } elseif ($contact_type == 1 || $contact_type == 2) {
        $filtered_phone_val = make_phone_valid($value);
        if (validate_phone($filtered_phone_val) == 0) {
            exit(errormes("Mobile number invalid"));
        }
    }

    //////---------Check if contact type exists
    if ($contact_type == 1) {
        $exists_1 = checkrowexists("o_customer_contacts", "(contact_type = 1 OR contact_type = 2) AND value= \"$filtered_phone_val\" AND status = 1");
    } elseif ($contact_type == 2) {
        $exists_2 = checkrowexists("o_customer_contacts", "(contact_type = 2 OR contact_type = 1) AND value = \"$filtered_phone_val\" AND status = 1");
    } elseif ($contact_type == 3) {
        $exists_3 = checkrowexists("o_customer_contacts", "contact_type = 3 AND value = \"$value\" AND status = 1");
    }


    if ($exists_1 == 1 || $exists_2 == 1) {
        exit(errormes("Phone Number Exists."));
    } elseif ($exists_3 == 1) {
        exit(errormes("Email Exists."));
    }

    //////---------Check if contact type exists in o_customers table
    if ($contact_type == 1 || $contact_type == 2) {
        $exists_4 = checkrowexists("o_customers", "primary_number = \"$filtered_phone_val\"");
    } elseif ($contact_type == 3 && !empty($value)) {
        $exists_5 = checkrowexists("o_customers", "email = \"$value\"");
    }

    if ($exists_4 == 1) {
        exit(errormes("Phone Number Already Exists as Primary Number!"));
    } elseif ($exists_5 == 1) {
        exit(errormes("Email Already Exists as Primary Email!"));
    }

} else {
    exit(errormes("Please select Contact Type"));
}

if ($contact_type == 1 || $contact_type == 2) {
    $enc_phone = hash('sha256', $filtered_phone_val);
    $fds = array('customer_id', 'contact_type', 'value', 'enc_phone', 'status');
    $vals = array("$customer_id_dec", "$contact_type", "$filtered_phone_val", "$enc_phone", "$status");
} else {
    $fds = array('customer_id', 'contact_type', 'value', 'status');
    $vals = array("$customer_id_dec", "$contact_type", "$value", "$status");
}
$create = addtodb('o_customer_contacts', $fds, $vals);
if ($create == 1) {
    echo sucmes('Contact Added Successfully');
    $proceed = 1;

} else {
    echo errormes('Unable to Add Contact');
}


?>
<script>

    if ('<?php echo $proceed; ?>' === "1") {
        setTimeout(function () {
            reload();
        }, 2500);
        clear_form('contact_');
        contact_list('<?php echo $customer_id; ?>', 'EDIT');
    }
</script>