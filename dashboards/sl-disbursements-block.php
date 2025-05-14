<?php
$start_date = getFirstDayOfMonth($date);
$end_date = "$date";
/*
//$date = '2024-01-15';
$userd = session_details();
$loan_branches = branch_permissions($userd, 'o_loans');
$branches_list = branch_permissions($userd, 'o_branches');
$pay_branches = branch_permissions($userd, 'o_incoming_payments');
$customer_branches = $user_branches = branch_permissions($userd, 'o_customers');





$disb_today = 0;
$disb_yest = 0;
$total_loans_today = 0;
$new_borrowers_today = 0;
$new_borrowers_today_number = 0;
$repeat_borrowers_today = 0;
$repeat_borrowers_today_number = 0;
$pending_disb_today = 0;
$pending_disb_today_number = 0;

$all_loans_today_array = $all_loans_thisweek_array = $all_loans_thismonth_array =array();
$loans_per_customer_array = array();
///-----Fetch all loans for the set period
$loan_list_array = table_to_array('o_loans',"status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date' $loan_branches","100000000","uid","uid","asc");
$loan_list = implode(',', $loan_list_array);

$customers_list_array = table_to_array('o_loans',"uid in ($loan_list) $loan_branches","10000000","customer_id","uid","asc");
$customer_list = implode(',', $customers_list_array);
$cust =fetchtable('o_customers',"uid in ($customer_list) $customer_branches","uid","asc","1000000","uid, total_loans");
while($l = mysqli_fetch_array($cust)){
    $cid = $l['uid'];
    $loans_taken = $l['total_loans'];
    $loans_per_customer_array[$cid] = $loans_taken;

}


///-----------Loans during
$loans = fetchtable('o_loans',"uid in ($loan_list) $loan_branches","uid","asc","1000000000","uid, given_date, customer_id,loan_amount, status, disbursed, paid");
while($l = mysqli_fetch_array($loans)){
    $luid = $l['uid'];
    $given_date = $l['given_date'];
    $status  = $l['status'];
    $loan_amount = $l['loan_amount'];
    $disbursed = $l['disbursed'];
    $customer_id = $l['customer_id'];
    $paid = $l['paid'];

    ////--------Today
    if($disbursed == 1 && (datediff3($given_date, $date) == 0)){
        $disb_today+=$loan_amount;
        $total_loans_today+=1;
        array_push($all_loans_today_array, $loan_amount);
        $loans_taken = $loans_per_customer_array[$customer_id];
        if($loans_taken > 1){
            $repeat_borrowers_today+=$loan_amount;
            $repeat_borrowers_today_number+=1;
        }
        else{
            $new_borrowers_today+=$loan_amount;
            $new_borrowers_today_number+=1;
        }
    }
    elseif ($disbursed == 0 AND (datediff3($given_date, $date) == 0) && $status!=0){
        $pending_disb_today+=$loan_amount;
        $pending_disb_today_number+=1;
    }
    ////-------------------Comparison, yesterday
   if($disbursed == 1 && (datediff3($given_date, $date) == 1)){
       $disb_yest+=$loan_amount;
   }


   ////-----------------This week-------------------------------------------------------
    ///
    $this_week_start = getFirstDayOfWeek($date);
   $last_week = datesub($date, 0,0,7);
    if($disbursed == 1 && (isDateInRange($given_date, $this_week_start, $date))){
        $disb_thisweek+=$loan_amount;
        $total_loans_thisweek+=1;
        array_push($all_loans_thisweek_array, $loan_amount);
        $loans_taken = $loans_per_customer_array[$customer_id];
        if($loans_taken > 1){
            $repeat_borrowers_thisweek+=$loan_amount;
            $repeat_borrowers_thisweek_number+=1;
        }
        else{
            $new_borrowers_thisweek+=$loan_amount;
            $new_borrowers_thisweek_number+=1;
        }
    }
    elseif ($disbursed == 0 AND (datediff3($given_date, $date) == 0) && $status!=0){
        $pending_disb_thisweek+=$loan_amount;
        $pending_disb_thisweek_number+=1;
    }
    ////-------------------Comparison, last_week
    $last_week_start = getFirstDayOfWeek($last_week);
    //$last_week_end = getLastDayOfWeek($last_week);
    if($disbursed == 1 &&  (isDateInRange($given_date, $last_week_start, $last_week))){///Start of last week to a week ago
        $disb_lastweek+=$loan_amount;
    }
    ///
    ///----------------This month -------------------------------------------------------
    ///
    $this_month_start = getFirstDayOfMonth($date);
    $last_month = datesub($date, 0,1,0);
    if($disbursed == 1 && (isDateInRange($given_date, $this_month_start, $date))){
        $disb_thismonth+=$loan_amount;
        $total_loans_thismonth+=1;
        array_push($all_loans_thismonth_array, $loan_amount);
        $loans_taken = $loans_per_customer_array[$customer_id];
        if($loans_taken > 1){
            $repeat_borrowers_thismonth+=$loan_amount;
            $repeat_borrowers_thismonth_number+=1;
        }
        else{
            $new_borrowers_thismonth+=$loan_amount;
            $new_borrowers_thismonth_number+=1;
        }
    }
    elseif ($disbursed == 0 AND (datediff3($given_date, $date) == 0) && $status!=0){
        $pending_disb_thismonth+=$loan_amount;
        $pending_disb_thismonth_number+=1;
    }
    ////-------------------Comparison, last_month
    $last_month_start = getFirstDayOfMonth($last_month);
    //$last_week_end = getLastDayOfWeek($last_week);
    if($disbursed == 1 &&  (isDateInRange($given_date, $last_month_start, $last_month))){///Start of last week to a week ago
        $disb_lastmonth+=$loan_amount;
    }
    ///


}
/// -----End of fetch all loans for the given period

///-----Today
$min_borrowed_today = min($all_loans_today_array);
$max_borrowed_today = max($all_loans_today_array);
$av_borrowed_today = round(($disb_today/$total_loans_today), 2);



///----This week
//$disb_week = 0;
//$total_loans_week = 0;
//$min_borrowed_week = 0;
//$max_borrowed_week = 0;
//$av_borrowed_week = 0;
//$new_borrowers_week = 0;
//$new_borrowers_week_number = 0;
//$repeat_borrowers_week = 0;
//$repeat_borrowers_week_number = 0;
//$pending_disb_week = 0;
//$pending_disb_week_number = 0;
$min_borrowed_thisweek = min($all_loans_thisweek_array);
$max_borrowed_thisweek = max($all_loans_thisweek_array);
$av_borrowed_thisweek = round(($disb_thisweek/$total_loans_thisweek), 2);

$weeks_disb_analysis = "<span class='text-green'><i class=\"fa fa-thumbs-up\"></i> Everything looking good</span>";

////----This month
//$disb_month = 0;
//$total_loans_month = 0;
//$min_borrowed_month = 0;
//$max_borrowed_month = 0;
//$av_borrowed_month = 0;
//$new_borrowers_month = 0;
//$new_borrowers_month_number = 0;
//$repeat_borrowers_month = 0;
//$repeat_borrowers_month_number = 0;
//$pending_disb_month = 0;
//$pending_disb_month_number = 0;
$min_borrowed_thismonth = min($all_loans_thismonth_array);
$max_borrowed_thismonth = max($all_loans_thismonth_array);
$av_borrowed_thismonth = round(($disb_thismonth/$total_loans_thismonth), 2);
*/
?>
<br />
<div class="box-separator">
    <i class="fa fa-upload"></i> DISBURSEMENTS

</div>

<div class="row">
    <div class="col-sm-12">
        <div class="row">

            <div class="col-sm-12">
                <div class="box box-primary  box-solid">
                    <div class="box-header box-title bg-blue font-bold">




                        <div class="pull-left"> &nbsp;&nbsp; &nbsp;&nbsp;
                            <?php
                            if ($has_regions == "TRUE") { ?>

                                <button onclick="input_add('#dobj', 'REGION'); nd_loan_list();"
                                    class="btn btn-default font-bold bg-blue-gradient">Per Region</button>

                            <?php } ?>

                            <button onclick="input_add('#dobj', 'BRANCH'); nd_loan_list();"
                                class="btn btn-default font-bold bg-blue-gradient">Per Branch</button>
                            <button onclick="input_add('#dobj', 'PRODUCT'); nd_loan_list();"
                                class="btn btn-default font-bold bg-blue-gradient">Per Product</button>
                            <button onclick="input_add('#dobj', 'AGENT'); nd_loan_list();"
                                class="btn btn-default font-bold bg-blue-gradient">Per Agent</button>
                            <button onclick="input_add('#dobj', 'GROUP'); nd_loan_list();"
                                class="btn btn-default font-bold bg-blue-gradient">Per Group</button>
                            <button onclick="input_add('#dobj', 'MONTHLY'); nd_loan_list();"
                                class="btn btn-default font-bold bg-blue-gradient">Per Month</button>
                            <button onclick="input_add('#dobj', 'WEEKLY'); nd_loan_list();"
                                class="btn btn-default font-bold bg-blue-gradient">Per Week</button>
                            <button onclick="input_add('#dobj', 'DAILY'); nd_loan_list();"
                                class="btn btn-default font-bold bg-blue-gradient">Per Day</button>
                            <input type="hidden" id="dobj" value="DAILY">

                        </div>
                        <div class="pull-right">
                            <input type="date" value="<?php echo $start_date; ?>" id="start_date_loans"
                                class="btn btn-default font-bold">
                            <input type="date" value="<?php echo $end_date; ?>" id="end_date_loans"
                                class="btn btn-default font-bold">
                            <button onclick="nd_loan_list();"
                                class="btn btn-success bg-black-gradient font-bold">GO</button>
                        </div>


                    </div>
                    <!-- /.box-header -->
                    <div class="box-body scroll-hor" style="background: white;" id="nd_dashboard_loans">
                        <a onclick="nd_loan_list();" href="javascript:void(0)" class="font-bold text-blue hotbutton"><i
                                class="fa fa-refresh"></i> Click to View</a>


                    </div>
                    <!-- /.box-body -->
                </div>
            </div>




        </div>


    </div>



</div>