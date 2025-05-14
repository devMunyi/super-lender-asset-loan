<?php
// Initialize pair-based arrays
$pair_bal = array();
$pair_disbursed = array();
$pair_repaid = array();
$pair_loans = array();
$pair_repayable = array();

$agent_names = table_to_obj('o_users',"uid > 0","10000","uid","name");
$agent_branches = table_to_obj('o_users',"uid > 0","10000","uid","branch");
$branch_names = table_to_obj('o_branches',"uid > 0","10000","uid","name");

// Get active pairs
$pairs = table_to_obj('o_pairing', "status=1", "100000", "lo", "co");

// Process loans
$loans_monthly = fetchtable('o_loans', "disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date' $andbranch_loan", "loan_balance", "desc", "100000000", "loan_amount, total_repaid, total_repayable_amount, loan_balance, current_lo, current_co");

while($dm = mysqli_fetch_array($loans_monthly)) {
    $current_lo = $dm['current_lo'];
    $paired_co = isset($pairs[$current_lo]) ? $pairs[$current_lo] : null;
    
    if($paired_co) {
        $pair_key = $current_lo.'-'.$paired_co;
        
        // Initialize pair if not exists
        if(!isset($pair_disbursed[$pair_key])) {
            $pair_disbursed[$pair_key] = 0;
            $pair_repaid[$pair_key] = 0;
            $pair_bal[$pair_key] = 0;
            $pair_loans[$pair_key] = 0;
            $pair_repayable[$pair_key] = 0;
        }
        
        // Aggregate values
        $pair_disbursed[$pair_key] += $dm['loan_amount'];
        $pair_repaid[$pair_key] += $dm['total_repaid'];
        $pair_bal[$pair_key] += $dm['loan_balance'];
        $pair_repayable[$pair_key] += $dm['total_repayable_amount'];
        $pair_loans[$pair_key]++;
    }
}
?>

<div class="row">
    <div class="col-sm-12">
        <h4>Loan Agent Pairs</h4>
        <table id="example2" class="table table-condensed table-striped table-bordered">
            <thead>
                <tr>
                    <th>Pair</th>
                    <th>Branch</th>
                    <th>Total Disbursed</th>
                    <th>Total Repayable</th>
                    <th>Total Paid</th>
                    <th>Total Balance</th>
                    <th>Coll. Rate</th>
                    <th>Total Loans</th>
                </tr>
            </thead>
            <tbody>
                <?php
               
               $disbursed_sum = $repaid_sum = $balance_sum = $loans_sum = $repayable_sum = 0;
                foreach($pairs as $lo_id => $co_id) {
                    $pair_key = $lo_id.'-'.$co_id;
                    $branch_id = $agent_branches[$lo_id];
                    
                    $metrics = [
                        'disbursed' => $pair_disbursed[$pair_key] ?? 0,
                        'repaid' => $pair_repaid[$pair_key] ?? 0,
                        'balance' => $pair_bal[$pair_key] ?? 0,
                        'loans' => $pair_loans[$pair_key] ?? 0,
                        'repayable' => $pair_repayable[$pair_key] ?? 0
                    ];
                    
                    // Calculate collection rate
                    $cr = $metrics['repayable'] > 0 
                        ? round(($metrics['repaid'] / $metrics['repayable']) * 100, 2)
                        : 0;

                    // Update totals
                    foreach($metrics as $key => $val) {
                        $totals[$key] += $val;
                    }
                    
                    echo "<tr>
                        <td>{$agent_names[$lo_id]} - {$agent_names[$co_id]}</td>
                        <td>{$branch_names[$branch_id]}</td>
                        <td>".money($metrics['disbursed'])."</td>
                        <td>".money($metrics['repayable'])."</td>
                        <td>".money($metrics['repaid'])."</td>
                        <td>".money($metrics['balance'])."</td>
                        <td>{$cr}%</td>
                        <td>{$metrics['loans']}</td>
                    </tr>";

                    $disbursed_sum = $disbursed_sum + $metrics['disbursed'];
                    $repaid_sum = $repaid_sum + $metrics['repaid'];
                    $balance_sum = $balance_sum + $metrics['balance'];
                    $loans_sum = $loans_sum + $metrics['loans'];
                    $repayable_sum  = $repayable_sum + $metrics['repayable'];
                    
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>Total</th>
                    <th>--</th>
                    <th><?php echo money($disbursed_sum) ?></th>
                    <th><?php echo money($repayable_sum) ?></th>
                    <th><?php echo money($repaid_sum) ?></th>
                    <th><?php echo money($balance_sum) ?></th>
                    <th><?php echo round(($repayable_sum > 0 ? ($repaid_sum / $repayable_sum) * 100 : 0), 2) ?>%</th>
                    <th><?php echo $loans_sum ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
