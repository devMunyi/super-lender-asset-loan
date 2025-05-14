<?php
//echo ">>>";
///-------Passports
//$passport = fetchrow('o_documents',"tbl='o_customers' AND rec='$customer_id' AND category='1'","uid");
$docs = countotal('o_documents',"tbl='o_customers' AND rec='$customer_id' AND category in (1,2,3)","uid");
///-------ID
///-------4 Referees
//echo sucmes($customer_id.'jjsj');
$total_referees = countotal('o_customer_referees',"customer_id='$customer_id' AND status=1","uid");
///------Business/Home direction
$customer_det = fetchonerow('o_customers',"uid='$customer_id'","sec_data, physical_address");
$sec_data = $customer_det['sec_data'];
$sec_data_obj = json_decode($sec_data, true);
///--18
$business_location = $sec_data_obj['19'];

$interaction = fetchrow('o_customer_conversations',"customer_id='$customer_id' AND status=1","uid");

if($docs >= 3 && $total_referees >= 4 AND (input_length($business_location, 8) == 1) AND $interaction > 0){
    ////----Required data found
}
else{
    ///----Return the customer to lead until these details are filled
   // $update = updatedb('');
   // updatedb('o_customers',"status=3","uid='$customer_id'");
   // echo store_event('o_customers',$customer_id,"Customer reverted to lead until other data are filled
    echo errormes("Please fill all customer data. 4 referees, Passport photo, Home and Business address, Interaction");
    die();
   // (Passport photo, 4 referees, Business Location)");
}




//echo "<<<".decurl($customer_id);