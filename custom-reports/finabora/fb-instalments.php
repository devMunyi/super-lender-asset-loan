<?php

$start_days = datediff3($start_date, $date);
$end_days = datediff3($end_date, $date);

$oldest = datesub($date, 0, 0, 35);

//if(($start_days - $end_days) > 7){
//    echo errormes("Please select 7 days time lapse");
//}



    ?>
    <div class="col-sm-12">
        <table id="example2" class="table table-condensed table-striped table-bordered">
            <thead>
            <tr><th>ID</th><th>Name</th><th>Address</th><th>Phone</th><th>Loan Amount</th> <th>Given Date</th><th>Instalment Date</th> <th>Days Lapsed</th><th>Instalment</th><th>Paid</th><th>Inst. Balance</th></tr>
            </thead>
            <tbody>
            <?php

            $skip = 0;
            $loans_list = implode(',', table_to_array('o_loans',"disbursed=1 AND paid=0 AND product_id=14 AND given_date >= '$oldest'","1000000","uid"));
            $customers_list = implode(',', table_to_array('o_loans',"disbursed=1 AND paid=0 AND product_id=14 AND given_date >= '$oldest'","1000000","customer_id"));

            $customer_names = array();
            $customer_addresses = array();
            $customer_details = fetchtable('o_customers',"uid > 0", 'uid','asc',"100000","uid,full_name,physical_address");
            while($c = mysqli_fetch_array($customer_details)){
                $cuid = $c['uid'];
                $full_name = $c['full_name'];
                $physical_address = $c['physical_address'];
                $customer_names[$cuid] = $full_name;
                $customer_addresses[$cuid] = $physical_address;
            }

            $reg_fees = table_to_obj('o_loan_addons',"addon_id=2 AND status=1 AND loan_id in ($loans_list)","1000000","loan_id","addon_amount");

            $loans = fetchtable('o_loans'," (DATE_ADD(given_date, INTERVAL 7 DAY) = '$start_date'
   OR DATE_ADD(given_date, INTERVAL 14 DAY) = '$start_date'
   OR DATE_ADD(given_date, INTERVAL 21 DAY) = '$start_date'
   OR DATE_ADD(given_date, INTERVAL 28 DAY) = '$start_date')
    AND (disbursed = 1 AND paid = 0 AND product_id=14)","uid","asc","100000","uid, given_date, final_due_date, loan_amount, next_due_date, total_addons, account_number, total_repaid, customer_id");
            while($l = mysqli_fetch_array($loans)) {

                $lid = $l['uid'];
                $given_date = $l['given_date'];
                $final_due_date = $l['final_due_date'];
                $next_due_date = $l['next_due_date'];
                $loan_amount = $l['loan_amount'];
                $total_addons = $l['total_addons'];
                $account_number = $l['account_number'];
                $total_repaid = $l['total_repaid'];
                $customer_id = $l['customer_id'];
                $instalment_amt = $instalment_bal = $instalment_paid = 0;

                $days_passed = (int)datediff3($given_date, $start_date);


                $loan_repayment = $total_repaid - $reg_fees[$lid];

                $total_amount = $loan_amount * 1.3;
                $weekly = $total_amount / 4;

                $given_date_7 = dateadd($given_date, 0, 0, 7);
                $given_date_7_amount = $weekly;

                $given_date_14 = dateadd($given_date, 0, 0, 14);
                $given_date_14_amount = $weekly * 2;

                $given_date_21 = dateadd($given_date, 0, 0, 21);
                $given_date_21_amount = $weekly * 3;

                $given_date_28 = dateadd($given_date, 0, 0, 28);
                $given_date_28_amount = $total_amount;

                if($days_passed >= 7 && $days_passed <= 13){
                    ///---It falls in the date range
                    if($given_date_7_amount > $loan_repayment){
                        ///----Instalment has not been repaid
                        $instalment_amt = $weekly * 1;
                        $instalment_paid = $loan_repayment;
                        $instalment_bal = $instalment_amt - $instalment_paid;
                        $inst_date = dateadd($given_date, 0, 0, 7);
                        $skip = 0;
                    }
                    else{
                        //----Instalment repaid,skip
                       // $skip = 1;
                    }
                }


                if($days_passed >= 14 && $days_passed <= 20){
                    ///---It falls in the date range
                    if($given_date_14_amount > $loan_repayment){
                        ///----Instalment has not been repaid
                        $instalment_amt = $weekly * 2;
                        $instalment_paid = $loan_repayment;
                        $instalment_bal = $instalment_amt - $instalment_paid;
                        $inst_date = dateadd($given_date, 0, 0, 14);
                        $skip = 0;
                    }
                    else{
                        //----Instalment repaid,skip
                       // $skip = 1;
                    }
                }






                if($days_passed >= 21 && $days_passed <= 27){
                    ///---It falls in the date range
                    if($given_date_21_amount > $loan_repayment){
                        ///----Instalment has not been repaid
                        $instalment_amt = $weekly * 3;
                        $instalment_paid = $loan_repayment;
                        $instalment_bal = $instalment_amt - $instalment_paid;
                        $inst_date = dateadd($given_date, 0, 0, 21);
                        $skip = 0;
                    }
                    else{
                        //----Instalment repaid,skip
                      //  $skip = 1;
                    }
                }
                if($days_passed >= 28){
                    ///---It falls in the date range
                    if($given_date_28_amount > $loan_repayment){
                        ///----Instalment has not been repaid
                        $instalment_amt = $weekly * 4;
                        $instalment_paid = $loan_repayment;
                        $instalment_bal = $instalment_amt - $instalment_paid;
                        $inst_date = dateadd($given_date, 0, 0, 28);
                        $skip = 0;
                    }
                    else{
                        //----Instalment repaid,skip
                       // $skip = 1;
                    }
                }
                else{
                   // $skip = 1;
                }

                if($instalment_bal > 0){

                }
                else{
                    $skip = 1;
                }

                if($skip == 1){
                    continue;
                }

                $skip = 0;

                $given_days = datediff3($given_date, $date);
                $name = $customer_names[$customer_id];
                $address = $customer_addresses[$customer_id];

                $link_cust = "<a title='Go to customer' href=\"customers?customer=".encurl($customer_id)."\" target='_blank'><i class='fa fa-external-link-square'></i></a>";
                $link_loan = "<a title='Go to loan' href=\"loans?loan=".encurl($lid)."\" target='_blank'><i class='fa fa-external-link-square'></i></a>";

                echo "<tr><td>$lid $link_loan </td><td>$name $link_cust</td><td>$address</td><td>$account_number</td><td>$loan_amount</td> <td>$given_date</td><td>$inst_date</td> <td>$given_days</td><td>$instalment_amt</td><td>$instalment_paid</td><td>$instalment_bal</td></tr>";

            }

            ?>


            </tbody>
        </table>
    </div>



