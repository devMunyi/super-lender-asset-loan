<?php

$all_loans_array = array();
$all_customers_array = array();
$loan_customer_array = array();
$customer_loan_number_array = array();
$loans_per_week_array = array();
$borrowers_by_gender_array = array();
$borrowers_by_application_mode = array();
$loans_per_day_array = array();
$loans_per_month_array = array();
$loans_per_hour_array = array();
$borrowers_by_amount_array = array();
$borrowers_by_product_array = array();
$borrowers_by_product_array[0] = "Unspecified";
$loans_per_customer_array = array();
$loan_per_defalter_array = array();

$products = table_to_obj('o_loan_products',"uid > 0","20","uid","name");

$all_loans = fetchtable('o_loans',"disbursed=1 AND status=7 AND given_date >= '2022-01-01'","uid","desc","1000000","uid, given_date, final_due_date, customer_id, loan_amount, total_repayable_amount, product_id, total_repaid, loan_balance, status, application_mode, added_date");
while ($r = mysqli_fetch_array($all_loans)){
    $lid = $r['uid'];
    $given_date = $r['given_date'];
                     $weekofmonth = weekOfMonth($given_date);
                     $dayofweek = date('w', strtotime($given_date));



    $final_due_date = $r['final_due_date'];

                     $my = explode('-', $final_due_date);
                     $month_of_year = $my[0].'-'.$my[1];

    $customer_id = $r['customer_id'];
    $loan_amount = $r['loan_amount'];
    $loan_product = $r['product_id'];
    $total_repayable = $r['total_repayable_amount'];
    $total_repaid = $r['total_repaid'];
    $loan_balance = $r['loan_balance'];
    $application_mode = $r['application_mode'];
    $added_date = $r['added_date'];
                   $hour = hour_from_date($added_date);
                  $borrowers_by_application_mode = obj_add($borrowers_by_application_mode, $application_mode, 1);
    $status = $r['status'];

    $loans_per_week_array = obj_add($loans_per_week_array, $weekofmonth, 1);

    $customer_loan_number_array = obj_add($customer_loan_number_array, $customer_id, 1);

    $loans_per_day_array = obj_add($loans_per_day_array, $dayofweek, 1);

    $loans_per_month_array = obj_add($loans_per_month_array, $month_of_year, 1);


    $loans_per_hour_array = obj_add($loans_per_hour_array, $hour, 1);

    $borrowers_by_amount_array = obj_add($borrowers_by_amount_array, rounduptoany($loan_amount, 1000), 1);

    $borrowers_by_product_array = obj_add($borrowers_by_product_array, $loan_product, 1);


    $loan_customer_array[$lid] = $customer_id;


    array_push($all_loans_array, $lid);
    array_push($all_customers_array, $customer_id);
}


///////---
$loan_dist_array = array();
foreach($customer_loan_number_array as $customer => $borrowers) {
    $loan_dist_array = obj_add($loan_dist_array, $borrowers , 1);
}

$customer_str = implode(",", $all_customers_array);
$customer_ages_array = array();
$customer_limits_array = array();
$borrowers_by_age_array = array();
$borrowers_by_loan_limit_array = array();




$customer_d = fetchtable('o_customers',"uid in ($customer_str) AND dob != '1970-01-01'","uid","asc","10000000","uid, dob,primary_product, loan_limit, status, gender");
while($c = mysqli_fetch_array($customer_d)){
    $cid = $c['uid'];
    $dob = $c['dob'];       $age = false_zero(round((datediff3($dob, $date))/365, 0));
                            if($age > 100){
                                $age = 0;
                            }
    $loan_limit = $c['loan_limit'];

                             // $loan_limit =  round ($loan_limit, -3);

     $gender = $c['gender'];

    $status = $c['status'];

   // echo $age.',';
    $borrowers_by_gender_array = obj_add($borrowers_by_gender_array, $gender, 1);
    $customer_ages_array[$cid] = $age;
    $customer_limits_array[$cid] = rounduptoany($loan_limit, 1000);
    $borrowers_by_age_array = obj_add($borrowers_by_age_array, "$age", 1);
    $borrowers_by_loan_limit_array = obj_add($borrowers_by_loan_limit_array, rounduptoany($loan_limit, 1000), 1);

}

$loans_per_cust = fetchtable('o_loans',"disbursed=1 AND status!=0 AND customer_id in($customer_str)","uid","asc","100000000","uid,customer_id");
while($ll = mysqli_fetch_array($loans_per_cust)){
    $llid = $ll['uid'];
    $cust_id = $ll['customer_id'];
    $loans_per_customer_array = obj_add($loans_per_customer_array, $cust_id, 1);
}
foreach($loans_per_customer_array as $cust => $loans) {

    $loan_per_defalter_array = obj_add($loan_per_defalter_array, $loans, 1);
}



ksort($borrowers_by_age_array);
ksort($borrowers_by_loan_limit_array);
ksort($borrowers_by_gender_array);
ksort($loan_dist_array);
ksort($loan_dist_array);
ksort($loans_per_week_array);
ksort($customer_loan_number_array);
ksort($loans_per_day_array);
ksort($loans_per_hour_array);
ksort($borrowers_by_amount_array);
ksort($borrowers_by_product_array);
ksort($loans_per_month_array);
ksort($loan_per_defalter_array);
?>





<div class="row">

    <div class="col-lg-12 col-xs-12">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3>Defaulters Analysis</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">

                <div class="row">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title font-bold">Defaulters By Age</h5>
                                <div class="card-text">
                                    <table class="table table-bordered">
                                        <thead class="bg-black-gradient">
                                        <tr><th>Age</th><th><18</th><th>18-23</th><th>24-28</th><th>29-32</th><th>33-35</th><th>36-40</th><th>41-45 </th><th>46-50</th><th>>50</th><th>Undefined</th></tr>
                                        <?php
                                        $total_borr = $age18 = $age18_23 = $age24_28 = $age29_32 = $age33_35 = $age36_40 = $age41_45 = $age46_50 = $age_50plus = $undefined = 0;
                                        foreach($borrowers_by_age_array as $age => $borrowers) {
                                            $total_borr = $total_borr + $borrowers;
                                            if($age == 0){
                                                $undefined = $undefined + $borrowers;
                                            }
                                            elseif($age < 18 && $age > 0){
                                                $age18 = $age18 + $borrowers;
                                            }
                                            else if($age >= 18 && $age <= 23){
                                                $age18_23 = $age18_23 + $borrowers;
                                            }
                                            else if($age >= 24 && $age <= 28){
                                                $age24_28 = $age24_28 + $borrowers;
                                            }
                                            else if($age >= 29 && $age <= 32){
                                                $age29_32 = $age29_32 + $borrowers;
                                            }
                                            else if($age >= 33 && $age <= 35){
                                                $age33_35 = $age33_35 + $borrowers;
                                            }
                                            else if($age >= 36 && $age <= 40){
                                                $age36_40 = $age36_40 + $borrowers;
                                            }
                                            else if($age >= 41 && $age <= 45){
                                                $age41_45 = $age41_45 + $borrowers;
                                            }
                                            else if($age >= 46 && $age <= 50){
                                                $age46_50 = $age46_50 + $borrowers;
                                            }
                                            else if($age > 50){
                                                $age_50plus = $age_50plus + $borrowers;
                                            }
                                            else{
                                                $undefined = $undefined + $borrowers;
                                            }


                                        }

                                        $undefinedh = $undefined*1.3 + 50;
                                        $age18h = $age18*2 + 30;
                                        $age18_23h = $age18_23*2 + 50;
                                        $age24_28h = $age24_28*2 + 50;
                                        $age29_32h = $age29_32*2 + 50;
                                        $age33_35h = $age33_35*2 + 50;
                                        $age36_40h = $age36_40*2 + 50;
                                        $age41_45h = $age41_45*2 + 50;
                                        $age46_50h = $age46_50*2 + 50;
                                        $age_50plush = $age_50plus*2 + 50;
                                        $px = 'px';
                                        ?>
                                        </thead>
                                        <tbody>
                                        <tr><td>Share</td>
                                            <?php
                                            echo "<td><div class=\"circle_\" style=\"height: $undefinedh$px; Width: $undefinedh$px;\">$undefined%</div> </td>";
                                            echo "<td><div class=\"circle_\" style=\"height: $age18h$px; width: $age18h$px;\">$age18%</div> </td>";
                                            echo "<td><div class=\"circle_\"  style=\"height: $age18_23h$px; width: $age18_23h$px;\">$age18_23%</div> </td>";
                                            echo "<td><div class=\"circle_\" style=\"height: $age24_28h$px; width: $age24_28h$px;\">$age24_28%</div> </td>";
                                            echo "<td><div class=\"circle_\" style=\"height: $age29_32h$px; width: $age29_32h$px;\">$age29_32%</div> </td>";
                                            echo "<td><div class=\"circle_\" style=\"height: $age33_35h$px; width: $age33_35h$px;\">$age33_35%</div> </td>";
                                            echo "<td><div class=\"circle_\" style=\"height: $age36_40h$px; width: $age36_40h$px;\">$age36_40%</div> </td>";
                                            echo "<td><div class=\"circle_\" style=\"height: $age41_45h$px; width: $age41_45h$px;\">$age41_45%</div> </td>";
                                            echo "<td><div class=\"circle_\" style=\"height: $age46_50h$px; width: $age46_50h$px;\">$age46_50%</div> </td>";
                                            echo "<td><div class=\"circle_\" style=\"height: $age_50plush$px; width: $age_50plush$px;\">$age_50plus%</div> </td>";
                                            ?>

                                        </tr>
                                        </tbody>

                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title font-bold">Borrowers By Age (Ungrouped)</h5>
                                <div class="card-text">
                                    <table class="table table-bordered">
                                        <thead class="bg-black-gradient">
                                        <tr><th>Age</th>
                                            <?php
                                            $total_borrowers = 0;
                                            foreach($borrowers_by_age_array as $age => $borrowers) {
                                                $total_borrowers = $total_borrowers + $borrowers;
                                                echo "<th>$age</th>";
                                            }
                                            ?>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr><td>Borrowers</td>
                                            <?php
                                            foreach($borrowers_by_age_array as $age => $borrowers) {
                                                //  echo $borrowers.',';
                                                $perc = round(($borrowers / $total_borrowers) * 100, 1);
                                                echo "<td><div class=\"circle_\" title=\"$borrowers\" style=\"height: ".height($perc)."px; width: ".height($perc)."px;\">$perc%</div></td>";
                                            }
                                            ?>
                                        </tr>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title font-bold">Defaulters By Gender</h5>
                                <div class="card-text">

                                    <table class="table table-bordered">
                                        <thead class="bg-black-gradient">
                                        <tr><th>Gender</th><th>Male</th><th>Female</th><th>Undefined</th></tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $male = $female = $other = $total_g = $borrowers = 0;
                                        foreach($borrowers_by_gender_array as $gender => $borrowers) {
                                            if($gender == 'M'){
                                              $male = $male + $borrowers;
                                            }
                                            elseif ($gender == 'F'){
                                                $female = $female + $borrowers;
                                            }
                                            else{
                                                $other = $other + $borrowers;
                                            }
                                            $total_g = $total_g + $borrowers;
                                        }
                                        $male_p = round(($male / $total_g)*100, 2);
                                        $female_p = round(($female / $total_g)*100, 2);
                                        $other_p = round(($other / $total_g)*100, 2);

                                        ?>

                                        <tr><td>%</td><td>
                                                <?php
                                                echo "<div class=\"circle_\" style=\"height: ".height($male_p)."px; width: ".height($male_p)."px;\">$male_p%</div>";
                                                ?>
                                            </td><td>
                                                <?php
                                                echo "<div class=\"circle_\" style=\"height: ".height($female_p)."px; width: ".height($female_p)."px;\">$female_p%</div>";
                                                ?>
                                            </td><td>
                                                <?php
                                                echo "<div class=\"circle_\" style=\"height: ".height($other_p)."px; width: ".height($other_p)."px;\">$other_p%</div>";
                                                ?>
                                            </td></tr>

                                        </tbody>
                                    </table>




                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title font-bold">Defaulters By Number of Loans</h5>
                                <div class="card-text">
                                    <table class="table table-bordered">
                                        <thead class="bg-black-gradient">
                                        <tr><th>Loans</th>
                                            <?php
                                            $total_borrowers = 0;
                                            foreach($loan_per_defalter_array as $total_loans => $borrowers) {
                                                $total_borrowers = $total_borrowers + $borrowers;
                                              echo "<th>$total_loans</th>";
                                            }
                                            ?>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr><td>Defaulters</td>
                                            <?php
                                            foreach($loan_per_defalter_array as $total_loans => $borrowers) {
                                                $perc = round(($borrowers / $total_borrowers) * 100, 0);
                                                echo "<td><div class=\"circle_\" title=\"$borrowers\" style=\"height: ".height($perc)."px; width: ".height($perc)."px;\">$perc%</div></td>";
                                            }
                                            ?>
                                        </tr>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title font-bold">Defaulters By Month</h5>
                                <table class="table table-bordered">
                                    <thead class="bg-black-gradient">
                                    <tr><th>Month of Year</th>
                                        <?php
                                        $total_borrowers = 0;
                                        foreach($loans_per_month_array as $month => $borrowers) {
                                            $total_borrowers = $total_borrowers + $borrowers;
                                            echo "<th>".$month."</th>";
                                        }
                                        ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr><td>Defaulters</td>
                                        <?php
                                        foreach($loans_per_month_array as $month => $borrowers) {
                                            $perc = round(($borrowers / $total_borrowers) * 100, 0);
                                            echo "<td><div title=\"$borrowers defaulters\" class=\"circle_\" style=\"height: ".height($perc)."px; width: ".height($perc)."px;\">$perc%</div></td>";
                                        }
                                        ?>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>


                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title font-bold">Defaulters By Limit</h5>
                                <table class="table table-bordered">
                                    <thead class="bg-black-gradient">
                                    <tr><th>Loan-Limit</th>
                                        <?php
                                        $total_borrowers = 0;
                                        foreach($borrowers_by_loan_limit_array as $limit => $borrowers) {
                                            $total_borrowers = $total_borrowers + $borrowers;
                                            echo "<th>".number_format($limit)."</th>";
                                        }
                                        ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr><td>Defaulters</td>
                                        <?php
                                        foreach($borrowers_by_loan_limit_array as $limit => $borrowers) {
                                            $perc = round(($borrowers / $total_borrowers) * 100, 0);
                                            echo "<td><div title=\"$borrowers\" class=\"circle_\" style=\"height: ".height($perc)."px; width: ".height($perc)."px;\">$perc%</div></td>";
                                        }
                                        ?>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title font-bold">Defaulters By Amount Borrowed</h5>

                                <table class="table table-bordered">
                                    <thead class="bg-black-gradient">
                                    <tr><th>Loan-Amount</th>
                                        <?php
                                        $total_borrowers = 0;
                                        foreach($borrowers_by_amount_array as $amount => $borrowers) {
                                            $total_borrowers = $total_borrowers + $borrowers;
                                            echo "<th>".number_format($amount)."</th>";
                                        }
                                        ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr><td>Defaulters</td>
                                        <?php
                                        foreach($borrowers_by_amount_array as $amount => $borrowers) {
                                            $perc = round(($borrowers / $total_borrowers) * 100, 0);
                                            echo "<td><div title=\"$borrowers\" class=\"circle_\" style=\"height: ".height($perc)."px; width: ".height($perc)."px;\">$perc%</div></td>";
                                        }
                                        ?>
                                    </tr>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title font-bold">Defaulters By Product</h5>
                                <table class="table table-bordered">
                                    <thead class="bg-black-gradient">
                                    <tr><th>Loan Amount</th>
                                        <?php
                                        $total_borrowers = 0;
                                        foreach($borrowers_by_product_array as $product => $borrowers) {
                                            $total_borrowers = $total_borrowers + $borrowers;
                                            echo "<th>".$products[$product]."</th>";
                                        }
                                        ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr><td>Defaulters</td>
                                        <?php
                                        foreach($borrowers_by_product_array as $product => $borrowers) {
                                            $perc = round(($borrowers / $total_borrowers) * 100, 0);
                                            echo "<td><div class=\"circle_\" title=\"$borrowers\" style=\"height: ".height($perc)."px; width: ".height($perc)."px;\">$perc%</div></td>";
                                        }
                                        ?>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title font-bold">Defaulters By Mode</h5>
                                <table class="table table-bordered">
                                    <thead class="bg-black-gradient">
                                    <tr><th>Mode</th>
                                        <?php
                                        $total_borrowers = 0;
                                        foreach($borrowers_by_application_mode as $mode => $borrowers) {
                                            $total_borrowers = $total_borrowers + $borrowers;
                                            echo "<th>".$mode."</th>";
                                        }
                                        ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr><td>Defaulters</td>
                                        <?php
                                        foreach($borrowers_by_application_mode as $mode => $borrowers) {
                                            $perc = round(($borrowers / $total_borrowers) * 100, 0);
                                            echo "<td><div title=\"$borrowers\" class=\"circle_\" style=\"height: ".height($perc)."px; width: ".height($perc)."px;\">$perc%</div></td>";
                                        }
                                        ?>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.box-body -->
        </div>
    </div>




</div>



    <!-- ./col -->

