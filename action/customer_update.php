<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}

$update_customer = permission($userd['uid'], 'o_customers', "0", "update_");
if ($update_customer != 1) {
    exit(errormes("You can not edit a customer"));
}



$update_number = permission($userd['uid'], 'o_customer_contacts', "0", "update_");
/////---------End of session check

$uid = intval($_POST['cid']);
$full_name = sanitizeAndEscape($_POST['full_name'], $con);
$primary_mobile = make_phone_valid($_POST['primary_mobile']);
$enc_phone = hash('sha256', $primary_mobile);
$email_address = sanitizeAndEscape($_POST['email_address'], $con);
$physical_address = urldecode(sanitizeAndEscape($_POST['physical_address'], $con));
$town = $_POST['town'];
$national_id = sanitizeAndEscape($_POST['national_id'], $con);
$gender = $_POST['gender'];
$dob = $_POST['dob'];
$added_by = intval($userd['uid']);
$added_date = $fulldate;
$branch = intval($_POST['branch']);
$group_id = $_POST['group_id'];
$primary_product = intval($_POST['primary_product']);
$loan_limit = doubleval(sanitizeAndEscape($_POST['loan_limit'], $con));
$agent = intval($_POST['agent']);

$events = "Customer updated at [$fulldate] by [" . $userd['name'] . "{" . $userd['uid'] . "}]";
$geolocation = urldecode(sanitizeAndEscape($_POST['geolocation'] ?? "", $con));
$phone_number_provider = ($cc == 254) ? 1 : intval($_POST['phone_number_provider']);
$update_b2c_validation_flag = false;


if ($uid > 0) {
    $customer_id = decurl($uid);
} else {
    exit(errormes("User Error. Please select user again"));
}

if ((input_available($email_address)) == 1) {
    if ((emailOk($email_address)) == 0) {
        //  exit(errormes("Email is invalid"));
        //
    } else {
        $email_exists = checkrowexists('o_customers', "email_address='$email_address' AND uid !='$customer_id'");
        if ($email_exists == 1) {
            //    exit(errormes("Email Exists"));
            //  
        }
    }
} else {
    $email_address = null;
}
///////////--------------------Validation
if ((input_available($full_name)) == 0) {
    exit(errormes("Name is invalid/required"));
}


if ((validate_phone($primary_mobile)) == 0) {
    exit(errormes("Mobile number invalid"));
} else {
    $andmob = "";
    $phone_exists = checkrowexists('o_customers', "primary_mobile='$primary_mobile' AND uid !='$customer_id'");
    if ($phone_exists == 1) {
        exit(errormes("Primary Mobile Number Exists"));

    }
    $current_d = fetchonerow('o_customers', "uid='$customer_id'", "primary_mobile, status, added_by, full_name, email_address, physical_address, national_id, branch");
    $current_status_code = $current_d['status'];
    $current_mobile = $current_d['primary_mobile'];
    $was_added_by = $current_d['added_by'];


    $cur_primary_mobile = $current_d['primary_mobile'];
    $cur_full_name = $current_d['full_name'];
    $cur_email = $current_d['email_address'];
    $cur_address = $current_d['physical_address'];
    $cur_national_id = $current_d['national_id'];
    $cur_branch = $current_d['branch'];


    $changes = "";
    if ($cur_primary_mobile != $primary_mobile) {
        $changes .= " [Phone: $cur_primary_mobile -> $primary_mobile]";
    }
    if ($cur_email != $email_address) {
        $changes .= " [Email: $cur_email -> $email_address]";
    }
    $striped_full_name = stripslashes($full_name);
    if ($cur_full_name != $striped_full_name) {
        $changes .= " [Name: $cur_full_name -> $striped_full_name]";
    }
    if ($cur_national_id != $national_id) {
        $changes .= " [ID: $cur_national_id -> $national_id]";
        $update_b2c_validation_flag = true; // handle b2c skip validation flag
    }
    $striped_address = stripslashes($physical_address);
    if ($cur_address != $striped_address) {
        $changes .= " [Address: {$cur_address} -> {$striped_address}]";
    }
    if ($cur_branch != $branch) {
        $branchNames = table_to_obj("o_branches", "uid IN ($cur_branch, $branch)", "100", "uid", "name");
        $changes .= " [Branch: {$branchNames[$cur_branch]} -> {$branchNames[$branch]}]";
    }

    $update_customer_status = permission($userd['uid'], 'o_customer_statuses', "$current_status_code", "update_");
    if ($update_customer_status != 1) {
        $current_status_name = fetchrow('o_customer_statuses', "code='$current_status_code'", "name");
        exit(errormes("You cannot update customer who is in $current_status_name status"));
    }




    /////----Check if user can update leads

    /////--------Check if a user is updating a lead belonging to another person who is in the branch
    $staff_los = table_to_array('o_users', "user_group=7 AND status =1 AND branch='$branch'", "100000", "uid");

    if ($current_mobile != $primary_mobile) {
        if ($update_number == 0 && in_array($current_status_code, [1, 2])) {
            exit(errormes("You do not have permission to edit number"));
        }
        ////--------There is attempt change of phone number, check if the old number has a loan
        ////----Check if user is super admin or admin
        $has_loan = checkrowexists('o_loans', "account_number='$current_mobile' AND disbursed=1 AND paid=0 AND status!=0");
        if ($has_loan == 1) {
            exit(errormes("The phone number you are trying to change has a loan already"));
        } else {
            $andmob = ", primary_mobile='$primary_mobile', enc_phone='$enc_phone'";
        }
    }
}

// validate phone number provider
if ($phone_number_provider > 0) {
} else {
    $phone_number_provider = 1;
}

if ((input_length($national_id, 5)) == 0) {
    exit(errormes("National Id Required"));
} else {
    $id_exists = checkrowexists('o_customers', "national_id='$national_id' AND uid !='$customer_id'");
    if ($id_exists == 1) {
        exit(errormes("National ID Already Exists"));
    }
}

if ($gender != 'M' && $gender != 'F') {
    exit(errormes("Gender is required"));
}

if ((input_length($physical_address, 10)) == 0) {
    exit(errormes($switch_home_with_business_address ? "Business Address is Required and be Descriptive" : "Home Address is Required and be Descriptive"));
}

if ((input_length($dob, 10)) == 0 || $dob == '0000-00-00') {
    exit(errormes("Date of birth required"));
}

// Ensure customer is >= 18 years old
$dob = date('Y-m-d', strtotime($dob));
$today = date('Y-m-d');
$minAgeDate = date('Y-m-d', strtotime('-18 years', strtotime($today)));

if ($dob > $minAgeDate) {
    // Customer is under 18
    exit(errormes("Customer must be at least 18 years old."));
}


/////-----------------------Check if age falls in the required bracket
///
if (isset($customer_age_limit) && !empty($customer_age_limit)) {
    $lower_limit = $customer_age_limit[0];
    $upper_limit = $customer_age_limit[1];

    if (date_greater($dob, $date)) {
        exit(errormes("The Date of birth is invalid"));
    }

    $age = ceil(datediff3($dob, $date) / 365);
    if ($age < $lower_limit || $age > $upper_limit) {
        exit(errormes("The client's age must be between $lower_limit - $upper_limit"));
    }
}
///
/////-------------------


if ($town > 0) {
} else {
    //  exit(errormes("Town is required"));
    //
}

if ($branch > 0) {
} else {
    exit(errormes("Branch is required"));
}

if ($primary_product > 0) {
} else {
    exit(errormes("Primary Product is required"));
}





///////////===================Validation

$update_flds = " full_name='$full_name',  email_address='$email_address' $andmob, phone_number_provider='$phone_number_provider',  physical_address='$physical_address', geolocation='$geolocation', town='$town',  gender='$gender', dob='$dob', national_id='$national_id',  branch='$branch', primary_product='$primary_product'";

$update = updatedb('o_customers', $update_flds, "uid=" . $customer_id);
if ($update == 1) {
    echo sucmes('Customer Updated Successfully');
    $proceed = 1;
    $cust_id = $customer_id;

    /////----------Check group save
    if ($group_loans == 1) {
        $customer_group = fetchrow('o_group_members', "customer_id='$cust_id' AND status=1", "group_id");
        if ($group_id > 0) {
            //---Current group
            if ($customer_group == $group_id) {
                ///-----Customer is already in group
            }
            if ($customer_group != $group_id) {
                //-----Customer is in different group
                //-----Remove from other groups
                $upd = updatedb('o_group_members', "status=0", "customer_id='$cust_id' AND group_id='$customer_group' AND status=1");
                ///-----Add to group
                $fds = array('group_id', 'customer_id', 'added_date', 'added_by', 'loan_limit', 'status');
                $vals = array("$group_id", "$cust_id", "$fulldate", "$added_by", "$loan_limit", "1");
                $save_ = addtodb('o_group_members', $fds, $vals);
            }
        } else {
            /////-----Group selected is 0
            if ($customer_group > 0) {
                ///////----But they are in a group, remove them
                $upd = updatedb('o_group_members', "status=0", "customer_id='$cust_id' AND group_id='$customer_group' AND status=1");
            }
        }
    }
    /// -----------End of check group save

    // Handle b2c validation flag update
    if ($update_b2c_validation_flag) {
        $affectedRows = handleSkipB2CValidation($customer_id, 1); // parsing 1 will enforce b2c validation back if it was initially disabled

        $changes .= ". B2C Validation Flag Set.";

    }

    store_event('o_customers', "$customer_id", "$events: $changes");

    /////-------Check the after save script
    $scr = after_script($primary_product, "CUSTOMER_UPDATE");

    $cust_id = $customer_id;
    if ($scr !== 0) {
        include_once("../$scr");
    }
    ////-------End of check after save script
} else {
    echo errormes('Unable to Update Customer ' . $update);
}

?>

<script>
    if ('<?php echo $proceed; ?>' === "1") {
        setTimeout(function () {
            gotourl("customers?customer-add-edit=<?php echo $uid; ?>&contact");
        }, 1500);
    }
</script>