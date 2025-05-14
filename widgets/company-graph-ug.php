<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
////----------Fetch all Loans
$branches_array = array();
$staff_array = array();
$products_array = array();
$branch_disb_targets = array();
$this_year = date('Y');
$this_month = date('m');
$this_day = date('d');
$currencyUsed = currencyUsed();
$currencyUsed = "<small class='font-14'>$currencyUsed</small>";

$userbranch = $userd['branch'];

$view_summary = permission($userd['uid'], 'o_summaries', "0", "read_");
if ($view_summary == 1) {
    $andbranch_payments = "";
    $andbranch_customers = "";
    $andbranch_loans = "";
    $andbranch = "";
} else {
    $andbranch_payments = "AND branch_id='$userbranch'";
    $andbranch_customers = "AND branch='$userbranch'";
    $andbranch_loans = "AND current_branch='$userbranch'";
    $andbranch = "AND uid='$userbranch'";
}

$start_date = "$this_year-$this_month-01";


$three_months_back = datesub($date, 0, 3, 0);
$expd = explode('-', $three_months_back);
$three_1 = $expd[0] . '-' . $expd[1] . '-01';

$collections = fetchtable2('o_incoming_payments', "status=1 $andbranch_payments AND payment_date > '$three_1'", "uid", "asc", "uid, customer_id, branch_id, amount, payment_date");



$new_clients = fetchtable2('o_customers', "status=1 $andbranch_customers", "uid", "asc", "uid, branch, primary_product, DATE(added_date) as added_date"); /////


$branch_disb_targets = table_to_obj('o_targets', "target_type='DISBURSEMENTS' AND starting_date <= '$date' AND ending_date >= '$date' AND target_group ='BRANCH' AND status=1", "1000", "group_id", "amount"); /////


$branches_list = fetchtable2('o_branches', "status=1 $andbranch", "name", "asc", "uid, name");
while ($br = mysqli_fetch_array($branches_list)) {
    $bid = $br['uid'];
    $name = $br['name'];
    $branches_array[$bid] = $name;
}
////------------Summaries




/////------------Targets
$branch_disb_targets[0] = 0;
$view_summary = permission($userd['uid'], 'o_summaries', "0", "read_"); {
?>


    <div class="row">
        <div class="col-lg-12 col-xs-12">
            <div class="row">
                <?php
                $loans_today = totaltable('o_loans', "given_date='$date' AND status !=0 AND disbursed=1 $andbranch_loans", "loan_amount");
                $payments_today = totaltable('o_incoming_payments', "payment_date='$date' AND status=1 $andbranch_payments", "amount");
                $due_today = totaltable('o_loans', "final_due_date='$date' AND disbursed=1 AND paid=0 $andbranch_loans", "loan_balance");
                if ($cc == 256) {
                    $utility_balance = fetchrow('o_summaries', "name='MTN_UTILITY_BALANCE' $andbranch_loans", "value_");
                    $inline_text = 'MTN B2C Balance:';

                    $airtel_ug_utility_balance = fetchrow('o_summaries', "name='AIRTEL_UG_UTILITY_BALANCE' $andbranch_loans", "value_");
                    $ug_airtel_utility_inline_text = "Airtel B2C Balance:";

                    $airtel_ug_paybill_balance = fetchrow('o_summaries', "name='AIRTEL_UG_PAYBILL_BALANCE' $andbranch_loans", "value_");
                    $airtel_ug_paybill_inline_text = "Airtel C2B Balance:";
                } else {
                    $utility_balance = fetchrow('o_summaries', "name='UTILITY_BALANCE' $andbranch_loans", "value_");
                    $inline_text = 'B2C Balance:';
                }

                $paybill_balance = fetchrow('o_summaries', "name='PAYBILL_BALANCE' $andbranch_loans", "value_");
                $sms_balance = fetchrow('o_summaries', "name='SMS_BALANCE'", "value_");
                ///----Fetch SMS balance from API
                $sms_bal = bulk_sms_balance();
                $sms_balance = $sms_bal;

                // Use a regular expression to extract all digits
                if (preg_match('/(\d+(\.\d+)?)/', strval($sms_balance), $matches)) {
                    $sms_balance = $matches[0]; // Extract all digits, including decimal part
                    $remains_ = trim(substr($sms_balance, strlen($balance)));
                }

                ?>
                <div class="col-sm-2">
                    <a href="loans?start_date=<?php echo $date; ?>&end_date=<?php echo $date; ?>" class="small-box box box-solid bg-blue-gradient">
                        <div class="inner">
                            <span class='font-14'>Loans Today <i class="fa fa-external-link"></i></span>
                            <p class="text-bold"> <?php echo $currencyUsed . " <span class='font-16'>" . money($loans_today); ?></p>
                        </div>

                    </a>
                </div>
                <div class="col-sm-2">
                    <a href="incoming-payments?start_date=<?php echo $date; ?>&end_date=<?php echo $date; ?>" class="small-box box box-solid bg-green-gradient">
                        <div class="inner">
                            <span class='font-14'>Payments Today <i class="fa fa-external-link"></i></span>
                            <p class="text-bold font-16"> <?php echo $currencyUsed . " <span class='font-16'>" . money($payments_today); ?></span></p>
                        </div>

                    </a>
                </div>
                <div class="col-sm-2">
                    <a href="falling-due?start_date=<?php echo $date; ?>&end_date=<?php echo $date; ?>" class="small-box box box-solid bg-black">
                        <div class="inner">
                            <span class='font-14'>Due Today <i class="fa fa-external-link"></i> </span>
                            <p class="text-bold"> <?php echo $currencyUsed . " <span class='font-16'>" . money($due_today); ?></span></p>
                        </div>

                    </a>
                </div>
                <div class="col-sm-2">
                    <div class="small-box box box-solid bg-red">
                        <div class="inner">
                            <span class="font-14"><?php echo $inline_text; ?></span>
                            <p class="text-bold"> <?php echo $currencyUsed . " <span class='font-16'>" . money($utility_balance); ?></span></p>

                            <?php
                            if ($cc == 256) { ?>
                                <span class="font-14"><?php echo $ug_airtel_utility_inline_text; ?></span>
                                <p class="text-bold"> <?php echo $currencyUsed ?> <span class="font-16"><?php echo money($airtel_ug_utility_balance); ?></span></p>

                            <?php } ?>
                        </div>

                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="small-box box box-solid bg-orange-active">
                        <div class="inner">
                            <?php
                            if ($cc == 256) {
                                echo "<span class='font-14'>" . $airtel_ug_paybill_inline_text . "</span>";
                                echo "<p id='airtel_ug_paybill_balance' class='text-bold font-14'>$currencyUsed <span class='font-16'>" . money($airtel_ug_paybill_balance) . "</span></p>";
                            } else {
                                echo "<span class='font-14'> C2B Balance:</span>";
                                echo "<p class='text-bold'>" . $currencyUsed . " <span class='font-16'>" . money($paybill_balance) . "</span></p>";
                            }
                            ?>
                        </div>

                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="small-box box box-solid bg-aqua-gradient">
                        <div class="inner">
                            <span class='font-14'>SMS Balance:</span>
                            <p class="text-bold">
                                <?php echo doubleval($sms_balance) != 0 ? $currencyUsed . " <span class='font-16'>" . money($sms_balance) . "</span>" : 'N/A'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php
}
    ?>
    <div class="col-lg-12 col-xs-12">


        <!-- small box -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Time Performance <i class="fa fa-line-chart"></i><span class="text-green"> Default shows MTD</span></h3>
            </div>
            <table class="table table-striped">
                <tr>
                    <td> <input type="date" value="<?php echo $start_date; ?>" class="form-control" id="date_start"> </td>
                    <td> <input type="date" value="<?php echo $date; ?>" class="form-control" id="date_end"> </td>
                    <td> <select id="select_type" class="form-control">
                            <option value="DAILY">DAILY</option>
                            <option value="MONTHLY">MONTHLY</option>
                        </select>
                    </td>
                    <td> <button class="btn btn-primary" onclick="graph_load();">GENERATE</button> </td>
                </tr>
            </table>
            <div id="graph1">
                Loading...
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-xs-4" style="display: none;">
        <div class="box box-primary">
            <div class="box-header ui-sortable-handle" style="cursor: move;">
                <i class="ion ion-clipboard"></i>

                <h3 class="box-title">To Do List</h3>


            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <!-- See dist/js/pages/dashboard.js to activate the todoList plugin -->
                <ul class="todo-list ui-sortable">
                    <li class="">
                    <li>No Pending Items</li>
                    </li>


                </ul>
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix no-border">
                <button type="button" class="btn btn-default pull-right"><i class="fa fa-plus"></i> Add item</button>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xs-6">
        <!-- small box -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Disburse Progress - MTD</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <?php
                $branch_disb_targets = table_to_obj('o_targets', "target_type='DISBURSEMENTS' AND target_group='BRANCH' AND status=1", "1000", "group_id", "amount");
                //  $branch_disb_days = table_to_obj('o_targets',"target_type='DISBURSEMENTS' AND target_group='BRANCH' AND status=1","1000","group_id","working_days");


                $loans = fetchtable2('o_loans', "disbursed=1 AND given_date >= '$this_year-$this_month-01' $andbranch_loans AND status!=0", "uid", "asc", "uid, customer_id, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, given_date, final_due_date, current_agent, current_branch, status");
                $branch_totals_array = array();
                while ($dp = mysqli_fetch_array($loans)) {
                    $current_branch = $dp['current_branch'];
                    $given_date = $dp['given_date'];
                    $given_date_array = explode('-', $given_date);

                    if ($this_year == $given_date_array[0] && $this_month == $given_date_array[1]) {
                        $loan_amount = $dp['loan_amount'];
                        if ($branch_totals_array[$current_branch] > 0) {
                            $branch_totals_array[$current_branch] = $loan_amount + $branch_totals_array[$current_branch];
                        } else {
                            $branch_totals_array[$current_branch] = $loan_amount;
                        }
                    }
                }


                ?>
                <table class="table table-striped table-condensed">
                    <tr>
                        <th>Branch</th>
                        <th>Monthly Target</th>
                        <th>MTD Target</th>
                        <th>Actual</th>
                        <th>Deficit</th>
                        <th>Rate</th>
                    </tr>
                    <?php
                    foreach ($branch_totals_array as $bra => $branch_total) {
                        ///----Remove HQ
                        if ($bra > 1) {
                            $monthly_target = $branch_disb_targets[$bra];
                            $mtd_target = mtd_target($monthly_target);
                            $deficit = false_zero($mtd_target - $branch_total);
                            $branch_name = $branches_array[$bra];
                            if (input_available($branch_name) == 0) {
                                $branch_name = 'Unspecified';
                            }
                            $total_target = $total_target + $monthly_target;
                            $total_mtd_target = $total_mtd_target + $mtd_target;
                            $total_disbursed = $total_disbursed + $branch_total;
                            $rate = false_zero(ceil((($branch_total / $mtd_target) * 100)));
                            echo "<tr><td>" . $branch_name . "</td><td>" . money($monthly_target) . "</td><td>" . money($mtd_target) . "</td><td>" . money($branch_total) . "</td><td>" . money($deficit) . "</td><td><span class=\"font-16 font-bold text-black\">$rate%</span></td></tr>";
                        }
                    }
                    $total_deficit =  $total_target - $total_disbursed;
                    $average_rate = false_zero(ceil((($total_disbursed / $total_mtd_target) * 100)));
                    ?>

                    <tr class="font-16 text-blue">
                        <th>Total.</th>
                        <th><?php echo money($total_target) ?></th>
                        <th><?php echo money($total_mtd_target); ?></th>
                        <th><?php echo money($total_disbursed) ?></th>
                        <th><?php echo money($total_deficit); ?></th>
                        <th><span class="font-18 font-bold text-blue">~<?php echo $average_rate; ?>%</span></th>
                    </tr>
                </table>

            </div>
            <!-- /.box-body -->
        </div>
    </div>

    <div class="col-lg-6 col-xs-6">
        <!-- small box -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Daily Performance</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body max-height">
                <?php
                ////------------Disbursements
                $loans_daily = fetchtable2('o_loans', "disbursed=1 AND given_date >= '$this_year-$this_month-01'  $andbranch_loans AND status!=0", "uid", "asc", "uid, customer_id, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, given_date, final_due_date, current_agent, current_branch, status");
                $day_totals_array = array();
                while ($dt = mysqli_fetch_array($loans_daily)) {
                    $given_date = $dt['given_date'];
                    $given_date_array = explode('-', $given_date);
                    $loan_day = $given_date_array[2];

                    if ($this_year == $given_date_array[0] && $this_month == $given_date_array[1]) {
                        $loan_amount = $dt['loan_amount'];
                        if ($day_totals_array[$loan_day] > 0) {
                            $day_totals_array[$loan_day] = $loan_amount + $day_totals_array[$loan_day];
                        } else {
                            $day_totals_array[$loan_day] = $loan_amount;
                        }
                        $total_disbursed_daily = $total_disbursed_daily + $loan_amount;
                    }
                }



                //////-----------Collections
                $collections_daily = fetchtable('o_incoming_payments', "status=1 AND payment_date >= '$this_year-$this_month-01' $andbranch_payments", "uid", "asc", "100000000", "uid, customer_id, branch_id, amount,payment_date");
                $total_collected_daily = 0;
                $day_total_collections_array = array();
                while ($dc = mysqli_fetch_array($collections_daily)) {
                    $paid_date = $dc['payment_date'];
                    $paid_date_array = explode('-', $paid_date);
                    $pay_day = $paid_date_array[2];

                    if ($this_year == $paid_date_array[0] && $this_month == $paid_date_array[1]) {
                        $payment_amount = $dc['amount'];
                        if ($day_total_collections_array[$pay_day] > 0) {
                            $day_total_collections_array[$pay_day] = $payment_amount + $day_total_collections_array[$pay_day];
                        } else {
                            $day_total_collections_array[$pay_day] = $payment_amount;
                        }
                        $total_collected_daily = $total_collected_daily + $payment_amount;
                    }
                }

                /////------------------New customers
                $new_clients_daily = fetchtable('o_customers', "status=1 AND added_date  >= '$this_year-$this_month-01 00:00:00' $andbranch_customers", "uid", "asc", "10000000", "uid, branch, primary_product, DATE(added_date) as added_date");
                $day_total_customers = array();
                $total_join_daily = 0;
                while ($dcu = mysqli_fetch_array($new_clients_daily)) {

                    $join_date = $dcu['added_date'];
                    $join_date_array = explode('-', $join_date);
                    $join_day = $join_date_array[2];

                    if ($this_year == $join_date_array[0] && $this_month == $join_date_array[1]) {
                        $total_joins = 0;
                        if ($day_total_customers[$join_day] > 0) {
                            $day_total_customers[$join_day] =  $day_total_customers[$join_day] + 1;
                        } else {
                            $day_total_customers[$join_day] = 1;
                        }
                        $total_join_daily = $total_join_daily + 1;
                    }
                }


                ?>
                <table class="table table-striped">
                    <tr>
                        <th>Date</th>
                        <th>Disbursements</th>
                        <th>Collections</th>
                        <th>New Clients</th>
                    </tr>
                    <?php
                    for ($i = 1; $i <= $this_day; ++$i) {
                        $dtc = $day_total_customers[leading_zero($i)];
                        if ($dtc < 1) {
                            $dtc = 0;
                        }



                        echo "<tr><td>$this_year-$this_month-" . leading_zero($i) . "</td><td>" . money($day_totals_array[leading_zero($i)]) . "</td><td>" . money($day_total_collections_array[leading_zero($i)]) . "</td><td>" . $dtc . "</td></tr>";
                    }

                    ?>

                    <tr class="font-18 text-blue">
                        <th>Total.</th>
                        <th><?php echo money($total_disbursed_daily); ?></th>
                        <th><?php echo money($total_collected_daily); ?></th>
                        <th><?php echo $total_join_daily ?></th>
                    </tr>
                </table>

            </div>
            <!-- /.box-body -->
        </div>
    </div>

    <div class="col-lg-6 col-xs-6">
        <!-- small box -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Monthly Performance</h3>
            </div>
            <?php
            //////-----------Collections
            if ($show_monthly_dashboard == 'TRUE') {
                $collections_monthly = fetchtable('o_incoming_payments', "status=1 AND payment_date >= '$this_year-01-01' $andbranch_payments", "uid", "asc", "100000000", "uid, customer_id, branch_id, amount, payment_date");
                $month_total_collections_array = array();
                $total_collected_monthly = 0;
                while ($mc = mysqli_fetch_array($collections_monthly)) {
                    $paid_date = $mc['payment_date'];
                    $paid_date_array = explode('-', $paid_date);
                    $pay_day = $paid_date_array[1];



                    if ($this_year == $paid_date_array[0]) {
                        $payment_amount = $mc['amount'];
                        if ($month_total_collections_array[$pay_day] > 0) {
                            $month_total_collections_array[$pay_day] = $payment_amount + $month_total_collections_array[$pay_day];
                        } else {
                            $month_total_collections_array[$pay_day] = $payment_amount;
                        }
                        $total_collected_monthly = $total_collected_monthly + $payment_amount;
                    }
                }


                /////------------------New customers
                $new_clients_monthly = fetchtable('o_customers', "status=1 AND added_date >= '$this_year-01-01 00:00:00' $andbranch_customers", "uid", "asc", "10000000", "uid, branch, primary_product, DATE(added_date) as added_date");
                $month_total_customers = array();
                $total_join_monthly = 0;
                while ($dcu = mysqli_fetch_array($new_clients_monthly)) {

                    $join_date = $dcu['added_date'];
                    $join_date_array = explode('-', $join_date);
                    $join_month = $join_date_array[1];


                    if ($this_year == $join_date_array[0]) {
                        $total_joins = 0;
                        if ($month_total_customers[$join_month] > 0) {
                            $month_total_customers[$join_month] =  $month_total_customers[$join_month] + 1;
                        } else {
                            $month_total_customers[$join_month] = 1;
                        }
                        $total_join_monthly = $total_join_monthly + 1;
                    }
                }

            ?>
                <!-- /.box-header -->
                <div class="box-body">
                    <table class="table table-striped">
                        <tr>
                            <th>Month</th>
                            <th>Disbursements</th>
                            <th>Collections</th>
                            <th>New Clients</th>
                        </tr>
                        <?php
                        $loans_monthly = fetchtable2('o_loans', "disbursed=1 AND given_date >= '$this_year-01-01' $andbranch_loans AND status!=0", "uid", "asc", "uid, customer_id, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, given_date, final_due_date, current_agent, current_branch, status");
                        $month_totals_array = array();
                        $total_disbursed_monthly = 0;
                        while ($dm = mysqli_fetch_array($loans_monthly)) {
                            $given_date = $dm['given_date'];
                            $given_date_array = explode('-', $given_date);
                            $loan_month = $given_date_array[1];
                            if ($this_year == $given_date_array[0]) {
                                $loan_amount = $dm['loan_amount'];
                                if ($month_totals_array[$loan_month] > 0) {
                                    $month_totals_array[$loan_month] = $loan_amount + $month_totals_array[$loan_month];
                                } else {
                                    $month_totals_array[$loan_month] = $loan_amount;
                                }
                                $total_disbursed_monthly = $total_disbursed_monthly + $loan_amount;
                            }
                        }

                        $month_of_year = date('m');
                        for ($m = 1; $m <= $month_of_year; ++$m) {
                            $mtc = $month_total_customers[leading_zero($m)];
                            if ($mtc < 1) {
                                $mtc = 0;
                            }
                            echo " <tr><td>" . month_name($m) . "</td><td>" . money($month_totals_array[leading_zero($m)]) . "</td><td>" . money($month_total_collections_array[leading_zero($m)]) . "</td><td>" . $mtc . "</td></tr>";
                        }

                        ?>

                        <tr class="font-18 text-blue">
                            <th>Total.</th>
                            <th><?php echo money($total_disbursed_monthly); ?></th>
                            <th><?php echo money($total_collected_monthly); ?></th>
                            <th><?php echo $total_join_monthly; ?></th>
                        </tr>
                    </table>
                <?php
            }
                ?>
                </div>
                <!-- /.box-body -->
        </div>
    </div>
    <!-- ./col -->

    <!-- ./col -->
    <div class="col-lg-6 col-xs-6">
        <!-- small box -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Product Performance</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body">

                <table class="table table-striped">
                    <tr>
                        <th>Product</th>
                        <th>Disbursements</th>
                    </tr>

                    <?php
                    /*      $loans_product = fetchtable('o_loans',"disbursed=1 $andbranch_loans","uid","asc","100000000","uid, customer_id, product_id, loan_amount, disbursed_amount, total_repayable_amount, total_repaid, loan_balance, given_date, final_due_date, current_agent, current_branch, status");
                    $products = fetchtable('o_loan_products',"status=1","name","asc","1000","uid, name");
                    while($p = mysqli_fetch_array($products)){
                        $pid = $p['uid'];
                        $pname = $p['name'];
                        $products_array[$pid] = $pname;
                    }

                    $product_totals_array = array();
                    while($pr = mysqli_fetch_array($loans_product)){
                        $product = $pr['product_id'];
                        $given_date = $pr['given_date'];
                        $given_date_array = explode('-', $given_date);

                        if($this_year == $given_date_array[0] && $this_month == $given_date_array[1]) {
                            $loan_amount = $pr['loan_amount'];
                            $loan_amount_total = $loan_amount_total + $loan_amount;
                            if ($product_totals_array[$product] > 0) {
                                $product_totals_array[$product] = $loan_amount + $product_totals_array[$product];
                            } else {
                                $product_totals_array[$product] = $loan_amount;
                            }
                        }
                    }

                    foreach($product_totals_array as $pro => $product_total) {
                        $total_disbursed = $total_disbursed + $branch_total;
                        $product_total = $product_totals_array[$pro];
                        echo "<tr><td>".$products_array[$pro]."</td><td>".money($product_total)."</td></tr>";
                    }
                   */

                    ?>

                    <tr class="font-18 text-blue">
                        <th>Total.</th>
                        <th><?php echo money($loan_amount_total); ?></th>
                    </tr>
                </table>
            </div>
            <!-- /.box-body -->
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-12 col-xs-12 well well-sm shadow p-3 mb-5 bg-white rounded">
        <div class="box-header with-border">
            <h3 class="box-title box-primary">Performance BreakDown <i class="fa fa-line-chart"></i><span class="text-green"> Default shows MTD</span></h3>
        </div>
        <table class="table table-striped">
            <tr>
                <td> <input type="date" value="<?php echo $start_date; ?>" class="form-control" id="bdate_start"> </td>
                <td> <input type="date" value="<?php echo $date; ?>" class="form-control" id="bdate_end"> </td>
                <td> <button class="btn btn-primary" onclick="performance_breakdown_load();">GENERATE</button> </td>
            </tr>
        </table>
        <!-- small box -->
        <div id="perform_">
            Loading...
        </div>

    </div>

    <div style="display: none;" class="col-lg-12 col-xs-12 well well-sm shadow p-3 mb-5 bg-white rounded">
        <div class="box-header with-border">
            <h3 class="box-title box-primary">Income <i class="fa fa-line-chart"></i><span class="text-green"> Default shows MTD</span></h3>
        </div>
        <table class="table table-striped">
            <tr>
                <td> <input type="date" value="<?php echo $start_date; ?>" class="form-control" id="idate_start"> </td>
                <td> <input type="date" value="<?php echo $date; ?>" class="form-control" id="idate_end"> </td>
                <td> <button class="btn btn-primary" onclick="income_load();">GENERATE</button> </td>
            </tr>
        </table>
        <!-- small box -->
        <div id="income_">
            Loading...
        </div>

    </div>
    <div class="col-lg-12 col-xs-12 well well-sm shadow p-3 mb-5 bg-white rounded">
        <div class="box-header with-border">
            <h3 class="box-title box-primary">Collection Rate <i class="fa fa-line-chart"></i></h3>
        </div>

        <!-- small box -->
        <div id="collection_rate">
            Loading...
        </div>

    </div>

    <div class="col-lg-12 col-xs-12 well well-sm shadow p-3 mb-5 bg-white rounded">
        <div class="box-header with-border">
            <h3 class="box-title box-primary">Defaulters BreakDown <i class="fa fa-line-chart"></i><span class="text-green"> Default shows MTD</span></h3>
        </div>
        <table class="table table-striped">
            <tr>
                <td> <input type="date" value="<?php echo $start_date; ?>" class="form-control" id="ddate_start"> </td>
                <td> <input type="date" value="<?php echo $date; ?>" class="form-control" id="ddate_end"> </td>
                <td> <button class="btn btn-primary" onclick="defaulters_breakdown();">GENERATE</button> </td>
            </tr>
        </table>
        <!-- small box -->
        <div id="defaulters_">
            Loading...
        </div>

    </div>

    <!-- ./col -->
    </div>
    <?php

    function currencyUsed()
    {
        global $cc;
        $currency = "";
        if ($cc == 256) {
            $currency = "UGX";
        } elseif ($cc == 254) {
            $currency = 'KES';
        } else if($cc == 255){
            $currency = 'TZS';
        }

        return $currency;
    }

    ?>