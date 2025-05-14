<?php

$branch_leads = array();
$leads = fetchtable('o_customers', "status in (3) $andbranch_client AND added_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'", "uid", "asc", "1000000", "uid, branch");
while ($l = mysqli_fetch_array($leads)) {
    $lid = $l['uid'];
    $branch = $l['branch'];
    $branch_leads = obj_add($branch_leads, $branch, 1);
}

$branch_interactions = array();
$interactions = fetchtable('o_customer_conversations', "conversation_date BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59' $andbranch_client", "uid", "asc", "1000000", "customer_id, branch");
while ($i = mysqli_fetch_array($interactions)) {
    $iid = $i['uid'];
    $branch = $i['branch'];
    $branch_interactions = obj_add($branch_interactions, $branch, 1);
}


// collection rate
$m = date('m', strtotime($end_date));
$thisyearmonth = "$thisyear-" . $m . "-01";
$thisyearmonth_last_month = datesub($thisyearmonth, 0, 1, 0);
$thisyearmonthend = last_date_of_month($thisyearmonth_last_month);
$branch_repayable_due_array = array();
$branch_paid_array = array();
$due_ = fetchtable('o_loans', "disbursed=1 $andRegbranches_loan AND given_date BETWEEN '$thisyearmonth_last_month' AND '$thisyearmonthend'", "uid", "asc", "1000000", "uid, loan_amount, total_repayable_amount, total_repaid, current_branch, loan_balance");
while ($du = mysqli_fetch_array($due_)) {
    $amnt = $du['total_repayable_amount'];  //////-------------Hot fix to make this total due rather than principal due
    $total_repa = $du['total_repaid'];
    $bran_ = $du['current_branch'];
    $balance = $du['loan_balance'];

    $branch_repayable_due_array = obj_add($branch_repayable_due_array, $bran_, $amnt);
    $branch_paid_array = obj_add($branch_paid_array, $bran_, $total_repa);
}




$customer_l = table_to_array('o_loans', "given_date >= '$start_date' AND given_date <= '$end_date' AND disbursed=1 AND status!=0 $andRegbranches_loan", "1000000", "customer_id");
$customer_list = implode(',', $customer_l);
$customer_loans = table_to_obj2('o_customers', "uid in ($customer_list)", "100000", "uid", array('total_loans', 'branch'));


// for classifying by regions
$branches = table_to_obj('o_branches', "status=1 $andRegbranches $andRegion AND uid > 1", "1000", "uid", "name");
$regions = table_to_obj('o_regions', "status=1 $andRegionUid", "1000", "uid", "name");
// $branch_regions = table_to_obj('o_branches', "status=1 $andRegion AND uid > 1", "1000", "uid", "region_id");



$new_branch_loans_sum = array();
$repeat_branch_loans_sum = array();

$active_custs = []; // store already iterated active customers
$active_custs_branches = []; // to store key value pair branch uid => customer uid

$new_custs = []; // store already iterated new customers
$new_custs_branches = []; // to store key value pair branch uid => customer uid

$repeat_custs = []; // store already iterated repeat customers
$repeat_custs_branches = []; // to store key value pair branch uid => customer uid



/////-------------------Loans taken previous month
$older_customer_loans = array();
$during_customer_loans = array();
$loans_older = fetchtable('o_loans', "given_date < '$start_date' $andRegbranches_loan AND disbursed=1 AND status!=0 AND customer_id in ($customer_list)", "uid", "asc", "1000000", "customer_id");
while ($p = mysqli_fetch_array($loans_older)) {
    $customer_id = $p['customer_id'];
    $older_customer_loans = obj_add($older_customer_loans, $customer_id, 1); ///Loans taken before this period selected

}


///------------End of loans taken before
///------------Loans taken during
$loans_during = fetchtable('o_loans', "given_date >= '$start_date' AND given_date <= '$end_date' $andRegbranches_loan AND disbursed=1 AND status!=0 AND customer_id in ($customer_list)", "uid", "asc", "1000000", "customer_id");
while ($d = mysqli_fetch_array($loans_during)) {
    $customer_id = $d['customer_id'];
    $during_customer_loans = obj_add($during_customer_loans, $customer_id, 1); ///Loans taken during this period

}


///-------------End of loans taken during


$loans = fetchtable('o_loans', "given_date >= '$start_date' AND given_date <= '$end_date' $andRegbranches_loan AND disbursed=1 AND status!=0", "uid", "asc", "1000000", "current_branch, customer_id, loan_amount");

$new_customers_uids = array();
$repeat_customers_uids = array();
// try to get uid from global else use default of 10
$targetReportUID = encurl($customer_filter_uid ? $customer_filter_uid : 10);
while ($l = mysqli_fetch_array($loans)) {
    $branch_l = $l['current_branch'];
    $customer_id = $l['customer_id'];
    $loan_amount = $l['loan_amount'];
    $customer_loans_arr = $customer_loans[$customer_id];
    $customer_total_loans = $customer_loans_arr['total_loans'] ?? 0;
    $customer_branch  = $customer_loans_arr['branch'] ?? '';
    // $customer_branch = $customer_branches[$customer_id];


    $customer_older_loans = $older_customer_loans[$customer_id];
    $customer_during_loans = $during_customer_loans[$customer_id];
    $customer_total_loans = $customer_older_loans + $customer_during_loans;

    if ($customer_during_loans > 0 && $customer_older_loans < 1) {
        $new_branch_loans_sum = obj_add($new_branch_loans_sum, $branch_l, $loan_amount);

        // add new customer to array if not yet added
        if (!isset($new_custs[$customer_id])) {
            $new_custs[$customer_id] = 1;
            $new_custs_branches = obj_add($new_custs_branches, $branch_l, 1);
        }
    } else if ($customer_during_loans > 0 && $customer_older_loans > 0) {
        $repeat_branch_loans_sum = obj_add($repeat_branch_loans_sum, $branch_l, $loan_amount);

        // add repeat customer to array if not yet added
        if (!isset($repeat_custs[$customer_id])) {
            $repeat_custs[$customer_id] = 1;
            $repeat_custs_branches = obj_add($repeat_custs_branches, $branch_l, 1);
        }
    }

    // add active customer to array if not yet added
    if (!isset($active_custs[$customer_id])) {
        $active_custs[$customer_id] = 1;
        $active_custs_branches = obj_add($active_custs_branches, $branch_l, 1);
    }
}

?>

<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Branch</th>
                <th>New Leads</th>
                <th>New Customers</th>
                <th>New Total Loans</th>
                <th>Repeat Customers</th>
                <th>Repeat Total Loans</th>
                <th>Active Customers</th>
                <th>All Total Loans</th>
                <th>Total Interactions</th>
                <th>Collection Rate (%)</th>
            </tr>
        </thead>
        <tbody>
            <?php

            // initialize variables to store regions totals
            // $regions_total_leads = $regions_total_new_customers = $regions_total_active_customers = $regions_total_repeat_loans = $regions_new_sum = $regions_repeat_sum = $regions_total_loans = 0;

            // foreach ($regions as $rid => $rname) {
            //     $region_total_leads = $region_total_new_customers = $region_total_active_customers = $region_total_repeat_loans = $region_new_sum = $region_repeat_sum = $region_total_loans = 0;
            //     foreach ($branches as $bid => $bname) {
            //         if ($branch_regions[$bid] == $rid) {
            //             $region_total_leads += $branch_leads[$bid] ?? 0;
            //             $region_total_new_customers += $new_custs_branches[$bid] ?? 0;
            //             $region_total_active_customers += $active_custs_branches[$bid] ?? 0;
            //             $region_total_repeat_loans += $repeat_custs_branches[$bid] ?? 0;

            //             $region_new_sum += $new_branch_loans_sum[$bid] ?? 0;
            //             $region_repeat_sum += $repeat_branch_loans_sum[$bid] ?? 0;

            //             $region_total_loans = $region_new_sum + $region_repeat_sum;
            //         }
            //     }

            //     $regions_total_leads += $region_total_leads;
            //     $regions_total_new_customers += $region_total_new_customers;
            //     $regions_total_active_customers += $region_total_active_customers;
            //     $regions_total_repeat_loans += $region_total_repeat_loans;

            //     $regions_new_sum += $region_new_sum;
            //     $regions_repeat_sum += $region_repeat_sum;

            //     $regions_total_loans = $regions_new_sum + $regions_repeat_sum;

            //     echo "<tr><td>$rid</td><td>$rname</td><td>$region_total_leads</td><td>$region_total_new_customers</td><td>" . number_format($region_new_sum, 2) . "</td><td>$region_total_repeat_loans</td><td>" . number_format($region_repeat_sum, 2) . "<td>$region_total_active_customers</td></td><td>" . number_format($region_total_loans, 2) . "</td></tr>";
            // }





            $total_leads = $total_new_customers = $total_active_customers = $total_repeat_loans = $new_sum = $repeat_sum = $total_loans = $total_interactions = $total_repayable_due = $total_paid = 0;
            foreach ($branches as $bid => $bname) {
                $new_leads = false_zero($branch_leads[$bid]);
                $new_loans = $new_custs_branches[$bid] ?? 0;
                $active_custs_b = $active_custs_branches[$bid] ?? 0;
                $repeat_loans = $repeat_custs_branches[$bid] ?? 0;
                $binteractions = $branch_interactions[$bid] ?? 0;
                $branch_repayable_due = $branch_repayable_due_array[$bid] ?? 0;
                $total_repayable_due += $branch_repayable_due;
                $branch_paid = $branch_paid_array[$bid] ?? 0;
                $total_paid += $branch_paid;
                $branch_collection_rate = round(($branch_paid / $branch_repayable_due) * 100, 2);


                $new_loans_sum = $new_branch_loans_sum[$bid];
                $repeat_loans_sum = $repeat_branch_loans_sum[$bid];

                $total_loans = $new_loans_sum + $repeat_loans_sum;

                $total_leads += $new_leads;
                $total_interactions += $binteractions;
                $total_new_customers += $new_loans;
                $total_active_customers += $active_custs_b;
                $total_repeat_loans += $repeat_loans;


                $new_sum += $new_loans_sum;
                $repeat_sum += $repeat_loans_sum;

                $go_new_leads = "<a href=\"reports?hreport=sl-customer-filters.php&from=$start_date&to=$end_date&branch=" . encurl($bid) . "&type=LEADS&hid=" . $targetReportUID . "\" target='_blank'><i class='fa fa-external-link-square'></i><a>";
                $go_new_loans = "<a href=\"reports?hreport=sl-customer-filters.php&from=$start_date&to=$end_date&branch=" . encurl($bid) . "&type=NEW-CUSTOMERS&hid=" . $targetReportUID . "\" target='_blank'><i class='fa fa-external-link-square'></i><a>";
                $go_active_customers = "<a href=\"reports?hreport=sl-customer-filters.php&from=$start_date&to=$end_date&branch=" . encurl($bid) . "&type=ACTIVE-CUSTOMERS&hid=" . $targetReportUID . "\" target='_blank'><i class='fa fa-external-link-square'></i><a>";
                $go_repeat_loans = "<a href=\"reports?hreport=sl-customer-filters.php&from=$start_date&to=$end_date&branch=" . encurl($bid) . "&type=REPEAT-CUSTOMERS&hid=" . $targetReportUID . "\" target='_blank'><i class='fa fa-external-link-square'></i><a>";



                echo "<tr><td>$bid</td><td>$bname</td><td>$new_leads $go_new_leads</td><td>$new_loans $go_new_loans</td><td>" . number_format($new_loans_sum, 2) . "</td><td>$repeat_loans $go_repeat_loans</td><td>" . number_format($repeat_loans_sum, 2) . "<td>$active_custs_b $go_active_customers</td></td><td>" . number_format($total_loans, 2) . "</td><td>$binteractions</td><td>$branch_collection_rate</td></tr>";
                $total_loans = 0;
            }
            ?>
        </tbody>

        <tfoot>
            <tr>
                <th>#</th>
                <th>--</th>
                <th><?php echo number_format($total_leads); ?></th>
                <th><?php echo number_format($total_new_customers); ?></th>
                <th><?php echo number_format($new_sum, 2); ?></th>
                <th><?php echo number_format($total_repeat_loans); ?></th>
                <th><?php echo number_format($repeat_sum, 2); ?></th>
                <th><?php echo number_format($total_active_customers); ?></th>
                <th><?php echo number_format($total_loans) ?></th>
                <th><?php echo number_format($total_interactions) ?></th>
                <th><?php echo round(($total_paid / $total_repayable_due) * 100, 2); ?></th>
            </tr>
        </tfoot>
    </table>


</div>

</div>

<?php
