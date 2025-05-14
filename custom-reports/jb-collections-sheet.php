<?php

// include_once("../configs/conn.inc");
// include_once("../php_functions/functions.php");
$branch = $_GET['branch'];
$group = $_GET['group_'];
if($group > 0){
    $andgroup = " AND group_id = $group ";
}
else{
    $andgroup = "";
}

$monthly_loan = fetchtable2("o_loans", "disbursed=1 $andgroup AND status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date'", "uid", "DESC", "uid, customer_id, group_id, product_id, loan_amount, total_repayable_amount, total_repaid, next_due_date, current_instalment_amount, given_date, final_due_date, current_agent, status");

$cust_names = table_to_obj('o_customers', "uid > 0", "10000000", "uid", "full_name");
$cust_grp_names = table_to_obj('o_customer_groups', "uid > 0", "1000", "uid", "group_name");
$prod_names = table_to_obj('o_loan_products', "uid > 0", "1000", "uid", "name");
$agent_names = table_to_obj('o_users', "uid > 0", "1000000", "uid", "name");
$l_status_names = table_to_obj('o_loan_statuses', "uid > 0", "1000", "uid", "name");

$entries = "";
$loan_amt_total = 0.00;
$repayable_amt_total = 0.00;
$repaid_amt_total = 0.00;
$cur_instal_amt_total = 0.00;
$overdue_bal_total = 0.00;

if (intval(mysqli_num_rows($monthly_loan)) > 0) {
    while ($l = mysqli_fetch_array($monthly_loan)) {
        // setting variables
        $uid = $l["uid"];
        $cust_id = intval($l["customer_id"]); $cust_name = $cust_id > 0 && $cust_names[$cust_id] ? $cust_names[$cust_id] : 'N/A';
        $cust_grp_id = intval($l["group_id"]); $cust_grp_name = $cust_grp_id > 0 && $cust_grp_names[$cust_grp_id] ? $cust_grp_names[$cust_grp_id] : 'N/A';
        $prod_id = intval($l["product_id"]); $prod_name =  $prod_id > 0 && $prod_names[$prod_id] ? $prod_names[$prod_id] : 'N/A';
        $loan_amt = round((double) $l["loan_amount"], 2);
        $repayable_amt = round((double) $l["total_repayable_amount"], 2);
        $repaid_amt = round((double) $l["total_repaid"], 2);
        $cur_instal_amt = round((double) $l["current_instalment_amount"], 2);
        $next_instal_date = $l["next_due_date"];
        $given_date = $l["given_date"];
        $final_due_date = $l["final_due_date"];
        $agent_id = intval($l["current_agent"]); $agent_name = $agent_id > 0 &&  $agent_names[$agent_id] ? $agent_names[$agent_id] : 'N/A';
        $status_id = intval($l["status"]); $status_name = $status_id > 0 && $l_status_names[$status_id] ? $l_status_names[$status_id] : 'N/A';


        // Arithmetic operations
        $loan_amt_total += $loan_amt;
        $repayable_amt_total += $repayable_amt;
        $cur_instal_amt_total += $cur_instal_amt;
        $repaid_amt_total += $repaid_amt;
        $overdue_bal =  $repaid_amt >= $cur_instal_amt ? 0.00 : $cur_instal_amt - $repaid_amt;
        $overdue_bal_total += $overdue_bal;

        $entries .= "
                <tr>
                    <th>$uid</th>
                    <td>$cust_name</td>
                    <td>$cust_grp_name</td>
                    <td>$prod_name</td>
                    <td>" . money($loan_amt) . "</td>
                    <td>" . money($repayable_amt) . "</td>
                    <td>" . money($cur_instal_amt) . "</td>
                    <td>" . money($repaid_amt) . "</td>
                    <td>" . money($overdue_bal) . "</td>
                    <td> $next_instal_date </td>
                    <td> $given_date </td>
                    <td> $final_due_date </td>
                    <td> $agent_name </td>
                    <td> $status_name </td>
                </tr>";
    }
} else {
    $entries .= "
        <tr><td colspan='14'>
            <i>No records found</i>
            </td>
        </tr>";
}

?>
<div class="row">
    <div class="col-sm-3">
<select class="form-control" id="group_" onchange="collection_sheet('<?php echo $start_date; ?>','<?php echo $end_date; ?>','<?php echo $branch;?>');">
    <option value="0">--All Groups</option>
    <?php
    $groups = fetchtable('o_customer_groups',"status=1","group_name","asc","10000","uid, group_name");
    while($g = mysqli_fetch_array($groups)){
        $gid = $g['uid'];
        $gname = $g['group_name'];
        if($gid == $group){
            $selected = " SELECTED";
        }
        else{
            $selected = "";
        }

        echo "<option $selected value='$gid'>$gname</option>";
    }
    ?>

</select>
    </div>
</div>

<table class="table table-condensed table-striped" id="example2">
    <thead>
        <tr>
            <th>UID</th>
            <th>Customer Name</th>
            <th>Group</th>
            <th>Product</th>
            <th>Given Amount</th>
            <th>Repayable Total</th>
            <th>Current Instalment Amount</th>
            <th>Paid</th>
            <th>Overdue Balance</th>
            <th>Next Instalment Date</th>
            <th>Given Date</th>
            <th>Due Date</th>
            <th>Agent</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        echo $entries
        ?>
    </tbody>
    <tfoot>
        <tr>
            <th>Total</th>
            <th>--</th>
            <th>--</th>
            <th>--</th>
            <th><?php echo money($loan_amt_total); ?></th>
            <th><?php echo money($repayable_amt_total); ?></th>
            <th> <?php echo money($cur_instal_amt_total) ; ?> </th>
            <th><?php echo money($repaid_amt_total) ; ?></th>
            <th><?php echo money($overdue_bal_total) ; ?></th>
            <th>--</th>
            <th>--</th>
            <th>--</th>
            <th>--</th>
            <th>--</th>
        </tr>
    </tfoot>
</table>

<script>
    function collection_sheet(start_date, end_date, branch){
        let group = $('#group_').val();
        let group_ = "reports?hreport=jb-collections-sheet.php&from="+start_date+"&to="+end_date+"&branch="+branch+"&group_="+group;
        gotourl(group_);
    }
</script>