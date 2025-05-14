<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

/*

UPDATE o_loans 
SET other_info = JSON_SET(
    IFNULL(other_info, '{}'), 
    '$.CRB_LISTED', '1',
    '$.CRB_LISTED_DATE', '$date'
) 
WHERE uid IN ($loan_ids)
AND (
    JSON_UNQUOTE(JSON_EXTRACT(other_info, '$.CRB_LISTED')) IS NULL 
    OR JSON_UNQUOTE(JSON_EXTRACT(other_info, '$.CRB_LISTED')) != '1'
    OR JSON_UNQUOTE(JSON_EXTRACT(other_info, '$.CRB_LISTED_DATE')) IS NULL
    OR JSON_UNQUOTE(JSON_EXTRACT(other_info, '$.CRB_LISTED_DATE')) != '$date'
);


*/

/////----------Session Check
$userd = session_details();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}
/////---------End of session check

$added_by = $userd['uid'];
$title = sanitizeAndEscape($_POST['title'], $con);
$description = sanitizeAndEscape($_POST['description'], $con);
$campaign_date = $_POST['date'];

$target_customers = $_POST['target_customers'];
$general_audience = $_POST['general_audience'];

$file_name = $_FILES['target_customers']['name'];
$file_size = $_FILES['target_customers']['size'];
$file_tmp = $_FILES['target_customers']['tmp_name'];

$upload_location = '../../crb_uploads/';

$status = $_POST['status'];

////////////////validation

if (input_available($title) == 0) {
    exit(errormes("Title is required"));
} elseif ((input_length($title, 3)) == 0) {
    exit(errormes("Title is too short"));
} else {
}


if (input_available($campaign_date) == 0) {
    exit(errormes("Date is required"));
} elseif ((input_length($campaign_date, 10)) == 0) {
    exit(errormes("Date is Invalid"));
}



$allowed_formats = "csv";
$allowed_formats_array = explode(",", $allowed_formats);
if ($file_size > 10) {
    if ((file_type($file_name, $allowed_formats_array)) == 0) {
        die(errormes("This file format is not allowed. Only $allowed_formats files"));
    }
} else {
    if ($general_audience > 0) {
        //////---We have audience
    } else {
        die(errormes("File not attached and no audience specified"));
    }
}

/////---------------------Upload file
if ($file_size > 10) {
    $upload = upload_file($file_name, $file_tmp, $upload_location);
    if ($upload === 0) {
        exit(errormes("Error uploading file, please retry"));
    }

    $total_valid = 0;
    $total_invalid = 0;
    $all_numbers = array();

    $open = fopen("$upload_location" . $upload, "r");
    if ($open !== false) {
        while (($data = fgetcsv($open, 100000, ",")) !== FALSE) {
            $phone = trim($data[0]);
            $phone_valid = validate_phone($phone);
            if ($phone_valid == 1) {
                $total_valid = $total_valid + 1;
                array_push($all_numbers, make_phone_valid($phone));
            } else {
                $total_invalid = $total_invalid + 1;
            }
        }

        // close the file
        fclose($open);
    } else {
        exit(errormes("Error reading file"));
    }

    $counts = array_count_values($all_numbers);
    $unique_count = count($counts);

    if ($unique_count != $total_valid) {
        exit(errormes("You have duplicate values in your document"));
    }


    if ($total_invalid > 5) {
        exit(errormes("You have $total_invalid invalid numbers"));
    }
    if ($total_valid < 2) {
        exit(errormes("You have Less than 2 valid numbers"));
    }
} else {
    $upload = $general_audience;
}

///////////------------------Save
$fds = array('name', 'description', 'running_date', 'frequency', 'repetitive', 'target_customers', 'total_customers', 'added_date', 'added_by', 'status');
$vals = array("$title", "$description", "$campaign_date", "0", "0", "$upload", "$total_valid", "$fulldate", "$added_by", "$status");

$create = addtodb('o_campaigns', $fds, $vals);
if ($create == 1) {
    echo sucmes('Campaign created successfully. Please review audiences');
    $proceed = 1;
    $last_campaign = fetchmax('o_campaigns', "name=\"$title\"", "uid", "uid");
    $cid = $last_campaign['uid'];
} else {
    echo errormes('Unable to create campaign' . $create);
}

mysqli_close($con);

?>


<script>
    if ('<?php echo $proceed ?>') {
        setTimeout(function() {
            gotourl('broadcasts?campaign=<?php echo encurl($cid); ?>');
        }, 1500);

    }
</script>