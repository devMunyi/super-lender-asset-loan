<?php
//ini_set('memory_limit','512M');
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../php_functions/secondary-functions.php");
include_once ("../../configs/conn.inc");
$search_type = $_GET['search_type']; //'DAILY';
$start_date = $_GET['start_date']; ////'2021-01-01';
$end_date = $_GET['end_date'];  //'2021-04-12';

$userd = session_details();
$userbranch = $userd['branch'];


$loan_branches = branch_permissions($userd, 'o_loans');
$payment_branches = branch_permissions($userd, 'o_incoming_payments');
$customer_branches = branch_permissions($userd, 'o_customers');



$col_array = array();
$disb_array = array();
$defaulters_array = array();
$customers_acquisition_array = array();
$total_defaulters = 0;
///////--------------------Fetch loans

$loans = fetchtable('o_loans',"given_date BETWEEN '$start_date' AND '$end_date' AND disbursed=1  AND status!=0 $loan_branches","uid","asc","1000000","uid, loan_amount, given_date, loan_balance, status");

while ($r = mysqli_fetch_array($loans)){
    $loan_amount = $r['loan_amount'];
    $given_date = $r['given_date'];
    $loan_balance = $r['loan_balance'];

    $status = $r['status'];
    if($search_type == 'MONTHLY'){
        $given_ = explode('-', $given_date);
        $ym = $given_[0].'-'.$given_[1].'-01';
        $disb_array = obj_add($disb_array, $ym, $loan_amount);
        if($status == 7) {
            $loan_balance = $r['loan_balance'];
            $defaulters_array = obj_add($defaulters_array, $ym, $loan_balance);
            $total_defaulters+=1;
        }
    }
    elseif ($search_type == 'DAILY'){
        $disb_array = obj_add($disb_array, $given_date, $loan_amount);
        if($status == 7) {
            $loan_balance = $r['loan_balance'];
            $defaulters_array = obj_add($defaulters_array, $given_date, $loan_balance);
            $total_defaulters+=1;
        }
    }
}

///////---------------Fetch payment

$payments = fetchtable('o_incoming_payments',"status=1 AND payment_date BETWEEN '$start_date' AND '$end_date' $payment_branches","uid","asc","1000000","uid, amount, payment_date");

while($p = mysqli_fetch_array($payments)){
    $amount = $p['amount'];
    $pay_date = $p['payment_date'];
   // echo $pay_date.'<br/>';

    if($search_type == 'MONTHLY'){
        $pay_date_ = explode('-', $pay_date);
        $ym = $pay_date_[0].'-'.$pay_date_[1].'-01';
        $col_array = obj_add($col_array, $ym, $amount);
    }
    elseif ($search_type == 'DAILY'){
        $col_array = obj_add($col_array, $pay_date, $amount);
    }

}

$customers = fetchtable('o_customers',"date(added_date) BETWEEN '$start_date' AND '$end_date' AND status=1 $customer_branches","uid","asc","1000000","uid, date(added_date) as added_day");
while($c = mysqli_fetch_array($customers)){
    $cuid = $c['uid'];
    $added_date = $c['added_day'];

    if($search_type == 'MONTHLY'){
        $added_date_ = explode('-', $added_date);
        $ym = $added_date_[0].'-'.$added_date_[1].'-01';
        $customers_acquisition_array = obj_add($customers_acquisition_array, $ym, 1);
    }
    elseif ($search_type == 'DAILY'){
        $customers_acquisition_array = obj_add($customers_acquisition_array, $added_date, 1);
    }
}
//echo json_encode($customers_acquisition_array);
//echo "$start_date, $end_date, $search_type";
//echo json_encode($disb_array);
//$col_array = array();

/*
$col_array = array('2020-01-01'=>100, '2020-02-01'=>300, '2020-03-01'=>200);
$disb_array = array('2020-01-01'=>150, '2020-02-01'=>100, '2020-03-01'=>250);
$customers_acquisition_array = array('2020-01-01'=>160, '2020-02-01'=>0, '2020-03-01'=>300);
$defaulters_array = array('2020-01-01'=>-100, '2020-02-01'=>-6, '2020-03-01'=>-50);
*/

$dates = array_unique(array_merge(
    array_keys($col_array),
    array_keys($disb_array),
    array_keys($customers_acquisition_array),
    array_keys($defaulters_array)
));

sort($dates);

$col_array = ensureKeys($col_array, $dates, 0);
$disb_array = ensureKeys($disb_array, $dates, 0);
$customers_acquisition_array = ensureKeys($customers_acquisition_array, $dates, 0);
$defaulters_array = ensureKeys($defaulters_array, $dates, 0);

//echo json_encode($disb_array);
//echo "<br/>";
//echo json_encode($col_array);
//echo "<br/>";
//echo json_encode($customers_acquisition_array);
//echo "<br/>";
//echo json_encode($defaulters_array);
$total_disbursed = array_sum($disb_array);
$total_collected = array_sum($col_array);
$total_customers = array_sum($customers_acquisition_array);
$total_defaulted = array_sum($defaulters_array);

?>
<style>
    .bar {
        stroke-width: 3;
    }
    .label {
        font-size: 14px;
        text-anchor: middle;
    }
    .description {
        font-size: 12px;
        fill: black;
        text-anchor: middle;
    }
    .x-axis path,
    .x-axis line,
    .y-axis path,
    .y-axis line {
        shape-rendering: crispEdges;
        stroke: #000;
    }
    .legend {
        font-size: 12px;
        font-weight: bold;
    }
    .line {
        fill: none;
        stroke-width: 4;
    }
</style>

<!-- /.box-header -->
<div class="box-body">
    <div class="row">
        <div class="col-md-8">
            <div class="container scrollable-container">
                <svg id="chart" width="800" height="500"></svg>
            </div>
        </div>
        <div class="col-md-4">
            <h3> Totals</h3>
            <table class="table table-bordered font-16">
                <tr><td>Total Disbursement</td><td class="font-bold"><?php echo money($total_disbursed); ?></td></tr>

                <tr><td>Incoming Payments</td><td class="font-bold"><?php echo money($total_collected); ?></td></tr>

                <tr><td>Customer Acquisition</td><td class="font-bold"><?php echo money($total_customers); ?></td></tr>
                <tr><td>Defaulted Amount</td><td class="font-bold"><?php echo money($total_defaulted); ?></td></tr>
                <tr><td>Defaulters</td><td class="font-bold"><?php echo number_format($total_defaulters); ?></td></tr>

            </table>
        </div>
    </div>

</div>
<!-- /.box-body -->
<?php
include_once ("../../configs/close_connection.inc");

?>


<style>
    /* Add some basic styling for the tooltip */
    .tooltip {
        position: absolute;
        text-align: center;
        padding: 6px;
        font: 12px sans-serif;
        background: rgba(0, 0, 0, 0.7);
        border: 0px;
        border-radius: 4px;
        pointer-events: none;
        color: white;
    }
    svg#chart {
        font-weight: bold;
    }
</style>

<div id="chart"></div>

<script>
    (function() {
        // Extract PHP arrays to JavaScript
        const data = [
            <?php foreach ($dates as $date): ?>
            {
                date: '<?= $date ?>',
                disbursements: <?= $disb_array[$date] ?>,
                collections: <?= $col_array[$date] ?>,
                acquisition: <?= $customers_acquisition_array[$date] ?>,
                defaulters: <?= $defaulters_array[$date] ?>
            },
            <?php endforeach; ?>
        ];

        const margin = { top: 60, right: 20, bottom: 100, left: 80 };
        const width = 800 - margin.left - margin.right;
        const height = 500 - margin.top - margin.bottom;

        // Clear existing SVG content
        d3.select("#chart").select("svg").remove();

        // Create new SVG
        const svg = d3.select("#chart")
            .append("svg")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom);

        const chartGroup = svg.append("g")
            .attr("transform", `translate(${margin.left},${margin.top})`);

        // Parse date strings into JavaScript Date objects
        const parseDate = d3.timeParse("%Y-%m-%d");
        data.forEach(d => {
            d.date = parseDate(d.date);
        });

        // Define scales
        const x = d3.scaleTime()
            .domain(d3.extent(data, d => d.date))
            .range([0, width]);

        const y = d3.scaleLinear()
            .domain([
                d3.min(data, d => Math.min(d.disbursements, d.collections, d.acquisition, d.defaulters, 0)),
                d3.max(data, d => Math.max(d.disbursements, d.collections, d.acquisition, d.defaulters))
            ])
            .nice()
            .range([height, 0]);

        const color = d3.scaleOrdinal()
            .domain(['disbursements', 'collections', 'Customer Acquisition', 'defaulters'])
            .range(['#007bff', '#28a745', '#6f42c1', '#dc3545']);

        // Add x-axis
        chartGroup.append("g")
            .attr("class", "x-axis")
            .attr("transform", `translate(0,${height})`)
            .call(
                d3.axisBottom(x)
                    .tickFormat(d3.timeFormat("%Y-%m-%d"))
                    .tickValues(data.map(d => d.date))
            )
            .selectAll("text")
            .attr("text-anchor", "end")
            .attr("transform", "rotate(-45)")
            .attr("dx", "-0.8em")
            .attr("dy", "0.15em")
            .style("font-size", "12px"); // Increase tick label font size

        // Add x-axis label
        chartGroup.append("text")
            .attr("x", width / 2)
            .attr("y", height + margin.bottom - 40)
            .attr("text-anchor", "middle")
            .attr("fill", "black")
            .text("Dates")
            .style("font-size", "13px"); // Increase axis label font size

        // Add y-axis
        chartGroup.append("g")
            .attr("class", "y-axis")
            .call(d3.axisLeft(y))
            .selectAll("text")
            .style("font-size", "13px"); // Increase tick label font size

        // Add y-axis label
        chartGroup.append("text")
            .attr("transform", "rotate(-60)")
            .attr("y", -margin.left + 20)
            .attr("x", -height / 2)
            .attr("text-anchor", "middle")
            .attr("fill", "black")
            .text("Values")
            .style("font-size", "13px"); // Increase axis label font size

        // Create a tooltip div that is hidden by default
        const tooltip = d3.select("body").append("div")
            .attr("class", "tooltip")
            .style("opacity", 0);

        // Draw lines for each category
        const categories = ['disbursements', 'collections', 'acquisition', 'defaulters'];

        categories.forEach(category => {
            const line = d3.line()
                .x(d => x(d.date))
                .y(d => y(d[category]))
                .curve(d3.curveMonotoneX);

            chartGroup.append("path")
                .datum(data)
                .attr("class", `line line-${category}`)
                .attr("d", line)
                .attr("stroke", color(category))
                .attr("fill", "none")
                .attr("stroke-width", 12) // Make lines thicker
                .attr("stroke-linejoin", "round")
                .attr("stroke-linecap", "round");

            // Add circles for tooltip interaction
            chartGroup.selectAll(`.dot-${category}`)
                .data(data)
                .enter().append("circle")
                .attr("class", `dot-${category}`)
                .attr("cx", d => x(d.date))
                .attr("cy", d => y(d[category]))
                .attr("r", 3) // Increase circle radius
                .attr("fill", color(category))
                .on("mouseover", function(event, d) {
                    tooltip.transition()
                        .duration(200)
                        .style("opacity", 0.9);
                    tooltip.html(`${category.charAt(0).toUpperCase() + category.slice(1)}: ${d[category]}<br>Date: ${d3.timeFormat("%Y-%m-%d")(d.date)}`)
                        .style("left", (event.pageX + 10) + "px")
                        .style("top", (event.pageY - 28) + "px");
                    d3.select(this).attr("r", 8);
                })
                .on("mouseout", function() {
                    tooltip.transition()
                        .duration(500)
                        .style("opacity", 0);
                    d3.select(this).attr("r", 6);
                });
        });

        // Add legend
        const legend = chartGroup.append("g")
            .attr("class", "legend")
            .attr("transform", `translate(0, -40)`);

        legend.selectAll(".legend-item")
            .data(categories)
            .enter().append("g")
            .attr("class", "legend-item")
            .attr("transform", (d, i) => `translate(${i * 150}, 0)`)
            .each(function(d) {
                d3.select(this)
                    .append("rect")
                    .attr("x", 0)
                    .attr("y", -10)
                    .attr("width", 15)
                    .attr("height", 15)
                    .attr("fill", color(d));

                d3.select(this)
                    .append("text")
                    .attr("x", 20)
                    .attr("y", 0)
                    .attr("dy", "0.35em")
                    .style("font-weight", "bold")
                    .style("font-size", "13px") // Increase legend text size
                    .text(d.charAt(0).toUpperCase() + d.slice(1));
            });
    })();
</script>











