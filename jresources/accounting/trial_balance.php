<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../php_functions/secondary-functions.php");
include_once ("../../configs/conn.inc");

$start_date = $_GET['start_date'] ?? first_date_of_month($date);
$end_date = $_GET['end_date'] ?? $date;
$obj = $_GET['obj'] ?? 'OVERALL';

// Sanitize and format dates to prevent SQL injection
$start_date_safe = mysqli_real_escape_string($con, $start_date);
$end_date_safe = mysqli_real_escape_string($con, $end_date);

$start_date_safe = date('Y-m-d', strtotime($start_date_safe));
$end_date_safe = date('Y-m-d', strtotime($end_date_safe));

// Initialize variables
$overall_trial_balance = [];
$grouped_trial_balances = []; // To hold trial balances per group

// Fetch overall data
// Total repayments (Cash received)
$sql_total_repaid = "
SELECT
    SUM(total_repaid) AS total_repaid
FROM o_loans
WHERE disbursed = 1 AND status != 0
AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
";
$result_total_repaid = mysqli_query($con, $sql_total_repaid);
$row_total_repaid = mysqli_fetch_assoc($result_total_repaid);
$overall_total_repaid = $row_total_repaid['total_repaid'] ?? 0;

// Total loan balances (Loans Receivable)
$sql_loans_receivable = "
SELECT
    SUM(loan_balance) AS total_loans_receivable
FROM o_loans
WHERE disbursed = 1 AND status != 0
AND loan_balance > 0
AND given_date <= '$end_date_safe'
";
$result_loans_receivable = mysqli_query($con, $sql_loans_receivable);
$row_loans_receivable = mysqli_fetch_assoc($result_loans_receivable);
$overall_loans_receivable = $row_loans_receivable['total_loans_receivable'] ?? 0;

// Total expenses
$sql_total_expenses = "
SELECT
    SUM(amount) AS total_expenses
FROM o_expenses
WHERE (
    recurring_expense = 1
    OR expense_date BETWEEN '$start_date_safe' AND '$end_date_safe'
)
";
$result_total_expenses = mysqli_query($con, $sql_total_expenses);
$row_total_expenses = mysqli_fetch_assoc($result_total_expenses);
$overall_total_expenses = $row_total_expenses['total_expenses'] ?? 0;

// Total loans disbursed
$sql_total_disbursed = "
SELECT
    SUM(amount) AS total_disbursed
FROM o_loans
WHERE disbursed = 1 AND status != 0
AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
";
$result_total_disbursed = mysqli_query($con, $sql_total_disbursed);
$row_total_disbursed = mysqli_fetch_assoc($result_total_disbursed);
$overall_total_disbursed = $row_total_disbursed['total_disbursed'] ?? 0;

// Estimate Interest Income
$overall_interest_income = $overall_total_repaid - $overall_total_disbursed;

// Prepare Overall Trial Balance data
$overall_trial_balance = [
    // Assets
    [
        'account_name' => 'Cash',
        'debit' => $overall_total_repaid - $overall_total_expenses,
        'credit' => 0
    ],
    [
        'account_name' => 'Loans Receivable',
        'debit' => $overall_loans_receivable,
        'credit' => 0
    ],
    // Income
    [
        'account_name' => 'Interest Income',
        'debit' => 0,
        'credit' => $overall_interest_income
    ],
    // Expenses
    [
        'account_name' => 'Operating Expenses',
        'debit' => $overall_total_expenses,
        'credit' => 0
    ],
];

// Calculate Equity for Overall Trial Balance
$overall_total_debits = 0;
$overall_total_credits = 0;
foreach ($overall_trial_balance as $entry) {
    $overall_total_debits += $entry['debit'];
    $overall_total_credits += $entry['credit'];
}

$overall_equity = $overall_total_credits - $overall_total_debits;
$overall_trial_balance[] = [
    'account_name' => 'Equity',
    'debit' => 0,
    'credit' => $overall_equity
];

// Update totals after adding Equity
$overall_total_debits += 0;
$overall_total_credits += $overall_equity;

// Logic based on $obj
if ($obj == 'BRANCH') {
    // Fetch branches
    $sql_branches = "SELECT uid, name FROM o_branches WHERE status = 1";
    $result_branches = mysqli_query($con, $sql_branches);
    $branches = [];
    while ($row = mysqli_fetch_assoc($result_branches)) {
        $branches[] = $row;
    }

    foreach ($branches as $branch) {
        $branch_id = $branch['uid'];
        $branch_name = $branch['name'];

        // Initialize trial balance for this branch
        $trial_balance = [];

        // Total repayments for branch
        $sql_total_repaid = "
        SELECT
            SUM(total_repaid) AS total_repaid
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND current_branch = $branch_id
        AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
        ";
        $result_total_repaid = mysqli_query($con, $sql_total_repaid);
        $row_total_repaid = mysqli_fetch_assoc($result_total_repaid);
        $total_repaid = $row_total_repaid['total_repaid'] ?? 0;

        // Total loan balances for branch
        $sql_loans_receivable = "
        SELECT
            SUM(loan_balance) AS total_loans_receivable
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND loan_balance > 0
        AND current_branch = $branch_id
        AND given_date <= '$end_date_safe'
        ";
        $result_loans_receivable = mysqli_query($con, $sql_loans_receivable);
        $row_loans_receivable = mysqli_fetch_assoc($result_loans_receivable);
        $loans_receivable = $row_loans_receivable['total_loans_receivable'] ?? 0;

        // Total expenses for branch
        $sql_total_expenses = "
        SELECT
            SUM(amount) AS total_expenses
        FROM o_expenses
        WHERE (
            recurring_expense = 1
            OR expense_date BETWEEN '$start_date_safe' AND '$end_date_safe'
        )
        AND (branch_id = $branch_id OR branch_id = 0)
        ";
        $result_total_expenses = mysqli_query($con, $sql_total_expenses);
        $row_total_expenses = mysqli_fetch_assoc($result_total_expenses);
        $total_expenses = $row_total_expenses['total_expenses'] ?? 0;

        // Total loans disbursed for branch
        $sql_total_disbursed = "
        SELECT
            SUM(amount) AS total_disbursed
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND current_branch = $branch_id
        AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
        ";
        $result_total_disbursed = mysqli_query($con, $sql_total_disbursed);
        $row_total_disbursed = mysqli_fetch_assoc($result_total_disbursed);
        $total_disbursed = $row_total_disbursed['total_disbursed'] ?? 0;

        // Estimate Interest Income for branch
        $interest_income = $total_repaid - $total_disbursed;

        // Prepare Trial Balance data for branch
        $trial_balance = [
            // Assets
            [
                'account_name' => 'Cash',
                'debit' => $total_repaid - $total_expenses,
                'credit' => 0
            ],
            [
                'account_name' => 'Loans Receivable',
                'debit' => $loans_receivable,
                'credit' => 0
            ],
            // Income
            [
                'account_name' => 'Interest Income',
                'debit' => 0,
                'credit' => $interest_income
            ],
            // Expenses
            [
                'account_name' => 'Operating Expenses',
                'debit' => $total_expenses,
                'credit' => 0
            ],
        ];

        // Calculate Equity for branch Trial Balance
        $total_debits = 0;
        $total_credits = 0;
        foreach ($trial_balance as $entry) {
            $total_debits += $entry['debit'];
            $total_credits += $entry['credit'];
        }

        $equity = $total_credits - $total_debits;
        $trial_balance[] = [
            'account_name' => 'Equity',
            'debit' => 0,
            'credit' => $equity
        ];

        // Store trial balance for this branch
        $grouped_trial_balances[] = [
            'name' => $branch_name,
            'trial_balance' => $trial_balance,
            'total_debits' => $total_debits,
            'total_credits' => $total_credits + $equity
        ];
    }
} elseif ($obj == 'PRODUCT') {
    // Fetch products
    $sql_products = "SELECT uid, name FROM o_loan_products WHERE status = 1";
    $result_products = mysqli_query($con, $sql_products);
    $products = [];
    while ($row = mysqli_fetch_assoc($result_products)) {
        $products[] = $row;
    }

    foreach ($products as $product) {
        $product_id = $product['uid'];
        $product_name = $product['name'];

        // Initialize trial balance for this product
        $trial_balance = [];

        // Total repayments for product
        $sql_total_repaid = "
        SELECT
            SUM(total_repaid) AS total_repaid
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND product_id = $product_id
        AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
        ";
        $result_total_repaid = mysqli_query($con, $sql_total_repaid);
        $row_total_repaid = mysqli_fetch_assoc($result_total_repaid);
        $total_repaid = $row_total_repaid['total_repaid'] ?? 0;

        // Total loan balances for product
        $sql_loans_receivable = "
        SELECT
            SUM(loan_balance) AS total_loans_receivable
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND loan_balance > 0
        AND product_id = $product_id
        AND given_date <= '$end_date_safe'
        ";
        $result_loans_receivable = mysqli_query($con, $sql_loans_receivable);
        $row_loans_receivable = mysqli_fetch_assoc($result_loans_receivable);
        $loans_receivable = $row_loans_receivable['total_loans_receivable'] ?? 0;

        // Total expenses for product
        $sql_total_expenses = "
        SELECT
            SUM(amount) AS total_expenses
        FROM o_expenses
        WHERE (
            recurring_expense = 1
            OR expense_date BETWEEN '$start_date_safe' AND '$end_date_safe'
        )
        AND (product_id = $product_id OR product_id = 0)
        ";
        $result_total_expenses = mysqli_query($con, $sql_total_expenses);
        $row_total_expenses = mysqli_fetch_assoc($result_total_expenses);
        $total_expenses = $row_total_expenses['total_expenses'] ?? 0;

        // Total loans disbursed for product
        $sql_total_disbursed = "
        SELECT
            SUM(amount) AS total_disbursed
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND product_id = $product_id
        AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
        ";
        $result_total_disbursed = mysqli_query($con, $sql_total_disbursed);
        $row_total_disbursed = mysqli_fetch_assoc($result_total_disbursed);
        $total_disbursed = $row_total_disbursed['total_disbursed'] ?? 0;

        // Estimate Interest Income for product
        $interest_income = $total_repaid - $total_disbursed;

        // Prepare Trial Balance data for product
        $trial_balance = [
            // Assets
            [
                'account_name' => 'Cash',
                'debit' => $total_repaid - $total_expenses,
                'credit' => 0
            ],
            [
                'account_name' => 'Loans Receivable',
                'debit' => $loans_receivable,
                'credit' => 0
            ],
            // Income
            [
                'account_name' => 'Interest Income',
                'debit' => 0,
                'credit' => $interest_income
            ],
            // Expenses
            [
                'account_name' => 'Operating Expenses',
                'debit' => $total_expenses,
                'credit' => 0
            ],
        ];

        // Calculate Equity for product Trial Balance
        $total_debits = 0;
        $total_credits = 0;
        foreach ($trial_balance as $entry) {
            $total_debits += $entry['debit'];
            $total_credits += $entry['credit'];
        }

        $equity = $total_credits - $total_debits;
        $trial_balance[] = [
            'account_name' => 'Equity',
            'debit' => 0,
            'credit' => $equity
        ];

        // Store trial balance for this product
        $grouped_trial_balances[] = [
            'name' => $product_name,
            'trial_balance' => $trial_balance,
            'total_debits' => $total_debits,
            'total_credits' => $total_credits + $equity
        ];
    }
} elseif ($obj == 'MONTH') {
    // Generate array of months between $start_date and $end_date
    $start    = new DateTime($start_date_safe);
    $start->modify('first day of this month');
    $end      = new DateTime($end_date_safe);
    $end->modify('first day of next month');

    $interval = DateInterval::createFromDateString('1 month');
    $period   = new DatePeriod($start, $interval, $end);

    $months = [];
    foreach ($period as $dt) {
        $months[] = $dt->format("Y-m");
    }

    foreach ($months as $month) {
        $month_start = $month . '-01';
        $month_end = date("Y-m-t", strtotime($month_start));

        // Initialize trial balance for this month
        $trial_balance = [];

        // Total repayments for month
        $sql_total_repaid = "
        SELECT
            SUM(total_repaid) AS total_repaid
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND given_date BETWEEN '$month_start' AND '$month_end'
        ";
        $result_total_repaid = mysqli_query($con, $sql_total_repaid);
        $row_total_repaid = mysqli_fetch_assoc($result_total_repaid);
        $total_repaid = $row_total_repaid['total_repaid'] ?? 0;

        // Total loan balances as of month end
        $sql_loans_receivable = "
        SELECT
            SUM(loan_balance) AS total_loans_receivable
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND loan_balance > 0
        AND given_date <= '$month_end'
        ";
        $result_loans_receivable = mysqli_query($con, $sql_loans_receivable);
        $row_loans_receivable = mysqli_fetch_assoc($result_loans_receivable);
        $loans_receivable = $row_loans_receivable['total_loans_receivable'] ?? 0;

        // Total expenses for month
        $sql_total_expenses = "
        SELECT
            SUM(amount) AS total_expenses
        FROM o_expenses
        WHERE (
            recurring_expense = 1
            OR expense_date BETWEEN '$month_start' AND '$month_end'
        )
        ";
        $result_total_expenses = mysqli_query($con, $sql_total_expenses);
        $row_total_expenses = mysqli_fetch_assoc($result_total_expenses);
        $total_expenses = $row_total_expenses['total_expenses'] ?? 0;

        // Total loans disbursed for month
        $sql_total_disbursed = "
        SELECT
            SUM(amount) AS total_disbursed
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND given_date BETWEEN '$month_start' AND '$month_end'
        ";
        $result_total_disbursed = mysqli_query($con, $sql_total_disbursed);
        $row_total_disbursed = mysqli_fetch_assoc($result_total_disbursed);
        $total_disbursed = $row_total_disbursed['total_disbursed'] ?? 0;

        // Estimate Interest Income for month
        $interest_income = $total_repaid - $total_disbursed;

        // Prepare Trial Balance data for month
        $trial_balance = [
            // Assets
            [
                'account_name' => 'Cash',
                'debit' => $total_repaid - $total_expenses,
                'credit' => 0
            ],
            [
                'account_name' => 'Loans Receivable',
                'debit' => $loans_receivable,
                'credit' => 0
            ],
            // Income
            [
                'account_name' => 'Interest Income',
                'debit' => 0,
                'credit' => $interest_income
            ],
            // Expenses
            [
                'account_name' => 'Operating Expenses',
                'debit' => $total_expenses,
                'credit' => 0
            ],
        ];

        // Calculate Equity for month Trial Balance
        $total_debits = 0;
        $total_credits = 0;
        foreach ($trial_balance as $entry) {
            $total_debits += $entry['debit'];
            $total_credits += $entry['credit'];
        }

        $equity = $total_credits - $total_debits;
        $trial_balance[] = [
            'account_name' => 'Equity',
            'debit' => 0,
            'credit' => $equity
        ];

        // Store trial balance for this month
        $grouped_trial_balances[] = [
            'name' => $month,
            'trial_balance' => $trial_balance,
            'total_debits' => $total_debits,
            'total_credits' => $total_credits + $equity
        ];
    }
} else {
    echo "<p>Invalid value for \$obj variable.</p>";
    exit();
}

// Close the database connection
mysqli_close($con);
?>

<!-- HTML code for displaying the Trial Balance -->
<div class="container mt-5">
    <!-- Overall Trial Balance -->
    <div class="row">
        <div class="col-12">
            <h3 class="mb-4 font-bold text-purple">Overall Trial Balance as of <?php echo htmlspecialchars($end_date_safe); ?></h3>
            <div class="bg-gray-light small-shadow">
                <table class="table table-bordered font-16">
                    <thead class="thead-dark">
                    <tr>
                        <th>Account Name</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($overall_trial_balance as $entry): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($entry['account_name']); ?></td>
                            <td><?php echo number_format($entry['debit'], 2); ?></td>
                            <td><?php echo number_format($entry['credit'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                    <tr>
                        <th>Total</th>
                        <th><?php echo number_format($overall_total_debits, 2); ?></th>
                        <th><?php echo number_format($overall_total_credits, 2); ?></th>
                    </tr>
                    </tfoot>
                </table>
            </div>
            <hr/>
        </div>
    </div>

    <!-- Grouped Trial Balances -->
    <div class="row">
        <?php foreach ($grouped_trial_balances as $group): ?>
            <div class="col-md-6">
                <h4 class="mb-4 font-bold text-purple">
                    <?php
                    if ($obj == 'BRANCH') {
                        echo 'Branch: ' . htmlspecialchars($group['name']);
                    } elseif ($obj == 'PRODUCT') {
                        echo 'Product: ' . htmlspecialchars($group['name']);
                    } elseif ($obj == 'MONTH') {
                        echo 'Month: ' . htmlspecialchars($group['name']);
                    }
                    ?>
                </h4>
                <div class="bg-gray-light small-shadow mb-4">
                    <table class="table table-bordered table-striped table-hover font-14">
                        <thead class="thead-dark">
                        <tr>
                            <th>Account Name</th>
                            <th>Debit</th>
                            <th>Credit</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($group['trial_balance'] as $entry): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($entry['account_name']); ?></td>
                                <td><?php echo number_format($entry['debit'], 2); ?></td>
                                <td><?php echo number_format($entry['credit'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                        <tr>
                            <th>Total</th>
                            <th><?php echo number_format($group['total_debits'], 2); ?></th>
                            <th><?php echo number_format($group['total_credits'], 2); ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
