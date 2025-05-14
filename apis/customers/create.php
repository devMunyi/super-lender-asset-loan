<?php
$expected_http_method = 'POST';
include_once("../../configs/cors.php");
include_once("../../vendor/autoload.php");
include_once("../../configs/conn.inc");
include_once("../../configs/jwt.php");
include_once("../../php_functions/jwtAuthUtils.php");
include_once("../../php_functions/jwtAuthenticator.php");
include_once("../../php_functions/functions.php");


$data = json_decode(file_get_contents('php://input'), true);

$full_name = sanitizeAndEscape($data['full_name'] ?? "", $con);
$primary_mobile = make_phone_valid($data['primary_mobile'] ?? "");
$enc_phone = hash('sha256', $primary_mobile);
$email_address = sanitizeAndEscape($data['email_address'] ?? "", $con);
$physical_address = sanitizeAndEscape($data['physical_address'] ?? "", $con);
$geolocation =  sanitizeAndEscape($data['geolocation'] ?? "", $con);
$national_id = sanitizeAndEscape($data['national_id'] ?? "", $con);
$gender = sanitizeAndEscape($data['gender'] ?? "", $con);
$dob = sanitizeAndEscape($data['dob'] ?? "", $con);
$branch = intval($data['branch'] ?? 0);
$primary_product = intval($data['primary_product'] ?? 0);
$loan_limit = 0;
$status = 3; // default status is lead
$phone_number_provider = ($cc == 254) ? 1 : intval($data['phone_number_provider'] ?? 0);
$group_id = intval($_POST['group_id'] ?? 0);

;

try {
    //// ================== Validation starts here
    if ((input_available($full_name)) == 0) {
        sendApiResponse(400, "Name is invalid/required");
    }


    if ((validate_phone($primary_mobile)) == 0) {
        sendApiResponse(400, "Mobile number invalid");
    } else {
        $phone_exists = checkrowexists('o_customers', "primary_mobile='$primary_mobile'");
        if ($phone_exists == 1) {
            sendApiResponse(409, "Phone number Exists");
        }
    }

    if ($phone_number_provider == 0) {
        sendApiResponse(400, "Phone number provider is invalid");
    }

    ////----Check if customer exists as an alternative number
    $exists_alt = checkrowexists('o_customer_contacts', "value='$primary_mobile' AND status=1");
    if ($exists_alt == 1) {
        sendApiResponse(409, "Phone number Exists as an alternative number");
    }


    // validate national id
    if (intval($national_id_disabled) == 0) {
        if ((input_length($national_id, 5)) == 0) {
            sendApiResponse(400, "National ID is required");
        } else {
            $id_exists = checkrowexists('o_customers', "national_id='$national_id'");
            if ($id_exists == 1) {
                sendApiResponse(409, "National ID Already Exists");
            }
        }
    }

    if ($gender != 'M' && $gender != 'F') {
        sendApiResponse(400, "Gender is required");
    }

    if ((input_length($physical_address, 10)) == 0) {
        $message = $switch_home_with_business_address ? "Business Address is Required and be Descriptive" :  "Home Address is Required and be Descriptive";
        sendApiResponse(400, $message);
    }


    if (intval($dob_disabled) == 0) {
        if ((input_length($dob, 10)) == 0) {
            sendApiResponse(400, "Date of birth required/Invalid");
        }
    } else {
        $dob = '0000-00-00';
    }

    if (isset($customer_age_limit) && !empty($customer_age_limit)) {
        $lower_limit = $customer_age_limit[0];
        $upper_limit = $customer_age_limit[1];

        if (date_greater($dob, $date)) {
            sendApiResponse(400, "The Date of birth is invalid");
        }

        $age = ceil(datediff3($dob, $date) / 365);
        if ($age < $lower_limit || $age > $upper_limit) {
            sendApiResponse(400, "The client's age must be between $lower_limit - $upper_limit");
        }
    }

    ///-----------Geo Location check
    if ($geo_location_enforced == 1) {
        if (input_length($geolocation, 10) == 0) {
            sendApiResponse(400, "Geo Location Required. Please enable");
        }
    }

    if ($branch == 0) {
        sendApiResponse(400, "Branch is required");
    }

    /////-------Check the after save script
    $scr1 = after_script($primary_product, "CUSTOMER_BEFORE_CREATE");
    if ($scr1 != '0') {
        include_once "../$scr1";
    }

    ////-------End of check after save script

    ///////////===================Validation ends here
    $columns = array('full_name', 'primary_mobile', 'enc_phone', 'phone_number_provider', 'email_address', 'physical_address', 'geolocation', 'national_id', 'gender', 'dob', 'added_by', 'current_agent', 'added_date', 'branch', 'primary_product', 'loan_limit', 'status');
    $values = array("$full_name", "$primary_mobile", "$enc_phone", "$phone_number_provider", "$email_address", "$physical_address", "$geolocation", "$national_id", "$gender", "$dob", $added_by, $added_by, "$fulldate", $branch, $primary_product, $loan_limit, $status);


    $create = addtodb('o_customers', $columns, $values);
    if ($create == 1) {
        $userd = json_decode($user);
        $events = "Customer created at [$fulldate] by [" . $user->username . "][" . $user->uid . "]";
        $cust_id = fetchrow('o_customers', "primary_mobile='$primary_mobile'", "uid");
        store_event_return_void('o_customers', $cust_id, "$events");


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
        if ($scr !== 0) {
            include_once("../$scr");
            // echo "CC.C => ". $scr;
        } else {
            // echo "CC.NC => ". $scr;
        }

        ////-------End of check after save script

        sendApiResponse(200, "Customer added successfully", "OK", ['customer_id' => $cust_id]);
    }else{
        sendApiResponse(500, "An error occurred while adding customer");
    }
} catch (Exception $e) {
    sendApiResponse(500, "An error occurred while adding customer");
}
