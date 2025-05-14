<?php

$branch_leads = array();
//////-----------------Leads added during the period
$leads = fetchtable('o_customers',"status in (3) $andbranch_client AND date(added_date) >= '$start_date' AND date(added_date) <= '$end_date'","uid","asc","1000000","uid, branch, added_date");
while($l = mysqli_fetch_array($leads)){
    $lid = $l['uid'];
    $branch = $l['branch'];
    $added_date = $l['added_date'];
    $branch_leads = obj_add($branch_leads, $branch, 1);

}

/////----------Array of all customers given a loan between the dates
$customer_l = table_to_array('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date' AND disbursed=1 AND status!=0 $andbranch_loan","1000000","customer_id");
$customer_list = implode(',', $customer_l);

//////---------Total loans per customer for all the customers in the list
$customer_loans = table_to_obj('o_customers',"uid in ($customer_list)","100000","uid","total_loans");

////------------The branches the customers are in
$customer_branches = table_to_obj('o_customers',"uid in ($customer_list)","100000","uid","branch");


$new_branch_loans = array();
$new_branch_loans_sum = array();
$repeat_branch_loans = array();
$repeat_branch_loans_sum = array();
$active_custs = []; // store already iterated active customers
$active_custs_branches = []; // to store key value pair branch uid => customer uid


/////-------------------Loans taken by customers before this period
$older_customer_loans = array();
$during_customer_loans = array();
$loans_older = fetchtable('o_loans',"given_date < '$start_date'  $andbranch_loan AND disbursed=1 AND status!=0 AND customer_id in ($customer_list)","uid","asc","1000000","uid, current_branch, current_lo,customer_id, loan_amount");
while($p = mysqli_fetch_array($loans_older)) {
    $loid = $p['uid'];
    $branch_l = $p['current_branch'];
    $customer_id = $p['customer_id'];
    $current_lo = $p['current_lo'];
    $older_customer_loans = obj_add($older_customer_loans, $customer_id, 1); ///Loans taken before this period selected

}


///------------End of loans taken before
///------------Loans taken during this period
$loans_during = fetchtable('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date'  $andbranch_loan AND disbursed=1 AND status!=0 AND customer_id in ($customer_list)","uid","asc","1000000","uid, current_branch, current_lo,customer_id, loan_amount");
while($d = mysqli_fetch_array($loans_during)) {
    $loid = $d['uid'];
    $branch_l = $d['current_branch'];
    $customer_id = $d['customer_id'];
    $current_lo = $d['current_lo'];
    $during_customer_loans = obj_add($during_customer_loans, $customer_id, 1); ///Loans taken during this period

}


///-------------End of loans taken during


//////-----All the loans during this period
$checked_customers = [];
$loans = fetchtable('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date' $andbranch_loan AND disbursed=1 AND status!=0","uid","asc","1000000","uid, current_branch, customer_id, loan_amount");

while($l = mysqli_fetch_array($loans)){
    $loid = $l['uid'];
    $branch_l = $l['current_branch'];
    $customer_id = intval($l['customer_id']);
    $loan_amount = $l['loan_amount'];

   // $customer_total_loans = $customer_loans[$customer_id];
    $customer_branch = $customer_branches[$customer_id];

    $customer_older_loans = $older_customer_loans[$customer_id];
    $customer_during_loans = $during_customer_loans[$customer_id];
    //$customer_total_loans = $customer_loans[$customer_id];
    $customer_total_loans = $customer_older_loans + $customer_during_loans;
    $customer_lo = $customer_branches[$customer_id];

    //////-------------Customers have taken loans during this period but never before, new loans
    if($customer_during_loans > 0 && $customer_older_loans < 1 && !(in_array($customer_id, $checked_customers))){
        // check if customer is already iterated
        $new_branch_loans = obj_add($new_branch_loans, $branch_l, 1);
        $new_branch_loans_sum = obj_add($new_branch_loans_sum, $branch_l, $loan_amount);
    }
    ////---------Customers has taken loans now and even before
    else if($customer_during_loans > 0 && $customer_older_loans > 0 && !(in_array($customer_id, $checked_customers))){
        $repeat_branch_loans = obj_add($repeat_branch_loans, $branch_l, 1);
        $repeat_branch_loans_sum = obj_add($repeat_branch_loans_sum, $branch_l, $loan_amount);
    }

    // add active customer to array if not yet added
    if(!isset($active_custs[$customer_id])){
        $active_custs[$customer_id] = 1;
        $active_custs_branches = obj_add($active_custs_branches, $branch_l, 1);
    }

    // updates checked customers array
    array_push($checked_customers, $customer_id);
}

?>

    <div class="col-sm-12">
        <table id="example2" class="table table-condensed table-striped table-bordered">
            <thead>
            <tr><th>ID</th><th>Branch</th><th>New Leads</th> <th>New Customers</th><th>Active Customers</th><th>New Total Loans</th> <th>Repeat Customers</th><th>Repeat Total Loans</th><th>All Total Loans</th></tr>
            </thead>
            <tbody>
            <?php
            $total_leads = $total_new_customers = $total_active_customers = $total_repeat_loans = $new_sum = $repeat_sum = $total_loans = $total_total_loans =0;
            $branches = fetchtable('o_branches',"uid > 0 $andbranch1","uid","asc","1000","uid, name");
            
            while($b = mysqli_fetch_array($branches)){
                $bid = $b['uid'];
                $bname = $b['name'];
                $new_leads = false_zero($branch_leads[$bid]);
                $new_loans = false_zero($new_branch_loans[$bid]);
                $active_custs_b = false_zero($active_custs_branches[$bid]);
                $repeat_loans = false_zero($repeat_branch_loans[$bid]);

                $new_loans_sum = $new_branch_loans_sum[$bid];
                $repeat_loans_sum = $repeat_branch_loans_sum[$bid];

                $total_loans = $new_loans_sum + $repeat_loans_sum;

                $total_leads+=$new_leads;
                $total_new_customers+=$new_loans;
                $total_active_customers += $active_custs_b;
                $total_repeat_loans+=$repeat_loans;
                $new_sum+=$new_loans_sum;
                $repeat_sum+=$repeat_loans_sum;

                $go_new_leads = "<a href=\"reports?hreport=sp-customer-filters.php&from=$start_date&to=$end_date&branch=".encurl($bid)."&type=LEADS&hid=".encurl(10)."\" target='_blank'><i class='fa fa-external-link-square'></i><a>";
                $go_new_loans = "<a href=\"reports?hreport=sp-customer-filters.php&from=$start_date&to=$end_date&branch=".encurl($bid)."&type=NEW-CUSTOMERS&hid=".encurl(10)."\" target='_blank'><i class='fa fa-external-link-square'></i><a>";
                $go_active_customers = "<a href=\"reports?hreport=sp-customer-filters.php&from=$start_date&to=$end_date&branch=".encurl($bid)."&type=ACTIVE-CUSTOMERS&hid=".encurl(10)."\" target='_blank'><i class='fa fa-external-link-square'></i><a>";
                $go_repeat_loans = "<a href=\"reports?hreport=sp-customer-filters.php&from=$start_date&to=$end_date&branch=".encurl($bid)."&type=REPEAT-CUSTOMERS&hid=".encurl(10)."\" target='_blank'><i class='fa fa-external-link-square'></i><a>";



                echo "<tr><td>$bid</td><td>$bname</td><td>$new_leads $go_new_leads</td><td>$new_loans $go_new_loans</td><td>$active_custs_b $go_active_customers</td><td>".number_format($new_loans_sum,2)."</td><td>$repeat_loans $go_repeat_loans</td><td>".number_format($repeat_loans_sum,2)."</td><td>".number_format($total_loans,2)."</td></tr>";
                $total_loans = 0;
            }
            ?>
            </tbody>

            <tfoot>
            <tr><th>#</th><th>--</th><th><?php echo number_format($total_leads); ?></th> <th><?php echo number_format($total_new_customers); ?></th> <th><?php echo number_format($total_active_customers); ?></th> <th><?php echo number_format($new_sum, 2); ?></th> <th><?php echo number_format($total_repeat_loans); ?></th><th><?php echo number_format($repeat_sum, 2); ?></th><th><?php echo number_format($repeat_sum + $new_sum) ?></th></tr>
            </tfoot>
        </table>


    </div>

    </div>

<?php

