<?php

$title = "Region Performance Summaries";

$customer_list_array = array();
$customer_branches_array = array();


$branches = table_to_obj('o_branches', "status=1 $andRegbranches $andRegion", "1000", "uid", "name");
$regions = table_to_obj('o_regions', "status=1 $andRegionUid", "1000", "uid", "name");
$branch_regions = table_to_obj('o_branches', "status=1 $andRegion", "1000", "uid", "region_id");

///////-----------------------Disbursements
$loans = fetchtable('o_loans', "disbursed=1 $andRegbranches_loan AND given_date BETWEEN '$start_date' AND '$end_date' AND status!=0", "uid", "asc", "100000000", "uid, customer_id ,loan_amount,  current_branch, current_lo, current_co, total_repaid, product_id");
$branch_totals_array = array();
$current_co_array = array();
$current_lo_array = array();
$product_disb_array = array();
$product_coll_array = array();
$loan_amounts_array = array();


while ($dp = mysqli_fetch_array($loans)) {
    $loan_id = $dp['uid'];
    $current_branch = $dp['current_branch'];
    $loan_amount = $dp['loan_amount'];
    $current_lo = $dp['current_lo'];
    $current_co = $dp['current_co'];
    $total_repaid = $dp['total_repaid'];
    $product_id = $dp['product_id'];
    $customer_i = $dp['customer_id'];
    array_push($customer_list_array, $customer_i);
    $loan_amounts_array[$loan_id] = $loan_amount;

    if ($branch_totals_array[$current_branch] > 0) {
        $branch_totals_array[$current_branch] = $loan_amount + $branch_totals_array[$current_branch];
    } else {
        $branch_totals_array[$current_branch] = $loan_amount;
    }

    ///////---------CO
    if ($current_co_array[$current_co] > 0) {
        $current_co_array[$current_co] = $total_repaid + $current_co_array[$current_co];
    } else {
        $current_co_array[$current_co] = $total_repaid;
    }
    //////----------LO
    if ($current_lo_array[$current_lo] > 0) {
        $current_lo_array[$current_lo] = $loan_amount + $current_lo_array[$current_co];
    } else {
        $current_lo_array[$current_lo] = $loan_amount;
    }
    /////----------Product Disbursement
    if ($product_disb_array[$product_id] > 0) {
        $product_disb_array[$product_id] = $loan_amount + $product_disb_array[$product_id];
    } else {
        $product_disb_array[$product_id] = $loan_amount;
    }
    /////////--------Product Collection
    if ($product_coll_array[$product_id] > 0) {
        $product_coll_array[$product_id] = $total_repaid + $product_coll_array[$product_id];
    } else {
        $product_coll_array[$product_id] = $total_repaid;
    }
    /////////-------

}



/////////////----------------Collections
$collections = fetchtable('o_incoming_payments', "status=1 $andRegbranches_pay AND payment_date BETWEEN '$start_date' AND '$start_date'", "uid", "asc", "100000000", "uid, branch_id, amount");
$branch_totals_collections_array = array();
while ($col = mysqli_fetch_array($collections)) {
    $current_branch = $col['branch_id'];
    $collections_amount = $col['amount'];
    if ($branch_totals_collections_array[$current_branch] > 0) {
        $branch_totals_collections_array[$current_branch] = $collections_amount + $branch_totals_collections_array[$current_branch];
    } else {
        $branch_totals_collections_array[$current_branch] = $collections_amount;
    }
}


echo "<table class='table table-bordered grid-width-50 col-lg-6' style='width: 40%;'>";
echo "<thead><tr><th>Region</th><th>Disbursement</th><th>Collections</th></tr></thead>";


// regional totals 
$regions_disb_total = $regions_collection_total = 0;
foreach ($regions as $rid => $rname) {
    $region_disb_total = $region_collection_total = 0;
    foreach ($branches as $bid => $bname) {
        if ($branch_regions[$bid] == $rid) {
            $region_disb_total = $region_disb_total + $branch_totals_array[$bid];
            $region_collection_total = $region_collection_total + $branch_totals_collections_array[$bid];
        }
    }
    $regions_disb_total = $regions_disb_total + $region_disb_total;
    $regions_collection_total = $regions_collection_total + $region_collection_total;
    echo "<tr><td>$rname</td><td>" . money($region_disb_total) . "</td><td>" . money($region_collection_total) . "</td></tr>";
}

echo "<thead><tr><th>Total</th><th>" . money($regions_disb_total) . "</th><th>" . money($regions_collection_total) . "</th></tr></thead>";
echo "</table>";
?>
<div class="col-md-6 well well-sm">
    <?php

    // $inception_year variable can be configured from config.php for the different companies else will default to 2021
    $inception_year = $inception_year ? $inception_year : 2021;
    $year_filter = "<select id='seletecd_filter_year' name='y' class='form-control' style='width: 100px;'>";

    for ($inception_year = 2021; $inception_year <= $thisyear; $inception_year++) {
        if ($_GET['y'] == $inception_year) {
            $year_filter .= "<option value='$inception_year' selected>$inception_year</option>";
        } elseif ($thisyear == $inception_year && !isset($_GET['y'])) {
            $year_filter .= "<option value='$inception_year' selected>$inception_year</option>";
        } else {
            $year_filter .= "<option value='$inception_year'>$inception_year</option>";
        }
    }
    $year_filter .= "</select>";

    echo $year_filter;


    // months to show
    $months_count = $_GET['y'] && $_GET['y'] == $thisyear ? $thismonth : 12;
    // if(isset($_GET['y']) && $_GET['y'] == $thisyear) {
    //     $months_count = $thismonth;
    // } elseif(isset($_GET['y']) && $_GET['y'] < $thisyear) {
    //     $months_count = 12;
    // } elseif(!isset($_GET['y'])) {
    //     $months_count = $thismonth;
    // }

    for ($i = 1; $i <= $months_count; ++$i) {
        echo "<button class='btn btn-outline-black' onclick='region_filter_collec_report($i)'>" . month_name($i) . "</button> ";
    }
    if (isset($_GET['m'])) {
        $m = $_GET['m'];
    } else {
        $m = $thismonth;
    }

    if (isset($_GET['y'])) {
        $thisyear = $_GET['y'];
    }

    $thisyearmonth = "$thisyear-" . leading_zero(number_format($m)) . "-01";
    $thisyearmonth_last_month = datesub($thisyearmonth, 0, 1, 0);
    $thisyearmonthend = last_date_of_month($thisyearmonth_last_month);

    ?>

    <h3>Collection Rate, <b><?php echo month_name($m); ?></b></h3> (<i>Loans Given Last Month, Due this month</i>)
    <?php
    $branch_principle_due_array = array();
    $branch_paid_array = array();

    $due_ = fetchtable('o_loans', "disbursed=1 $andRegbranches_loan AND given_date BETWEEN '$thisyearmonth_last_month' AND '$thisyearmonthend'", "uid", "asc", "1000000", "uid, loan_amount, total_repayable_amount, total_repaid, current_branch, loan_balance");
    while ($du = mysqli_fetch_array($due_)) {
        $amnt = $du['total_repayable_amount'];  //////-------------Hot fix to make this total due rather than principal due

        $total_repa = $du['total_repaid'];
        $bran_ = $du['current_branch'];
        $total_repayable_amount = $du['total_repayable_amount'];
        $balance = $du['loan_balance'];

        $branch_principle_due_array = obj_add($branch_principle_due_array, $bran_, $amnt);
        $branch_paid_array = obj_add($branch_paid_array, $bran_, $total_repa);
    }
    ?>

    <table class="table table-striped table-bordered">
        <tr>
            <th>Branch</th>
            <th>Due This Month</th>
            <th>Collected so Far</th>
            <th>Rate %</th>
        </tr>
        <?php
        $regions_total_principle_due = $regions_total_paid_ = 0;
        foreach ($regions as $rid => $rname) {
            $region_total_principle_due = $region_total_paid_ = 0;
            foreach ($branches as $bid => $bname) {
                if ($branch_regions[$bid] == $rid) {
                    $region_total_principle_due = $region_total_principle_due + $branch_principle_due_array[$bid];
                    $region_total_paid_ = $region_total_paid_ + $branch_paid_array[$bid];
                }
            }
            $regions_total_principle_due = $regions_total_principle_due + $region_total_principle_due;
            $regions_total_paid_ = $regions_total_paid_ + $region_total_paid_;
            $rate = round(($region_total_paid_ / $region_total_principle_due) * 100, 2);
            echo "<tr><td>$rname</td><td>" . money($region_total_principle_due) . "</td><td>" . money($region_total_paid_) . "</td><td>" . false_zero($rate) . "%</td></tr>";
        }

        $average = round(($regions_total_paid_ / $regions_total_principle_due) * 100, 2);
        ?>
        <tr>
            <th>Total</th>
            <th><?php echo money($regions_total_principle_due) ?></th>
            <th><?php echo money($regions_total_paid_) ?></th>
            <th><?php echo $average; ?>%</th>
        </tr>
    </table>
</div>

<script>
    function region_filter_collec_report(month) {
        const region = $('#region_').val();
        const from = '<?php echo $start_date; ?>'?.trim();
        const to = '<?php echo $end_date; ?>'?.trim();
        const year = document.getElementById('seletecd_filter_year').value;
        const url = "?regreport=sl-region-performance-summaries.php&m=" + month + "&y=" + year + "&from=" + from + "&to=" + to + "&region=" + region;
        window.location.href = url;
    }
</script>