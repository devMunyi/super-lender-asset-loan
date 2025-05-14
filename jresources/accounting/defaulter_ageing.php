<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../php_functions/secondary-functions.php");
include_once ("../../configs/conn.inc");

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$obj = $_GET['obj'] ?? 'OVERALL';

// Sanitize and format dates to prevent SQL injection
$start_date_safe = mysqli_real_escape_string($con, $start_date);
$end_date_safe = mysqli_real_escape_string($con, $end_date);

$start_date_safe = date('Y-m-d', strtotime($start_date_safe));
$end_date_safe = date('Y-m-d', strtotime($end_date_safe));

// Initialize variables
$overall_ageing_buckets = [
    '1-30' => ['count' => 0, 'amount' => 0],
    '31-60' => ['count' => 0, 'amount' => 0],
    '61-90' => ['count' => 0, 'amount' => 0],
    '91-180' => ['count' => 0, 'amount' => 0],
    'Over 180' => ['count' => 0, 'amount' => 0]
];

$grouped_ageing_reports = []; // To hold ageing reports per group

// Today's date or a specified date
$today = date('Y-m-d');

// Fetch overdue loans (status = 7) for the overall report
$sql_overdue_loans = "
SELECT
    uid,
    loan_balance,
    final_due_date,
    current_branch,
    product_id,
    DATEDIFF('$today', final_due_date) AS days_overdue
FROM o_loans
WHERE disbursed = 1
AND status = 7
";

// Fetch overdue loans
$result_overdue_loans = mysqli_query($con, $sql_overdue_loans);

if (!$result_overdue_loans) {
    die('Invalid query: ' . mysqli_error($con));
}

if (mysqli_num_rows($result_overdue_loans) > 0) {
    while ($loan = mysqli_fetch_assoc($result_overdue_loans)) {
        $days_overdue = $loan['days_overdue'];
        $loan_balance = $loan['loan_balance'];

        // Determine ageing bucket
        if ($days_overdue >= 1 && $days_overdue <= 30) {
            $bucket = '1-30';
        } elseif ($days_overdue >= 31 && $days_overdue <= 60) {
            $bucket = '31-60';
        } elseif ($days_overdue >= 61 && $days_overdue <= 90) {
            $bucket = '61-90';
        } elseif ($days_overdue >= 91 && $days_overdue <= 180) {
            $bucket = '91-180';
        } else {
            $bucket = 'Over 180';
        }

        // Increment counts and amounts for overall report
        $overall_ageing_buckets[$bucket]['count'] += 1;
        $overall_ageing_buckets[$bucket]['amount'] += $loan_balance;
    }
} else {
    echo "<p>No overdue loans found.</p>";
}

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

        // Initialize ageing buckets for this branch
        $ageing_buckets = [
            '1-30' => ['count' => 0, 'amount' => 0],
            '31-60' => ['count' => 0, 'amount' => 0],
            '61-90' => ['count' => 0, 'amount' => 0],
            '91-180' => ['count' => 0, 'amount' => 0],
            'Over 180' => ['count' => 0, 'amount' => 0]
        ];

        // Fetch overdue loans for this branch
        $sql_overdue_loans = "
        SELECT
            uid,
            loan_balance,
            final_due_date,
            current_branch,
            product_id,
            DATEDIFF('$today', final_due_date) AS days_overdue
        FROM o_loans
        WHERE disbursed = 1
        AND status = 7
        AND current_branch = $branch_id
        ";

        $result_overdue_loans = mysqli_query($con, $sql_overdue_loans);

        if (!$result_overdue_loans) {
            die('Invalid query: ' . mysqli_error($con));
        }

        if (mysqli_num_rows($result_overdue_loans) > 0) {
            while ($loan = mysqli_fetch_assoc($result_overdue_loans)) {
                $days_overdue = $loan['days_overdue'];
                $loan_balance = $loan['loan_balance'];

                // Determine ageing bucket
                if ($days_overdue >= 1 && $days_overdue <= 30) {
                    $bucket = '1-30';
                } elseif ($days_overdue >= 31 && $days_overdue <= 60) {
                    $bucket = '31-60';
                } elseif ($days_overdue >= 61 && $days_overdue <= 90) {
                    $bucket = '61-90';
                } elseif ($days_overdue >= 91 && $days_overdue <= 180) {
                    $bucket = '91-180';
                } else {
                    $bucket = 'Over 180';
                }

                // Increment counts and amounts for this branch
                $ageing_buckets[$bucket]['count'] += 1;
                $ageing_buckets[$bucket]['amount'] += $loan_balance;
            }
        }

        // Store ageing report for this branch
        $grouped_ageing_reports[] = [
            'name' => $branch_name,
            'ageing_buckets' => $ageing_buckets
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

        // Initialize ageing buckets for this product
        $ageing_buckets = [
            '1-30' => ['count' => 0, 'amount' => 0],
            '31-60' => ['count' => 0, 'amount' => 0],
            '61-90' => ['count' => 0, 'amount' => 0],
            '91-180' => ['count' => 0, 'amount' => 0],
            'Over 180' => ['count' => 0, 'amount' => 0]
        ];

        // Fetch overdue loans for this product
        $sql_overdue_loans = "
        SELECT
            uid,
            loan_balance,
            final_due_date,
            current_branch,
            product_id,
            DATEDIFF('$today', final_due_date) AS days_overdue
        FROM o_loans
        WHERE disbursed = 1
        AND status = 7
        AND product_id = $product_id
        ";

        $result_overdue_loans = mysqli_query($con, $sql_overdue_loans);

        if (!$result_overdue_loans) {
            die('Invalid query: ' . mysqli_error($con));
        }

        if (mysqli_num_rows($result_overdue_loans) > 0) {
            while ($loan = mysqli_fetch_assoc($result_overdue_loans)) {
                $days_overdue = $loan['days_overdue'];
                $loan_balance = $loan['loan_balance'];

                // Determine ageing bucket
                if ($days_overdue >= 1 && $days_overdue <= 30) {
                    $bucket = '1-30';
                } elseif ($days_overdue >= 31 && $days_overdue <= 60) {
                    $bucket = '31-60';
                } elseif ($days_overdue >= 61 && $days_overdue <= 90) {
                    $bucket = '61-90';
                } elseif ($days_overdue >= 91 && $days_overdue <= 180) {
                    $bucket = '91-180';
                } else {
                    $bucket = 'Over 180';
                }

                // Increment counts and amounts for this product
                $ageing_buckets[$bucket]['count'] += 1;
                $ageing_buckets[$bucket]['amount'] += $loan_balance;
            }
        }

        // Store ageing report for this product
        $grouped_ageing_reports[] = [
            'name' => $product_name,
            'ageing_buckets' => $ageing_buckets
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

        // Initialize ageing buckets for this month
        $ageing_buckets = [
            '1-30' => ['count' => 0, 'amount' => 0],
            '31-60' => ['count' => 0, 'amount' => 0],
            '61-90' => ['count' => 0, 'amount' => 0],
            '91-180' => ['count' => 0, 'amount' => 0],
            'Over 180' => ['count' => 0, 'amount' => 0]
        ];

        // Fetch overdue loans for this month (loans overdue in this month)
        $sql_overdue_loans = "
        SELECT
            uid,
            loan_balance,
            final_due_date,
            current_branch,
            product_id,
            DATEDIFF('$today', final_due_date) AS days_overdue
        FROM o_loans
        WHERE disbursed = 1
        AND status = 7
        AND final_due_date BETWEEN '$month_start' AND '$month_end'
        ";

        $result_overdue_loans = mysqli_query($con, $sql_overdue_loans);

        if (!$result_overdue_loans) {
            die('Invalid query: ' . mysqli_error($con));
        }

        if (mysqli_num_rows($result_overdue_loans) > 0) {
            while ($loan = mysqli_fetch_assoc($result_overdue_loans)) {
                $days_overdue = $loan['days_overdue'];
                $loan_balance = $loan['loan_balance'];

                // Determine ageing bucket
                if ($days_overdue >= 1 && $days_overdue <= 30) {
                    $bucket = '1-30';
                } elseif ($days_overdue >= 31 && $days_overdue <= 60) {
                    $bucket = '31-60';
                } elseif ($days_overdue >= 61 && $days_overdue <= 90) {
                    $bucket = '61-90';
                } elseif ($days_overdue >= 91 && $days_overdue <= 180) {
                    $bucket = '91-180';
                } else {
                    $bucket = 'Over 180';
                }

                // Increment counts and amounts for this month
                $ageing_buckets[$bucket]['count'] += 1;
                $ageing_buckets[$bucket]['amount'] += $loan_balance;
            }
        }

        // Store ageing report for this month
        $grouped_ageing_reports[] = [
            'name' => $month,
            'ageing_buckets' => $ageing_buckets
        ];
    }
} else {
    echo "<p>Invalid value for \$obj variable.</p>";
    exit();
}

// Close the database connection
mysqli_close($con);
?>

<!-- HTML code for displaying the Defaulters Ageing Report -->
<div class="container mt-5">
    <!-- Overall Ageing Report -->
    <div class="row">
        <div class="col-12">
            <h3 class="mb-4 font-bold text-purple">Overall Defaulters Ageing Report as of <?php echo htmlspecialchars($today); ?></h3>
            <div class="bg-gray-light small-shadow">
                <table class="table table-bordered font-16">
                    <thead class="thead-dark">
                    <tr>
                        <th>Ageing Bucket (Days Overdue)</th>
                        <th>Number of Loans</th>
                        <th>Total Outstanding Amount</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($overall_ageing_buckets as $bucket => $data): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($bucket); ?></td>
                            <td><?php echo number_format($data['count']); ?></td>
                            <td><?php echo number_format($data['amount'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <hr/>
        </div>
    </div>

    <!-- Grouped Ageing Reports -->
    <div class="row">
        <?php foreach ($grouped_ageing_reports as $group): ?>
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
                            <th>Ageing Bucket (Days Overdue)</th>
                            <th>Number of Loans</th>
                            <th>Total Outstanding Amount</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($group['ageing_buckets'] as $bucket => $data): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($bucket); ?></td>
                                <td><?php echo number_format($data['count']); ?></td>
                                <td><?php echo number_format($data['amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Chart.js Graph for Overall Ageing Report -->
<div class="container mt-5">
    <div class="row">
        <div class="col-8">
            <canvas id="overallAgeingChart"></canvas>
        </div>
    </div>
</div>

<!-- Include Chart.js library -->


<script>
    function renderOverallAgeingChart() {
        // Prepare data for the chart
        const labels = <?php echo json_encode(array_keys($overall_ageing_buckets)); ?>;
        const dataCounts = <?php echo json_encode(array_column($overall_ageing_buckets, 'count')); ?>;
        const dataAmounts = <?php echo json_encode(array_column($overall_ageing_buckets, 'amount')); ?>;

        const ctx = document.getElementById('overallAgeingChart').getContext('2d');
        const ageingChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Number of Loans',
                        data: dataCounts,
                        backgroundColor: 'rgba(0, 123, 255, 0.1)', // Light blue fill (if fill is true)
                        borderColor: 'rgba(0, 123, 255, 1)', // Blue line
                        pointBackgroundColor: 'rgba(0, 123, 255, 1)', // Blue points
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(0, 123, 255, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        yAxisID: 'y',
                        fill: false, // Do not fill under the line
                    },
                    {
                        label: 'Total Outstanding Amount',
                        data: dataAmounts,
                        backgroundColor: 'rgba(220, 53, 69, 0.1)', // Light red fill (if fill is true)
                        borderColor: 'rgba(220, 53, 69, 1)', // Red line
                        pointBackgroundColor: 'rgba(220, 53, 69, 1)', // Red points
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        yAxisID: 'y1',
                        fill: false, // Do not fill under the line
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                stacked: false,
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    if (context.dataset.yAxisID === 'y1') {
                                        label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(context.parsed.y);
                                    } else {
                                        label += context.parsed.y;
                                    }
                                }
                                return label;
                            }
                        }
                    },
                    legend: {
                        labels: {
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            font: {
                                size: 12,
                            }
                        },
                        grid: {
                            display: true,
                            color: '#e0e0e0'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Number of Loans',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            font: {
                                size: 12,
                            },
                            precision: 0
                        },
                        grid: {
                            display: true,
                            color: '#e0e0e0'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Amount (in Ksh)',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            font: {
                                size: 12,
                            },
                            callback: function(value, index, values) {
                                return 'Ksh.' + value.toLocaleString();
                            }
                        },
                        grid: {
                            drawOnChartArea: false,
                            color: '#e0e0e0'
                        }
                    }
                }
            }
        });
    }

    // Call the function to render the chart
    renderOverallAgeingChart();
</script>


