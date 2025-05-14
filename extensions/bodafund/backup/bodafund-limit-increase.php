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



//echo $amount.$given_date;

$given_ago = datediff3($given_date, $date);
//echo $given_ago;
if($given_ago > 6){

    ////------Last Limit was given more than 6 days ago
    if ($total_loans_taken % 2 === 0 && $total_loans_taken > 1) {
        ////------2, 4, 6, 8 loans taken
        $times_taken = countXInY(2, $given_ago);
        ////-----New Limit, 20% of previous limit
        $new_limit_add = $amount * 0.2;

        if($new_limit_add < 500){
            $new_limit_add = 500;
        }

        $savings =  totaltable('o_incoming_payments',"status=1 AND payment_category=4 AND customer_id='$customer_id'","amount");

        $new_limit = $savings + $new_limit_add + $amount;
        $fds = array('customer_uid','amount','given_date','given_by','comments','status');
        $vals = array("$customer_id","$new_limit","$fulldate","0","Increased by system because of repaying 2 loans","1");
        $create = addtodb('o_customer_limits', $fds, $vals);
        if($create == 1){

                $update_cust = updatedb('o_customers', "loan_limit='$new_limit'", "uid='$customer_id'");
               // echo sucmes("Limit updated successfully");
               $message = "Congratulations, we have increased your loan limit to $new_limit. Continue making timely payments and weekly savings to keep a good standing. Dial *789*600# to continue";
            //  echo "$message to $primary_mobile <br/>";

            $fds = array('phone','message_body','queued_date','created_by','status');
            $vals = array($primary_mobile,"$message", "$fulldate","1","1");
            $save_ = addtodb('o_sms_outgoing', $fds, $vals);

        }
        else{
          //  echo errormes("Unable to create limit. Please retry");
        }

      //  echo "$customer_id $new_limit <br/>";



    }


}





