<?php
// echo ">>>";
///-------Passports
//$passport = fetchrow('o_documents',"tbl='o_customers' AND rec='$customer_id' AND category='1'","uid");
$docs = countotal('o_documents',"tbl='o_customers' AND rec='$customer_id' AND category in (1,2,3)","uid");
///-------ID

///-------4 Referees
//echo sucmes($customer_id.'jjsj');
$total_referees = countotal('o_customer_referees', "customer_id='$customer_id' AND status=1", "uid");
///------Business/Home direction
$customer_det = fetchonerow('o_customers', "uid='$customer_id'", "sec_data, physical_address");
$sec_data = $customer_det['sec_data'];
$sec_data_obj = json_decode($sec_data, true);
///--18
$business_location = $sec_data_obj['19'];

$interaction = fetchrow('o_customer_conversations', "customer_id='$customer_id' AND status=1", "uid");


// validate passport
if ($docs >= 3) {
    // Passport photo is valid
} else {
    // Passport photo is missing or invalid
    echo errormes("Please Ensure Passport Photo  and National ID's are Uploaded.");
    die();
}

// validate referees
if (intval($total_referees) >= 4) {
    // Referees count is valid
} else {
    // Referees count is insufficient
    echo errormes("Please provide at least 4 referees.");
    die();
}


// validate business location
if (input_length($business_location, 8) == 1) {
    // Business location length is valid
} else {
    // Business location length is invalid
    echo errormes("Please provide a valid & descriptive Business location.");
    die();
}


// validate interactions
if (intval($interaction) > 0) {
    // Interaction is valid
} else {
    // Interaction is missing or invalid
    echo errormes("Please provide a valid Interaction.");
    die();
}

if($product_id == 2){
    ///-----Verify inua biashara
    $supplier = fetchrow('o_group_members',"customer_id='$customer_id' AND status=1","group_id");
    if($supplier < 1){
       echo errormes("Customer has not been added to any supplier group");
       die();
    }
    else{
        $sup = fetchonerow('o_customer_groups', "uid='$supplier'", "till, status, group_phone");
        if($sup['status'] != 1){
            echo errormes("The supplier is disabled in the system");
            die();
        }
        if(validate_phone($sup['group_phone']) != 1){
            echo errormes("The supplier phone number is not valid in the system");
            die();
        }
    }


}