<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../php_functions/secondary-functions.php");
include_once ("../../configs/conn.inc");

$start_date = $_GET['start_date'] ?? first_date_of_month($date);
$end_date = $_GET['end_date'] ?? $date;
$obj = $_GET['obj'] ?? 'BRANCH';

// Sanitize and format dates to prevent SQL injection
$start_date_safe = mysqli_real_escape_string($con, $start_date);
$end_date_safe = mysqli_real_escape_string($con, $end_date);

$start_date_safe = date('Y-m-d', strtotime($start_date_safe));
$end_date_safe = date('Y-m-d', strtotime($end_date_safe));

// Initialize variables
$overall_totals = array();
$totals = array();

// Logic based on $obj
if ($obj == 'BRANCH') {
    // Fetch overall assets
    // 1. Cash (Total repayments - Total expenses)
    // Total repayments received
    $sql_total_repaid = "
    SELECT
        SUM(total_repaid) AS total_repaid
    FROM o_loans
    WHERE disbursed = 1 AND status != 0
    AND given_date <= '$end_date_safe'
    ";
    $result_total_repaid = mysqli_query($con, $sql_total_repaid);
    $row_total_repaid = mysqli_fetch_assoc($result_total_repaid);
    $overall_totals['total_repaid'] = $row_total_repaid['total_repaid'] ?? 0;

    // Total expenses
    $sql_total_expenses = "
    SELECT
        SUM(amount) AS total_expenses
    FROM o_expenses
    WHERE (
        recurring_expense = 1
        OR expense_date <= '$end_date_safe'
    )
    ";
    $result_total_expenses = mysqli_query($con, $sql_total_expenses);
    $row_total_expenses = mysqli_fetch_assoc($result_total_expenses);
    $overall_totals['total_expenses'] = $row_total_expenses['total_expenses'] ?? 0;

    // Cash
    $overall_totals['cash'] = $overall_totals['total_repaid'] - $overall_totals['total_expenses'];

    // 2. Loans Receivable (Accounts Receivable)
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
    $overall_totals['loans_receivable'] = $row_loans_receivable['total_loans_receivable'] ?? 0;

    // Total Assets
    $overall_totals['total_assets'] = $overall_totals['cash'] + $overall_totals['loans_receivable'];

    // Liabilities (assuming zero)
    $overall_totals['total_liabilities'] = 0;

    // Equity
    $overall_totals['total_equity'] = $overall_totals['total_assets'] - $overall_totals['total_liabilities'];

    // Fetch branch totals
    // Get list of branches
    $sql_branches = "SELECT uid, name FROM o_branches";
    $result_branches = mysqli_query($con, $sql_branches);
    $branches = array();
    while ($row = mysqli_fetch_assoc($result_branches)) {
        $branches[] = $row;
    }

    foreach ($branches as $branch) {
        $branch_id = $branch['uid'];

        // Total repayments received for branch
        $sql_total_repaid = "
        SELECT
            SUM(total_repaid) AS total_repaid
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND current_branch = $branch_id
        AND given_date <= '$end_date_safe'
        ";
        $result_total_repaid = mysqli_query($con, $sql_total_repaid);
        $row_total_repaid = mysqli_fetch_assoc($result_total_repaid);
        $total_repaid = $row_total_repaid['total_repaid'] ?? 0;

        // Total expenses for branch
        $sql_total_expenses = "
        SELECT
            SUM(amount) AS total_expenses
        FROM o_expenses
        WHERE (
            recurring_expense = 1
            OR expense_date <= '$end_date_safe'
        )
        AND (branch_id = $branch_id OR branch_id = 0)
        ";
        $result_total_expenses = mysqli_query($con, $sql_total_expenses);
        $row_total_expenses = mysqli_fetch_assoc($result_total_expenses);
        $total_expenses = $row_total_expenses['total_expenses'] ?? 0;

        // Cash for branch
        $cash = $total_repaid - $total_expenses;

        // Loans Receivable for branch
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

        // Total Assets
        $total_assets = $cash + $loans_receivable;

        // Liabilities (assuming zero)
        $total_liabilities = 0;

        // Equity
        $total_equity = $total_assets - $total_liabilities;

        $totals[] = array(
            'name' => $branch['name'],
            'cash' => $cash,
            'loans_receivable' => $loans_receivable,
            'total_assets' => $total_assets,
            'total_liabilities' => $total_liabilities,
            'total_equity' => $total_equity
        );
    }

} elseif ($obj == 'PRODUCT') {
    // Similar logic for products
    // Fetch overall assets
    // 1. Cash (Total repayments - Total expenses)
    // Total repayments received
    $sql_total_repaid = "
    SELECT
        SUM(total_repaid) AS total_repaid
    FROM o_loans
    WHERE disbursed = 1 AND status != 0
    AND given_date <= '$end_date_safe'
    ";
    $result_total_repaid = mysqli_query($con, $sql_total_repaid);
    $row_total_repaid = mysqli_fetch_assoc($result_total_repaid);
    $overall_totals['total_repaid'] = $row_total_repaid['total_repaid'] ?? 0;

    // Total expenses
    $sql_total_expenses = "
    SELECT
        SUM(amount) AS total_expenses
    FROM o_expenses
    WHERE (
        recurring_expense = 1
        OR expense_date <= '$end_date_safe'
    )
    ";
    $result_total_expenses = mysqli_query($con, $sql_total_expenses);
    $row_total_expenses = mysqli_fetch_assoc($result_total_expenses);
    $overall_totals['total_expenses'] = $row_total_expenses['total_expenses'] ?? 0;

    // Cash
    $overall_totals['cash'] = $overall_totals['total_repaid'] - $overall_totals['total_expenses'];

    // 2. Loans Receivable
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
    $overall_totals['loans_receivable'] = $row_loans_receivable['total_loans_receivable'] ?? 0;

    // Total Assets
    $overall_totals['total_assets'] = $overall_totals['cash'] + $overall_totals['loans_receivable'];

    // Liabilities (assuming zero)
    $overall_totals['total_liabilities'] = 0;

    // Equity
    $overall_totals['total_equity'] = $overall_totals['total_assets'] - $overall_totals['total_liabilities'];

    // Fetch product totals
    // Get list of products
    $sql_products = "SELECT uid, name FROM o_loan_products";
    $result_products = mysqli_query($con, $sql_products);
    $products = array();
    while ($row = mysqli_fetch_assoc($result_products)) {
        $products[] = $row;
    }

    foreach ($products as $product) {
        $product_id = $product['uid'];

        // Total repayments received for product
        $sql_total_repaid = "
        SELECT
            SUM(total_repaid) AS total_repaid
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND product_id = $product_id
        AND given_date <= '$end_date_safe'
        ";
        $result_total_repaid = mysqli_query($con, $sql_total_repaid);
        $row_total_repaid = mysqli_fetch_assoc($result_total_repaid);
        $total_repaid = $row_total_repaid['total_repaid'] ?? 0;

        // Total expenses for product
        $sql_total_expenses = "
        SELECT
            SUM(amount) AS total_expenses
        FROM o_expenses
        WHERE (
            recurring_expense = 1
            OR expense_date <= '$end_date_safe'
        )
        AND (product_id = $product_id OR product_id = 0)
        ";
        $result_total_expenses = mysqli_query($con, $sql_total_expenses);
        $row_total_expenses = mysqli_fetch_assoc($result_total_expenses);
        $total_expenses = $row_total_expenses['total_expenses'] ?? 0;

        // Cash for product
        $cash = $total_repaid - $total_expenses;

        // Loans Receivable for product
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

        // Total Assets
        $total_assets = $cash + $loans_receivable;

        // Liabilities (assuming zero)
        $total_liabilities = 0;

        // Equity
        $total_equity = $total_assets - $total_liabilities;

        $totals[] = array(
            'name' => $product['name'],
            'cash' => $cash,
            'loans_receivable' => $loans_receivable,
            'total_assets' => $total_assets,
            'total_liabilities' => $total_liabilities,
            'total_equity' => $total_equity
        );
    }

} elseif ($obj == 'MONTH') {
    // For balance sheet, we use snapshot at month-end dates
    // Generate array of months between $start_date and $end_date
    $start    = new DateTime($start_date_safe);
    $start->modify('first day of this month');
    $end      = new DateTime($end_date_safe);
    $end->modify('first day of next month');

    $interval = DateInterval::createFromDateString('1 month');
    $period   = new DatePeriod($start, $interval, $end);

    $months = array();
    foreach ($period as $dt) {
        $months[] = $dt->format("Y-m");
    }

    // Overall totals as of end date
    // Total repayments received
    $sql_total_repaid = "
    SELECT
        SUM(total_repaid) AS total_repaid
    FROM o_loans
    WHERE disbursed = 1 AND status != 0
    AND given_date <= '$end_date_safe'
    ";
    $result_total_repaid = mysqli_query($con, $sql_total_repaid);
    $row_total_repaid = mysqli_fetch_assoc($result_total_repaid);
    $overall_totals['total_repaid'] = $row_total_repaid['total_repaid'] ?? 0;

    // Total expenses
    $sql_total_expenses = "
    SELECT
        SUM(amount) AS total_expenses
    FROM o_expenses
    WHERE (
        recurring_expense = 1
        OR expense_date <= '$end_date_safe'
    )
    ";
    $result_total_expenses = mysqli_query($con, $sql_total_expenses);
    $row_total_expenses = mysqli_fetch_assoc($result_total_expenses);
    $overall_totals['total_expenses'] = $row_total_expenses['total_expenses'] ?? 0;

    // Cash
    $overall_totals['cash'] = $overall_totals['total_repaid'] - $overall_totals['total_expenses'];

    // Loans Receivable
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
    $overall_totals['loans_receivable'] = $row_loans_receivable['total_loans_receivable'] ?? 0;

    // Total Assets
    $overall_totals['total_assets'] = $overall_totals['cash'] + $overall_totals['loans_receivable'];

    // Liabilities (assuming zero)
    $overall_totals['total_liabilities'] = 0;

    // Equity
    $overall_totals['total_equity'] = $overall_totals['total_assets'] - $overall_totals['total_liabilities'];

    // For each month, calculate snapshot at month end
    $totals = array();
    foreach ($months as $month) {
        $month_end = date("Y-m-t", strtotime($month . '-01'));

        // Total repayments received up to month end
        $sql_total_repaid = "
        SELECT
            SUM(total_repaid) AS total_repaid
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND given_date <= '$month_end'
        ";
        $result_total_repaid = mysqli_query($con, $sql_total_repaid);
        $row_total_repaid = mysqli_fetch_assoc($result_total_repaid);
        $total_repaid = $row_total_repaid['total_repaid'] ?? 0;

        // Total expenses up to month end
        $sql_total_expenses = "
        SELECT
            SUM(amount) AS total_expenses
        FROM o_expenses
        WHERE (
            recurring_expense = 1
            OR expense_date <= '$month_end'
        )
        ";
        $result_total_expenses = mysqli_query($con, $sql_total_expenses);
        $row_total_expenses = mysqli_fetch_assoc($result_total_expenses);
        $total_expenses = $row_total_expenses['total_expenses'] ?? 0;

        // Cash
        $cash = $total_repaid - $total_expenses;

        // Loans Receivable up to month end
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

        // Total Assets
        $total_assets = $cash + $loans_receivable;

        // Liabilities (assuming zero)
        $total_liabilities = 0;

        // Equity
        $total_equity = $total_assets - $total_liabilities;

        $totals[] = array(
            'name' => $month,
            'cash' => $cash,
            'loans_receivable' => $loans_receivable,
            'total_assets' => $total_assets,
            'total_liabilities' => $total_liabilities,
            'total_equity' => $total_equity
        );
    }

} else {
    echo "<p>Invalid value for \$obj variable.</p>";
    exit();
}

// Close the database connection
mysqli_close($con);
?>

<!-- HTML code for displaying the balance sheet -->
<div class="container mt-5">
    <?php if ($obj == 'BRANCH' || $obj == 'PRODUCT' || $obj == 'MONTH'): ?>
        <!-- Overall Balance Sheet Section -->
        <div class="row">
            <div class="col-12">
                <h3 class="mb-4 font-bold text-purple">Overall Balance Sheet as of <?php echo htmlspecialchars($end_date_safe); ?></h3>
                <div class="bg-gray-light small-shadow">
                    <table class="table table-bordered font-16">
                        <thead class="thead-dark">
                        <tr>
                            <th>Assets</th>
                            <th>Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Cash</td>
                            <td><?php echo number_format($overall_totals['cash'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Loans Receivable</td>
                            <td><?php echo number_format($overall_totals['loans_receivable'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Total Assets</th>
                            <th><?php echo number_format($overall_totals['total_assets'], 2); ?></th>
                        </tr>
                        </tbody>
                    </table>
                    <table class="table table-bordered font-16 mt-4">
                        <thead class="thead-dark">
                        <tr>
                            <th>Liabilities and Equity</th>
                            <th>Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Total Liabilities</td>
                            <td><?php echo number_format($overall_totals['total_liabilities'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Total Equity</td>
                            <td><?php echo number_format($overall_totals['total_equity'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Total Liabilities and Equity</th>
                            <th><?php echo number_format($overall_totals['total_liabilities'] + $overall_totals['total_equity'], 2); ?></th>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <hr/>
            </div>
        </div>

        <!-- Totals Section -->
        <div class="row">
            <?php if (!empty($totals)): ?>
                <?php foreach ($totals as $total): ?>
                    <div class="col-md-6">
                        <?php
                        if ($obj == 'BRANCH') {
                            $title = 'Branch: ' . htmlspecialchars($total['name']);
                        } elseif ($obj == 'PRODUCT') {
                            $title = 'Product: ' . htmlspecialchars($total['name']);
                        } elseif ($obj == 'MONTH') {
                            $title = 'Month: ' . htmlspecialchars($total['name']);
                        }
                        ?>
                        <h4 class="mb-4 font-bold text-purple"><?php echo $title; ?></h4>
                        <div class="small-shadow mb-4">

                            <table class="table table-bordered">
                                <thead class="thead-dark">
                                <tr>
                                    <th>Assets</th>
                                    <th>Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Cash</td>
                                    <td><?php echo number_format($total['cash'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Loans Receivable</td>
                                    <td><?php echo number_format($total['loans_receivable'], 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Total Assets</th>
                                    <th><?php echo number_format($total['total_assets'], 2); ?></th>
                                </tr>
                                </tbody>
                            </table>
                            <table class="table table-bordered mt-4">
                                <thead class="thead-dark">
                                <tr>
                                    <th>Liabilities and Equity</th>
                                    <th>Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Total Liabilities</td>
                                    <td><?php echo number_format($total['total_liabilities'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Total Equity</td>
                                    <td><?php echo number_format($total['total_equity'], 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Total Liabilities and Equity</th>
                                    <th><?php echo number_format($total['total_liabilities'] + $total['total_equity'], 2); ?></th>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No data found.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
