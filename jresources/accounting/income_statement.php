<?php
/****
 *
 * This code was generated entirely by AI chartGPT-01-Preview, with minimal user input
 * It is now clear AI will replace developers SMH, but we will get there when we get there
 */


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
    // Fetch overall income
    $sql_overall_income = "
    SELECT
        SUM(total_repaid) AS total_income
    FROM o_loans
    WHERE disbursed = 1 AND status != 0
    AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
    ";
    $result_overall_income = mysqli_query($con, $sql_overall_income);
    $row_overall_income = mysqli_fetch_assoc($result_overall_income);
    $overall_totals['total_income'] = $row_overall_income['total_income'] ?? 0;

    // Fetch overall expenses (include all expenses)
    $sql_overall_expenses = "
    SELECT
        SUM(amount) AS total_expenses
    FROM o_expenses
    WHERE (
        recurring_expense = 1
        OR expense_date BETWEEN '$start_date_safe' AND '$end_date_safe'
    )
    ";
    $result_overall_expenses = mysqli_query($con, $sql_overall_expenses);
    $row_overall_expenses = mysqli_fetch_assoc($result_overall_expenses);
    $overall_totals['total_expenses'] = $row_overall_expenses['total_expenses'] ?? 0;

    // Net income
    $overall_totals['net_income'] = $overall_totals['total_income'] - $overall_totals['total_expenses'];

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

        // Income per branch
        $sql_income = "
        SELECT
            SUM(total_repaid) AS total_income
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND current_branch = $branch_id
        AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
        ";
        $result_income = mysqli_query($con, $sql_income);
        $row_income = mysqli_fetch_assoc($result_income);
        $total_income = $row_income['total_income'] ?? 0;

        // Expenses per branch
        $sql_expenses = "
        SELECT
            SUM(amount) AS total_expenses
        FROM o_expenses
        WHERE (
            recurring_expense = 1
            OR expense_date BETWEEN '$start_date_safe' AND '$end_date_safe'
        )
        AND (
            branch_id = $branch_id
            OR branch_id = 0
        )
        ";
        $result_expenses = mysqli_query($con, $sql_expenses);
        $row_expenses = mysqli_fetch_assoc($result_expenses);
        $total_expenses = $row_expenses['total_expenses'] ?? 0;

        // Net income
        $net_income = $total_income - $total_expenses;

        $totals[] = array(
            'name' => $branch['name'],
            'total_income' => $total_income,
            'total_expenses' => $total_expenses,
            'net_income' => $net_income
        );
    }

} elseif ($obj == 'PRODUCT') {
    // Fetch overall income
    $sql_overall_income = "
    SELECT
        SUM(total_repaid) AS total_income
    FROM o_loans
    WHERE disbursed = 1 AND status != 0
    AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
    ";
    $result_overall_income = mysqli_query($con, $sql_overall_income);
    $row_overall_income = mysqli_fetch_assoc($result_overall_income);
    $overall_totals['total_income'] = $row_overall_income['total_income'] ?? 0;

    // Fetch overall expenses (include all expenses)
    $sql_overall_expenses = "
    SELECT
        SUM(amount) AS total_expenses
    FROM o_expenses
    WHERE (
        recurring_expense = 1
        OR expense_date BETWEEN '$start_date_safe' AND '$end_date_safe'
    )
    ";
    $result_overall_expenses = mysqli_query($con, $sql_overall_expenses);
    $row_overall_expenses = mysqli_fetch_assoc($result_overall_expenses);
    $overall_totals['total_expenses'] = $row_overall_expenses['total_expenses'] ?? 0;

    // Net income
    $overall_totals['net_income'] = $overall_totals['total_income'] - $overall_totals['total_expenses'];

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

        // Income per product
        $sql_income = "
        SELECT
            SUM(total_repaid) AS total_income
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND product_id = $product_id
        AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
        ";
        $result_income = mysqli_query($con, $sql_income);
        $row_income = mysqli_fetch_assoc($result_income);
        $total_income = $row_income['total_income'] ?? 0;

        // Expenses per product
        $sql_expenses = "
        SELECT
            SUM(amount) AS total_expenses
        FROM o_expenses
        WHERE (
            recurring_expense = 1
            OR expense_date BETWEEN '$start_date_safe' AND '$end_date_safe'
        )
        AND (
            product_id = $product_id
            OR product_id = 0
        )
        ";
        $result_expenses = mysqli_query($con, $sql_expenses);
        $row_expenses = mysqli_fetch_assoc($result_expenses);
        $total_expenses = $row_expenses['total_expenses'] ?? 0;

        // Net income
        $net_income = $total_income - $total_expenses;

        $totals[] = array(
            'name' => $product['name'],
            'total_income' => $total_income,
            'total_expenses' => $total_expenses,
            'net_income' => $net_income
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
    $overall_totals = array(
        'total_income' => 0,
        'total_expenses' => 0,
        'net_income' => 0
    );

    // For each month, calculate totals
    $totals = array();
    foreach ($months as $month) {
        $month_start = $month . '-01';
        $month_end = date("Y-m-t", strtotime($month_start)); // Last day of the month

        // Income for the month
        $sql_income = "
        SELECT
            SUM(total_repaid) AS total_income
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND given_date BETWEEN '$month_start' AND '$month_end'
        ";
        $result_income = mysqli_query($con, $sql_income);
        $row_income = mysqli_fetch_assoc($result_income);
        $total_income = $row_income['total_income'] ?? 0;

        // Expenses for the month
        // Include recurring expenses and expenses with expense_date in the month
        $sql_expenses = "
        SELECT
            SUM(amount) AS total_expenses
        FROM o_expenses
        WHERE (
            recurring_expense = 1
            OR expense_date BETWEEN '$month_start' AND '$month_end'
        )
        ";
        $result_expenses = mysqli_query($con, $sql_expenses);
        $row_expenses = mysqli_fetch_assoc($result_expenses);
        $total_expenses = $row_expenses['total_expenses'] ?? 0;

        // Net income
        $net_income = $total_income - $total_expenses;

        $totals[] = array(
            'name' => $month,
            'total_income' => $total_income,
            'total_expenses' => $total_expenses,
            'net_income' => $net_income
        );

        // Add to overall totals
        $overall_totals['total_income'] += $total_income;
        $overall_totals['total_expenses'] += $total_expenses;
        $overall_totals['net_income'] += $net_income;
    }

} else {
    echo "<p>Invalid value for \$obj variable.</p>";
    exit();
}

// Close the database connection
mysqli_close($con);
?>

<div class="container mt-5">
    <?php if ($obj == 'BRANCH' || $obj == 'PRODUCT' || $obj == 'MONTH'): ?>
        <!-- Overall Totals Section -->
        <div class="row">
            <div class="col-12">
                <h3 class="mb-4 font-bold text-purple">Overall Income Statement</h3>
                <div class="bg-gray-light small-shadow">
                    <table class="table table-bordered font-16">
                        <thead class="thead-dark">
                        <tr>
                            <th>Total Income</th>
                            <th>Total Expenses</th>
                            <th>Net Income</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><?php echo number_format($overall_totals['total_income'], 2); ?></td>
                            <td><?php echo number_format($overall_totals['total_expenses'], 2); ?></td>
                            <td><?php echo number_format($overall_totals['net_income'], 2); ?></td>
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
                                    <th>Total Income</th>
                                    <th>Total Expenses</th>
                                    <th>Net Income</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><?php echo number_format($total['total_income'], 2); ?></td>
                                    <td><?php echo number_format($total['total_expenses'], 2); ?></td>
                                    <td><?php echo number_format($total['net_income'], 2); ?></td>
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
