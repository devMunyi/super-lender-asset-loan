<?php

///------Business/Home direction
$passport_photo = countotal('o_documents', "tbl='o_customers' AND rec='$customer_id' AND category='1' AND status = 1", "uid");
if ($passport_photo > 0) {
} else {
    // passport photo is missing
    exit(errormes("Please Ensure Passport Photo is Uploaded."));
}

$national_id_front = countotal('o_documents', "tbl='o_customers' AND rec='$customer_id' AND category='2' AND status = 1", "uid");
if ($national_id_front > 0) {
} else {
    // national id front is missing
    exit(errormes("Please Ensure National ID Front Side Photo is Uploaded."));
}

$national_id_back = countotal('o_documents', "tbl='o_customers' AND rec='$customer_id' AND category='3' AND status = 1", "uid");
if ($national_id_back > 0) {
} else {
    // national id back is missing
    exit(errormes("Please Ensure National ID Back Side Photo is Uploaded."));
}

$business_photo = countotal('o_documents', "tbl='o_customers' AND rec='$customer_id' AND category='4' AND status = 1", "uid");
if ($business_photo >= 2) {
} else {
    // business photo is missing
    exit(errormes("Please Ensure at least 2 Business Photos are Uploaded."));
}

$home_asset_photos = countotal('o_documents', "tbl='o_customers' AND rec='$customer_id' AND category='5' AND status = 1","uid");
if ($home_asset_photos >= 2) {
} else {
    // home photos are missings
    exit(errormes("Please Ensure at least 2 Home Asset Photos are Uploaded."));
}


$total_referees = countotal('o_customer_referees', "customer_id='$customer_id' AND status=1", "uid");
if (intval($total_referees) >= 3) {
    // Referees count is valid
} else {
    // Referees count is insufficient
    exit(errormes("Please provide at least 3 referees."));
}


$customer_det = fetchonerow('o_customers', "uid='$customer_id'", "sec_data, physical_address, geolocation");
$sec_data = $customer_det['sec_data'];
$sec_data_obj = json_decode($sec_data, true);

$home_location = $customer_det['physical_address'];
$home_geolocation = $customer_det['geolocation'];

$business_location = trim($sec_data_obj['19']);
$business_geolocation = trim($sec_data_obj['50']);

if(input_length($home_location, 20)) {
    // Home location length is valid
} else {
    // Home location length is invalid
    exit(errormes("Please provide a valid & descriptive Home Physical Address on Bio Information."));
}

if(input_length($home_geolocation, 20) == 1 && strpos($home_geolocation, 'maps') !== false) {
    // Home location length is valid
} else {
    // Home location length is invalid
    exit(errormes("Please provide a valid Home geolocation on Bio Information."));
}


if(input_length($business_location, 8)) {
    // Business location length is valid
} else {
    // Business location length is invalid
    exit(errormes("Please provide a valid & descriptive Business Direction."));
}

if(input_length($business_geolocation, 20) == 1 && strpos($business_geolocation, 'maps') !== false) {
    // Business location length is valid
} else {
    // Business location length is invalid
    exit(errormes("Please provide a valid Business geolocation."));
}



$interaction = fetchrow('o_customer_conversations', "customer_id='$customer_id' AND status=1", "uid");
if (intval($interaction) > 0) {
} else {
    // Interaction is missing or invalid
    exit(errormes("Customer should have at least 1 interaction."));
}
