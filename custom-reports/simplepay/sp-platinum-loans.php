<?php

// Replace 'branch' with 'c.branch' for aliasing in the query
$andbranch_loan_ = str_replace('current_branch', 'o_loans.current_branch', $andbranch_loan);

$sql = "SELECT o_loans.uid, o_customers.full_name, o_branches.name as Branch, o_loans.customer_id, o_loans.account_number as Phone, o_loans.disbursed_amount as Amount_Disbursed, o_loans.product_id, o_loans.transaction_code, o_loans.total_addons as Interest, o_loans.total_repayable_amount as Amount_Repayable, o_loans.total_repaid as Amount_Repaid, o_loans.loan_balance as Balance, o_loans.given_date as Disbursement_Date,
TIME(o_loans.transaction_date) as `Disbursement_Time`, 
o_loans.final_due_date as Repayment_Date, lo.name as LO, co.name as CO, o_loans.application_mode, o_loan_statuses.name as Status FROM o_loans left join o_customers on o_customers.uid = o_loans.customer_id left join o_loan_statuses on o_loans.status = o_loan_statuses.uid left join o_branches on o_loans.current_branch = o_branches.uid left join o_users lo on o_loans.current_lo = lo.uid  left join o_users co on o_loans.current_co = co.uid  where o_loans.status != 0 AND o_loans.disbursed=1 AND o_loans.given_date BETWEEN '$start_date' AND '$end_date' AND o_loans.disbursed_amount >= 21000 $andbranch_loan_";


$result = mysqli_query($con, $sql);
$productNames = table_to_obj("o_loan_products", "uid > 0", "1000", "uid", "name");

?>

    <div class="col-sm-12">
        <table id="example2" class="table table-condensed table-striped table-bordered">
            <thead>
            <tr><th>UID</th><th>full_name</th><th>Branch</th> <th>Phone</th><th>Product</th><th>Amount_Disbursed</th> <th>Transaction_Code</th><th>Interest</th><th>Amount_Repayable</th><th>Amount_Repaid</th><th>Balance</th><th>Disbursement_Date</th><th>Disbursement_Time</th><th>Repayment_Date</th><th>LO</th><th>CO</th><th>application_mode</th><th>Status</th></tr>
            </thead>
            <tbody>
            <?php
            $total_amount_disbursed = $total_interest = $total_amount_repayable = 
            $total_amount_repaid = $total_balance = 0;


            $iterated_customers_uids = [];
            while($row = mysqli_fetch_array($result)) {

                $customer_id = intval($row['customer_id']);
                if(in_array($customer_id, $iterated_customers_uids)){
                    continue;
                }

                $uid = $row['uid'];
                $full_name = $row['full_name'];
                $Branch = $row['Branch'];
                $Phone = $row['Phone'];
                $Amount_Disbursed = $row['Amount_Disbursed'];
                $product_id = $row['product_id'] ?? 0;
                $product_name = $productNames[$product_id] ?? "";
                if($Amount_Disbursed >= 21000){
                    $product_name = "Platinum";
                }
                $Transaction_Code = $row['transaction_code'];
                $Interest = $row['Interest'];
                $Amount_Repayable = $row['Amount_Repayable'];
                $Amount_Repaid = $row['Amount_Repaid'];
                $Balance = $row['Balance'];
                $Disbursement_Date = $row['Disbursement_Date'];
                $Disbursement_Time = $row['Disbursement_Time'];
                $Repayment_Date = $row['Repayment_Date'];
                $LO = $row['LO'];
                $CO = $row['CO'];
                $application_mode = $row['application_mode'];
                $Status = $row['Status'];

                $total_amount_disbursed += $Amount_Disbursed;
                $total_interest += $Interest;
                $total_amount_repayable += $Amount_Repayable;
                $total_amount_repaid += $Amount_Repaid;
                $total_balance += $Balance;

                echo "<tr><td>$uid</td><td>$full_name</td><td>$Branch</td><td>$Phone</td><td>$product_name</td><td>$Amount_Disbursed</td><td>$Transaction_Code</td><td>$Interest</td><td>$Amount_Repayable</td><td>$Amount_Repaid</td><td>$Balance</td><td>$Disbursement_Date</td><td>$Disbursement_Time</td><td>$Repayment_Date</td><td>$LO</td><td>$CO</td><td>$application_mode</td><td>$Status</td></tr>";

                // push iterated customer to array
                array_push($iterated_customers_uids, $customer_id);
            }
            ?>
            </tbody>

            <tfoot>
            <tr><th colspan="5">Total</th><th><?php echo $total_amount_disbursed; ?></th><th></th><th><?php echo $total_interest; ?></th><th><?php echo $total_amount_repayable; ?></th><th><?php echo $total_amount_repaid; ?></th><th><?php echo $total_balance; ?></th><th></th><th></th><th></th><th></th><th></th></tr>
            </tfoot>
        </table>
    </div>

    </div>

<?php

