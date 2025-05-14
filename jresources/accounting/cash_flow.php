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
    // Fetch overall cash flows
    // Cash Inflows: Total repayments received
    $sql_total_repaid = "
    SELECT
        SUM(total_repaid) AS total_repaid
    FROM o_loans
    WHERE disbursed = 1 AND status != 0
    AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
    ";
    $result_total_repaid = mysqli_query($con, $sql_total_repaid);
    $row_total_repaid = mysqli_fetch_assoc($result_total_repaid);
    $overall_totals['total_repaid'] = $row_total_repaid['total_repaid'] ?? 0;

    // Cash Outflows: Total expenses
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
    $overall_totals['total_expenses'] = $row_total_expenses['total_expenses'] ?? 0;

    // Net Cash Flow from Operating Activities
    $overall_totals['net_cash_flow'] = $overall_totals['total_repaid'] - $overall_totals['total_expenses'];

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
        AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
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
            OR expense_date BETWEEN '$start_date_safe' AND '$end_date_safe'
        )
        AND (branch_id = $branch_id OR branch_id = 0)
        ";
        $result_total_expenses = mysqli_query($con, $sql_total_expenses);
        $row_total_expenses = mysqli_fetch_assoc($result_total_expenses);
        $total_expenses = $row_total_expenses['total_expenses'] ?? 0;

        // Net Cash Flow from Operating Activities
        $net_cash_flow = $total_repaid - $total_expenses;

        $totals[] = array(
            'name' => $branch['name'],
            'total_repaid' => $total_repaid,
            'total_expenses' => $total_expenses,
            'net_cash_flow' => $net_cash_flow
        );
    }

} elseif ($obj == 'PRODUCT') {
    // Similar logic for products
    // Fetch overall cash flows
    // Cash Inflows: Total repayments received
    $sql_total_repaid = "
    SELECT
        SUM(total_repaid) AS total_repaid
    FROM o_loans
    WHERE disbursed = 1 AND status != 0
    AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
    ";
    $result_total_repaid = mysqli_query($con, $sql_total_repaid);
    $row_total_repaid = mysqli_fetch_assoc($result_total_repaid);
    $overall_totals['total_repaid'] = $row_total_repaid['total_repaid'] ?? 0;

    // Cash Outflows: Total expenses
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
    $overall_totals['total_expenses'] = $row_total_expenses['total_expenses'] ?? 0;

    // Net Cash Flow from Operating Activities
    $overall_totals['net_cash_flow'] = $overall_totals['total_repaid'] - $overall_totals['total_expenses'];

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
        AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
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
            OR expense_date BETWEEN '$start_date_safe' AND '$end_date_safe'
        )
        AND (product_id = $product_id OR product_id = 0)
        ";
        $result_total_expenses = mysqli_query($con, $sql_total_expenses);
        $row_total_expenses = mysqli_fetch_assoc($result_total_expenses);
        $total_expenses = $row_total_expenses['total_expenses'] ?? 0;

        // Net Cash Flow from Operating Activities
        $net_cash_flow = $total_repaid - $total_expenses;

        $totals[] = array(
            'name' => $product['name'],
            'total_repaid' => $total_repaid,
            'total_expenses' => $total_expenses,
            'net_cash_flow' => $net_cash_flow
        );
    }

} elseif ($obj == 'MONTH') {
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

    // Initialize overall totals
    $overall_totals['total_repaid'] = 0;
    $overall_totals['total_expenses'] = 0;
    $overall_totals['net_cash_flow'] = 0;

    // For each month, calculate cash flows
    $totals = array();
    foreach ($months as $month) {
        $month_start = $month . '-01';
        $month_end = date("Y-m-t", strtotime($month_start)); // Last day of the month

        // Total repayments received in the month
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

        // Total expenses in the month
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

        // Net Cash Flow from Operating Activities
        $net_cash_flow = $total_repaid - $total_expenses;

        $totals[] = array(
            'name' => $month,
            'total_repaid' => $total_repaid,
            'total_expenses' => $total_expenses,
            'net_cash_flow' => $net_cash_flow
        );

        // Add to overall totals
        $overall_totals['total_repaid'] += $total_repaid;
        $overall_totals['total_expenses'] += $total_expenses;
        $overall_totals['net_cash_flow'] += $net_cash_flow;
    }

} else {
    echo "<p>Invalid value for \$obj variable.</p>";
    exit();
}

// Close the database connection
mysqli_close($con);
?>

<!-- HTML code for displaying the cash flow statement -->
<div class="container mt-5">
    <?php if ($obj == 'BRANCH' || $obj == 'PRODUCT' || $obj == 'MONTH'): ?>
        <!-- Overall Cash Flow Statement Section -->
        <div class="row">
            <div class="col-12">
                <h3 class="mb-4 font-bold text-purple">Overall Cash Flow Statement from <?php echo htmlspecialchars($start_date_safe); ?> to <?php echo htmlspecialchars($end_date_safe); ?></h3>
                <div class="bg-gray-light small-shadow">
                    <table class="table table-bordered font-16">
                        <thead class="thead-dark">
                        <tr>
                            <th>Cash Flows from Operating Activities</th>
                            <th>Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>Cash Inflows (Loan Repayments)</td>
                            <td><?php echo number_format($overall_totals['total_repaid'], 2); ?></td>
                        </tr>
                        <tr>
                            <td>Cash Outflows (Expenses)</td>
                            <td>(<?php echo number_format($overall_totals['total_expenses'], 2); ?>)</td>
                        </tr>
                        <tr>
                            <th>Net Cash Flow from Operating Activities</th>
                            <th><?php echo number_format($overall_totals['net_cash_flow'], 2); ?></th>
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
                                    <th>Cash Flows from Operating Activities</th>
                                    <th>Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td>Cash Inflows (Loan Repayments)</td>
                                    <td><?php echo number_format($total['total_repaid'], 2); ?></td>
                                </tr>
                                <tr>
                                    <td>Cash Outflows (Expenses)</td>
                                    <td>(<?php echo number_format($total['total_expenses'], 2); ?>)</td>
                                </tr>
                                <tr>
                                    <th>Net Cash Flow from Operating Activities</th>
                                    <th><?php echo number_format($total['net_cash_flow'], 2); ?></th>
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
