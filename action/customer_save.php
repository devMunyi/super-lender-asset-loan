<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");

$userd = session_details();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}

//== restrict adding lead between "05:30AM" and "05:30PM" 
if (!isWithinWorkingHours() && $retrict_working_hours == 1) {
    exit(errormes("⚠️ You cannot add lead outside working hours!"));
}


$full_name = sanitizeAndEscape($_POST['full_name'], $con);
$primary_mobile = make_phone_valid($_POST['primary_mobile']);
$enc_phone = hash('sha256', $primary_mobile);
$email_address = sanitizeAndEscape($_POST['email_address'], $con);
$physical_address = urldecode(sanitizeAndEscape($_POST['physical_address'], $con));
$town = $_POST['town'];
$national_id = sanitizeAndEscape($_POST['national_id'], $con);
$gender = $_POST['gender'];
$dob = $_POST['dob'];
$added_by = $userd['uid'];
$added_date = $fulldate;
$branch = intval($_POST['branch']);
$group_id = $_POST['group_id'];
//$agent = $_POST['agent'];
$agent = $added_by;
$primary_product = $_POST['primary_product'] ? $_POST['primary_product'] : 1;
$loan_limit = doubleval(sanitizeAndEscape($_POST['loan_limit'], $con));
$events = "Customer created at [$fulldate] by [" . $userd['name'] . "][" . $userd['uid'] . "]";
$status = 3; // default status is lead
$geolocation = urldecode(sanitizeAndEscape($_POST['geolocation'] ?? "", $con));
$phone_number_provider = ($cc == 254) ? 1 : intval($_POST['phone_number_provider']);


if (intval($email_disabled) == 0) {
    if ((input_available($email_address)) == 1) {
        if ((emailOk($email_address)) == 0) {
            // exit(errormes("Email is invalid"));
            // exit();
        } else {
            $email_exists = checkrowexists('o_customers', "email_address='$email_address'");
            if ($email_exists == 1) {
                //   exit(errormes("Email Exists"));
                //   exit();
            }
        }
    } else {
        $email_address = null;
    }
}

///////////--------------------Validation
if ((input_available($full_name)) == 0) {
    exit(errormes("Name is invalid/required"));
}


if ((validate_phone($primary_mobile)) == 0) {
    exit(errormes("Mobile number invalid"));
} else {
    $phone_exists = checkrowexists('o_customers', "primary_mobile='$primary_mobile'");
    if ($phone_exists == 1) {
        exit(errormes("Primary Mobile Number Exists"));
    }
}


// validate phone number provider
if ($phone_number_provider > 0) {
} else {
    exit(errormes("Please Select Phone Number Provider"));
}

////----Check if customer exists as an alternative number
$exists_alt = checkrowexists('o_customer_contacts', "value='$primary_mobile' AND status=1");
if ($exists_alt == 1) {
    exit(errormes("The phone number exists as an alternative number"));
}


if (intval($national_id_disabled) == 0) {
    if ((input_length($national_id, 5)) == 0) {
        exit(errormes("National Id Required"));
    } else {
        $id_exists = checkrowexists('o_customers', "national_id='$national_id'");
        if ($id_exists == 1) {
            exit(errormes("National ID Already Exists"));
        }
    }
}

if ($gender != 'M' && $gender != 'F') {
    exit(errormes("Gender is required"));
}

if ((input_length($physical_address, 10)) == 0) {
    exit(errormes($switch_home_with_business_address ? "Business Address is Required and be Descriptive" : "Home Address is Required and be Descriptive"));
}

if (intval($dob_disabled) == 0) {
    if ((input_length($dob, 10)) == 0) {
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

} else {
    $dob = '0000-00-00';
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


///-----------Geo Location check
if ($geo_location_enforced == 1) {
    if (input_length($geolocation, 10) == 0) {
        exit(errormes("Geo Location Required. Please enable"));
    }
}

////----------Geo location
if ($town > 0) {
} else {
    // exit(errormes("Town is required"));
    // exit();
}

if ($branch > 0) {
} else {
    exit(errormes("Branch is required"));
}



/////-------Check the after save script
$scr1 = after_script($primary_product, "CUSTOMER_BEFORE_CREATE");
if ($scr1 != '0') {
    include_once "../$scr1";
}

////-------End of check after save script

///////////===================Validation


// Define the base fields and values arrays
$fds = array('full_name', 'primary_mobile', 'enc_phone', 'phone_number_provider', 'email_address', 'physical_address', 'geolocation', 'town', 'gender', 'dob', 'added_by', 'current_agent', 'added_date', 'branch', 'primary_product', 'loan_limit', 'status');
$vals = array("$full_name", "$primary_mobile", "$enc_phone", "$phone_number_provider", "$email_address", "$physical_address", "$geolocation", "$town", "$gender", "$dob", "$added_by", "$agent", "$added_date", "$branch", "$primary_product", "$loan_limit", "$status");

// Conditionally add national_id if it is not empty
if (trim($national_id) != '') {
    $fds[] = 'national_id';
    $vals[] = $national_id;
}

$create = addtodb('o_customers', $fds, $vals);
if ($create == 1) {
    echo sucmes('Customer Saved Successfully');
    $customer_id = encurl(fetchrow('o_customers', "primary_mobile='$primary_mobile'", "uid"));
    $proceed = 1;
    $cust_id = decurl($customer_id);
    store_event('o_customers', $cust_id, "$events");

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

    /////-------Check the after save script
    $scr = after_script($primary_product, "CUSTOMER_CREATE");

    // 
    if ($scr !== 0) {
        include_once("../$scr");
        // echo "CC.C => ". $scr;
    } else {
        // echo "CC.NC => ". $scr;
    }

    ////-------End of check after save script
} else {
    echo errormes('Unable to Save Customer' . $create);
}

?>

<script>
    if ('<?php echo $proceed; ?>' === "1") {
        setTimeout(function () {
            gotourl("customers?customer-add-edit=<?php echo $customer_id; ?>&contact");
        }, 1500);
    }
</script>