<?php
session_start();

$_SESSION['db_name'] = 'stawika_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

$customers_with_limit_array = array();
$customer_limits_array = array();
$date_30 = datesub($date, 0, 0, 30);
////-----Get loans taken in particular period
$customers = fetchtable('o_customers',"loan_limit>=500","uid","asc","100000","uid, full_name, loan_limit, primary_mobile");
while($c = mysqli_fetch_array($customers)){
    $uid = $c['uid'];
    $full_name = $c['full_name'];
    $loan_limit = $c['loan_limit'];
    $primary_mobile = $c['primary_mobile'];
    array_push($customers_with_limit_array, $uid);
    $customer_limits_array[$uid] = $loan_limit;


   // echo "Customer: $uid $full_name $loan_limit $primary_mobile <br/>";
}

$customer_list = implode(',', $customers_with_limit_array);
//echo $customer_list;

////-----Customer loans
$all_loans_array = array();
$loan_customers_array = array();
$loan_status_array = array();
$loan_due_date_array = array();
$loan_last_pay_date_array = array();
$customer_phones_array = array();
$latest_loans_array = array();
$loans = fetchtable("o_loans","customer_id in ($customer_list) AND given_date >= '2021-06-01' AND status !=0","uid","desc", 1000000,"uid, customer_id, account_number, final_due_date, last_pay_date,  loan_amount, status");
while($l = mysqli_fetch_array($loans)){

    $lid = $l['uid'];
    $customer_id = $l['customer_id'];
    $loan_status = $l['status'];
    $due_date = $l['final_due_date'];
    $last_pay_date = $l['last_pay_date'];
    $primary_mobile = $l['account_number'];

    array_push($all_loans_array, $lid);
    $loan_customers_array[$lid] = $customer_id;
    $loan_status_array[$lid] = $loan_status;
    $loan_due_date_array[$lid] = $due_date;
    $loan_last_pay_date_array[$lid] = $last_pay_date;
    $customer_phones_array[$customer_id] = $primary_mobile;
    if($latest_loans_array[$customer_id][1] > 0){
        if($latest_loans_array[$customer_id][2] > 0)
        {
            if($latest_loans_array[$customer_id][3] > 0)
            {

            }
            else{
                ////---Latest 3 loans added to add list
                $latest_loans_array[$customer_id][3] = $lid;
            }
        }
        else{
            $latest_loans_array[$customer_id][2] = $lid;
        }

    }
    else{
        $latest_loans_array[$customer_id][1] = $lid;
    }


}

/////------All Reviews last 30 days
$limit_reviews = table_to_array('o_customer_limits',"date(given_date) >= '$date_30' AND status=1","1000000","customer_uid");
$current_customer_limits = table_to_obj('o_customers',"uid in ($customer_list)","1000000","uid","loan_limit");
$loan_statuses = table_to_obj('o_loan_statuses',"uid > 0","1000000","uid","name");
//echo json_encode($latest_loans_array);
//-----Defaulters
$defaulters_list = table_to_array('o_loans',"customer_id in ($customer_list) AND status=7","1000000","customer_id");


//////----------We have all the data and we have not broken the system (-_-) lets give users some Limit increase

for($i=0; $i<=sizeof($customers_with_limit_array); ++$i){
    $customer_id = $customers_with_limit_array[$i];

    $latest_loans_1 = $latest_loans_array[$customer_id][1];     $l1_status = $loan_status_array[$latest_loans_1];
    $latest_loans_2 = $latest_loans_array[$customer_id][2];     $l2_status = $loan_status_array[$latest_loans_2];
    $latest_loans_3 = $latest_loans_array[$customer_id][3];     $l3_status = $loan_status_array[$latest_loans_3];

    $l1_due_date = $loan_due_date_array[$latest_loans_1];    $l1_last_pay_date = $loan_last_pay_date_array[$latest_loans_1];
    $l2_due_date = $loan_due_date_array[$latest_loans_2];    $l2_last_pay_date = $loan_last_pay_date_array[$latest_loans_2];
    $l3_due_date = $loan_due_date_array[$latest_loans_3];    $l3_last_pay_date = $loan_last_pay_date_array[$latest_loans_3];

    /////------Check if last loan is defaulted yet
    if($l1_status == 3 || $l1_status == 5) {
        ////-------Check if client has had a review this month
        if(in_array($customer_id, $limit_reviews))
        {
          ////  echo "Customer $customer_id was reviewed last month <br/>";
        }
        else{
            ///---------Check if client has defaulted last 3 loans
            $phone = $customer_phones_array[$customer_id];
            $current_limit = $current_customer_limits[$customer_id];
           //// echo "Customer $phone is ready [Loan $latest_loans_1: Status $l1_status], DD:($l1_due_date : $l1_last_pay_date)  [Loan $latest_loans_2: Status $l2_status]  DD:($l2_due_date : $l2_last_pay_date) , [Loan $latest_loans_3: Status $l3_status] DD:($l3_due_date : $l3_last_pay_date) <br/>";

            ////---Check if client is in defaulters list
            if(in_array($customer_id, $defaulters_list)){
                ///echo "Defaulter";
            }
            else {

                if($l1_status == 5 && $current_limit < 50000)
                {
                    if($current_limit >= 20000){
                       $factor = 1.1;
                    }
                    else{
                        $factor = 1.2;
                    }
                    $new_limit = $current_limit * $factor;
                    echo "$phone, $current_limit, $new_limit <br/>";
                  //  echo update_limit($customer_id, $new_limit, "Manual Review by Process");
                }
            }


            ///---------Never defaulted a Loan for more than 30 days

        }

    }
    else{
       //// echo "Latest status for Loan $latest_loans_1 is $l1_status  <br/>";
    }



  ////  echo "Latest loans for $customer_id : $latest_loans_1, $latest_loans_1, $latest_loans_1 <br/>";

}



