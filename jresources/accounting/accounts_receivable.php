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
    // Fetch overall accounts receivable
    $sql_overall_ar = "
    SELECT
        SUM(loan_balance) AS total_ar
    FROM o_loans
    WHERE disbursed = 1 AND status != 0
    AND loan_balance > 0
    AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
    ";
    $result_overall_ar = mysqli_query($con, $sql_overall_ar);
    $row_overall_ar = mysqli_fetch_assoc($result_overall_ar);
    $overall_totals['total_ar'] = $row_overall_ar['total_ar'] ?? 0;

    // Fetch branch totals
    $sql_branch_totals = "
    SELECT
        b.name AS name,
        SUM(l.loan_balance) AS total_ar
    FROM o_loans l
    JOIN o_branches b ON l.current_branch = b.uid
    WHERE l.disbursed = 1 AND l.status != 0
    AND l.loan_balance > 0
    AND l.given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
    GROUP BY l.current_branch
    ";
    $result_totals = mysqli_query($con, $sql_branch_totals);

    // Store totals
    $totals = array();
    if (mysqli_num_rows($result_totals) > 0) {
        while ($row = mysqli_fetch_assoc($result_totals)) {
            $totals[] = $row;
        }
    } else {
        echo "<p>No branch totals found.</p>";
    }

} elseif ($obj == 'PRODUCT') {
    // Fetch overall accounts receivable
    $sql_overall_ar = "
    SELECT
        SUM(loan_balance) AS total_ar
    FROM o_loans
    WHERE disbursed = 1 AND status != 0
    AND loan_balance > 0
    AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
    ";
    $result_overall_ar = mysqli_query($con, $sql_overall_ar);
    $row_overall_ar = mysqli_fetch_assoc($result_overall_ar);
    $overall_totals['total_ar'] = $row_overall_ar['total_ar'] ?? 0;

    // Fetch product totals
    $sql_product_totals = "
    SELECT
        p.name AS name,
        SUM(l.loan_balance) AS total_ar
    FROM o_loans l
    JOIN o_loan_products p ON l.product_id = p.uid
    WHERE l.disbursed = 1 AND l.status != 0
    AND l.loan_balance > 0
    AND l.given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
    GROUP BY l.product_id
    ";
    $result_totals = mysqli_query($con, $sql_product_totals);

    // Store totals
    $totals = array();
    if (mysqli_num_rows($result_totals) > 0) {
        while ($row = mysqli_fetch_assoc($result_totals)) {
            $totals[] = $row;
        }
    } else {
        echo "<p>No product totals found.</p>";
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

    // Initialize overall total accounts receivable
    $overall_totals['total_ar'] = 0;

    // For each month, calculate accounts receivable
    $totals = array();
    foreach ($months as $month) {
        $month_start = $month . '-01';
        $month_end = date("Y-m-t", strtotime($month_start)); // Last day of the month

        // Fetch accounts receivable for the month
        $sql_ar = "
        SELECT
            SUM(loan_balance) AS total_ar
        FROM o_loans
        WHERE disbursed = 1 AND status != 0
        AND loan_balance > 0
        AND given_date BETWEEN '$month_start' AND '$month_end'
        AND given_date BETWEEN '$start_date_safe' AND '$end_date_safe'
        ";
        $result_ar = mysqli_query($con, $sql_ar);
        $row_ar = mysqli_fetch_assoc($result_ar);

        $total_ar = $row_ar['total_ar'] ?? 0;

        $totals[] = array(
            'name' => $month,
            'total_ar' => $total_ar
        );

        // Add to overall total accounts receivable
        $overall_totals['total_ar'] += $total_ar;
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
        <!-- Overall Accounts Receivable Totals Section -->
        <div class="row">
            <div class="col-12">
                <h3 class="mb-4 font-bold text-purple">Overall Accounts Receivable</h3>
                <div class="bg-gray-light small-shadow">
                    <table class="table table-bordered font-16">
                        <thead class="thead-dark">
                        <tr>
                            <th>Total Accounts Receivable</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><?php echo number_format($overall_totals['total_ar'], 2); ?></td>
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
                                    <th>Total Accounts Receivable</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr>
                                    <td><?php echo number_format($total['total_ar'], 2); ?></td>
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
