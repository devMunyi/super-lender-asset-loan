<?php
include_once ("boda-bulk.php");
$mob = make_phone_valid($primary_mobile);
$cust_id = decurl($customer_id);

$new_pin = rand(1000, 9999);
$upd = updatedb('o_customers',"pin_='".md5($new_pin)."'","primary_mobile='$mob'");

$message = "Welcome to BodaFund! We are happy to have you on board! Access loans and other services: dial *789*600# or download app at: https://shorturl.at/dzNT2. Your temporary PIN is $new_pin";

///----Make the account active
$update = updatedb('o_customers',"status=1", "primary_mobile='$mob'");

$send = send_bulk(make_phone_valid($mob), $message, 0)."<br/>";
$fds = array('phone','message_body','queued_date','sent_date','created_by','status');
$vals = array(make_phone_valid($mob),"$message", "$fulldate","$fulldate","1","2");
$save_ = addtodb('o_sms_outgoing', $fds, $vals);
//echo sucmes($save_);
