<?php

//echo sucmes("kdkd");
///-------Check if client has saved since he was signed up
$customer_d = fetchonerow('o_customers',"uid=$customer_id","date(added_date) as added_date, loan_limit, primary_mobile");
$customer_join_date = $customer_d['added_date'];
$loan_limit = $customer_d['loan_limit'];
$primary_mobile = $customer_d['primary_mobile'];
$total_loans_taken = countotal('o_loans',"customer_id=$customer_id AND disbursed=1 AND paid=1","uid");

$latest_loan = fetchmaxid('o_loans',"customer_id=$customer_id AND disbursed=1 AND paid=1","uid, loan_amount");
$latest_amount = $latest_loan['loan_amount'];

$last_limit = fetchmaxid('o_customer_limits',"customer_uid='$customer_id' AND status=1","uid, amount, given_date");
$amount = $last_limit['amount'];
$given_date = $last_limit['given_date'];
$lid = $last_limit['uid'];
if($lid > 0){}
else{
    $amount = $loan_limit;
    $given_date = $customer_join_date;
}

$total_savings = totaltable('o_incoming_payments',"customer_id = ".$customer_id." AND status=1 AND payment_category=4","amount");

$weeks_passed = weeksPassedSince($customer_join_date);
$required_savings = $weeks_passed * 50;

if($total_savings >= $required_savings){
    $has_saved = 1;
}
else{
    $has_saved = 0;
}

$join_ago = datediff3($customer_join_date, $date);



//echo $amount.$given_date;

$given_ago = datediff3($given_date, $date);
//echo $given_ago;
if($loan_limit < 5000){    /////If loan_limit given more than 6 days ago and client joined more than 2 weeks ago and current loan limit is < 5000



         if($total_loans_taken >= 3 && $given_ago >= 5 && $join_ago >= 14 && $has_saved == 1){

             if ($total_loans_taken % 3 == 0) {

                 $new_limit = $loan_limit + 150;
                 //echo "It goes into 3 $timesInto3 times.\n";
             } else {
               //  echo "$number is not a multiple of 3.\n";
             }







        if($new_limit > $loan_limit) {
            $fds = array('customer_uid', 'amount', 'given_date', 'given_by', 'comments', 'status');
            $vals = array("$customer_id", "$new_limit", "$fulldate", "0", "Increased by system because of repaying 2 loans", "1");
            $create = addtodb('o_customer_limits', $fds, $vals);
            if ($create == 1) {

                $update_cust = updatedb('o_customers', "loan_limit='$new_limit'", "uid='$customer_id'");
                // echo sucmes("Limit updated successfully");
                $message = "Congratulations, we have increased your loan limit to $new_limit. Continue making timely payments and weekly savings to keep a good standing. Dial *789*600# to continue";
                //  echo "$message to $primary_mobile <br/>";

                $fds = array('phone', 'message_body', 'queued_date', 'created_by', 'status');
                $vals = array($primary_mobile, "$message", "$fulldate", "1", "1");
                $save_ = addtodb('o_sms_outgoing', $fds, $vals);

            } else {
                //  echo errormes("Unable to create limit. Please retry");
            }
        }
      //  echo "$customer_id $new_limit <br/>";


         }



}


function weeksPassedSince($specificDate) {
    $now = new DateTime();
    $startDate = new DateTime($specificDate);

    $interval = $now->diff($startDate);
    $weeksPassed = floor($interval->days / 7);

    return $weeksPassed;
}




