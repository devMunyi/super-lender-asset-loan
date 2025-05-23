<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$contact_id = $_POST['contact_id'];
$contact_type = $_POST['contact_type'];
$customer_id = $_POST['customer_id'];
$value = trim($_POST['value']);
$status = 1;
$userd = session_details();

$update = permission($userd['uid'], 'o_customer_contacts', "0", "update_");
if ($update != 1) {
    die(errormes("You don't have permission to update contact"));
}

if ($customer_id > 0) {
    $customer_id_dec = decurl($customer_id);
}

if (($contact_type) > 0) {
    $contact_id_dec = decurl($contact_id);

    //ensure details field is filled
    if ((input_available($value)) == 0) {
        echo errormes("Please enter value to fill details field");
        die();
    }

    //validate email
    if ($contact_type == 3) {
        if ((emailOk($value)) == 0) {
            die(errormes("Email is invalid"));
        }
    } elseif ($contact_type == 1 || $contact_type == 2) {
        $filtered_phone_val = make_phone_valid($value);
        if (validate_phone($filtered_phone_val) == 0) {
            die(errormes("Mobile number invalid"));
        }
    }
    //////---------Check if contact type exists
    if ($contact_type == 1 || $contact_type == 2) {
        $exists = checkrowexists("o_customer_contacts", "(contact_type = 1 OR contact_type = 2) AND (value = \"$filtered_phone_val\") AND status = 1 AND (uid != $contact_id_dec OR customer_id != $customer_id_dec)");
    } else {
        $exists = checkrowexists("o_customer_contacts", "contact_type = 3 AND (value = \"$value\") AND status = 1 AND (uid != $contact_id_dec OR customer_id != $customer_id_dec)");
    }


    if ($exists == 1) {
        if ($contact_type == 3) {
            die(errormes("This Email In Use By Another User"));
        } elseif ($contact_type == 1 || $contact_type == 2)
            die(errormes('This Phone Number In Use By Another User'));
        exit();
    }

    //////---------Check if contact type exists in o_customers table
    if ($contact_type == 1 || $contact_type == 2) {
        $exists_4 = checkrowexists("o_customers", "primary_number = \"$filtered_phone_val\" AND uid != $customer_id_dec");
    } elseif ($contact_type == 3 && !empty($value)) {
        $exists_5 = checkrowexists("o_customers", "email = \"$value\" AND uid != $customer_id_dec");
    }

    if ($exists_4 == 1) {
        echo errormes("Phone Number Already Exists as Primary Number!");
        die();
    } elseif ($exists_5 == 1) {
        echo errormes("Email Already Exists as Primary Email!");
        die();
    }
} else {
    echo  errormes("Please select Contact Type");
    die();
}



if ($contact_type == 1 || $contact_type == 2) {
    $enc_phone = hash('sha256', $filtered_phone_val);
    $create = updatedb('o_customer_contacts', "contact_type='$contact_type', value='$filtered_phone_val', enc_phone='$enc_phone'", "uid='" . decurl($contact_id) . "'");
} else {
    $create = updatedb('o_customer_contacts', "contact_type=$contact_type, value=\"$value\"", "uid='" . decurl($contact_id) . "'");
}

if ($create == 1) {
    echo sucmes('Contact Updated Successfully');
    $proceed = 1;
} else {
    echo errormes('Unable to Update Contact');
}


?>
<script>
    if ('<?php echo $proceed; ?>' === "1") {
        setTimeout(function() {
            reload();
        }, 2500)
        //contact_list('<?php //echo $customer_id; 
                        ?>','EDIT');
    }
</script>