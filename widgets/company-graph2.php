<?php


// initialize the variables
$this_year = date('Y');
$this_month = date('m');

$start_date = "$this_year-$this_month-01";

?>


<div class="row">
    <div class="col-lg-12 col-xs-12" id="top-highlights">
        <i>Loading...</i>
    </div>

    <div class="col-lg-12 col-xs-12">


        <!-- small box -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Time Performance <i class="fa fa-line-chart"></i><span class="text-green"> Default shows MTD(Month-To-Date)</span></h3>
            </div>
            <table class="table table-striped">
                <tr>
                    <td> <input type="date" value="<?php echo $start_date; ?>" class="form-control" id="date_start"> </td>
                    <td> <input type="date" value="<?php echo $date; ?>" class="form-control" id="date_end"> </td>
                    <td> <select id="select_type" class="form-control">
                            <option value="DAILY">DAILY</option>
                            <option value="MONTHLY">MONTHLY</option>
                        </select>
                    </td>
                    <td> <button class="btn btn-primary" onclick="graph_load();">GENERATE</button> </td>
                </tr>
            </table>
            <div id="graph1">
                <i>Loading...</i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-xs-4" style="display: none;">
        <div class="box box-primary">
            <div class="box-header ui-sortable-handle" style="cursor: move;">
                <i class="ion ion-clipboard"></i>

                <h3 class="box-title">To Do List</h3>


            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <!-- See dist/js/pages/dashboard.js to activate the todoList plugin -->
                <ul class="todo-list ui-sortable">
                    <li class="">
                    <li>No Pending Items</li>
                    </li>


                </ul>
            </div>
            <!-- /.box-body -->
            <div class="box-footer clearfix no-border">
                <button type="button" class="btn btn-default pull-right"><i class="fa fa-plus"></i> Add item</button>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-xs-6">
        <!-- small box -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Disburse Progress - MTD(Month-To-Date)</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body" id="disburse-progress-mtd">
                Loading...
            <!-- <a onclick="disburse_progress_mtd_load()" href="javascript:void(0)">Click to view</a> -->
            </div>
            <!-- /.box-body -->
        </div>
    </div>

    <div class="col-lg-6 col-xs-6">
        <!-- small box -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Daily Performance</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body max-height" id="daily-performance">
                Loading...
            <!-- <a onclick="daily_performance_load()" href="javascript:void(0)">Click to view...</a> -->
            </div>
            <!-- /.box-body -->
        </div>
    </div>

    <!-- ./col -->
    <div class="col-lg-6 col-xs-6" style="display: none;">
        <!-- small box -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <!-- <h3 class="box-title">Product Performance</h3> -->
            </div>
            <!-- /.box-header -->
            <div class="box-body">

                <table class="table table-striped">
                    <tr>
                        <th>Product</th>
                        <th>Disbursements</th>
                    </tr>

                    <tr class="font-18 text-blue">
                        <th>Total.</th>
                        <th><?php echo money($loan_amount_total); ?></th>
                    </tr>
                </table>
            </div>
            <!-- /.box-body -->
        </div>
    </div>
    <!-- ./col -->
    <div class="col-lg-12 col-xs-12 well well-sm shadow p-3 mb-5 bg-white rounded">
        <div class="box-header with-border">
            <h3 title="Based on Disbursed Date Relative to Current Date" class="box-title box-primary">Performance BreakDown <i class="fa fa-line-chart"></i><span class="text-green"> Default shows MTD(Month-To-Date)</span></h3>
        </div>
        <table class="table table-striped" title="Filters By Disbursed Date">
            <tr>
                <td> <input type="date" value="<?php echo $start_date; ?>" class="form-control" id="bdate_start"> </td>
                <td> <input type="date" value="<?php echo $date; ?>" class="form-control" id="bdate_end"> </td>
                <td> <button class="btn btn-primary" onclick="performance_breakdown_load();">GENERATE</button> </td>
            </tr>
        </table>
        <!-- small box -->
        <div id="perform_">
            Loading...
        <!-- <a onclick="performance_breakdown_load()" href="javascript:void(0)">Click to view</a> -->
        </div>

    </div>
    <div class="col-lg-12 col-xs-12 well well-sm shadow p-3 mb-5 bg-white rounded">
        <div class="box-header with-border">
            <h3 class="box-title box-primary">Collection Rate <i class="fa fa-line-chart"></i></h3>
        </div>

        <!-- small box -->
        <div id="collection_rate">
        <a onclick="collection_rate()" href="javascript:void(0)">Click to view</a>
        </div>

    </div>

    <div class="col-lg-12 col-xs-12 well well-sm shadow p-3 mb-5 bg-white rounded">
        <div class="box-header with-border">
            <h3 class="box-title box-primary">Defaulters BreakDown <i class="fa fa-line-chart"></i><span class="text-green"> Default shows MTD(Month-To-Date)</span></h3>
        </div>
        <table class="table table-striped">
            <tr>
                <td> <input type="date" value="<?php echo $start_date; ?>" class="form-control" id="ddate_start"> </td>
                <td> <input type="date" value="<?php echo $date; ?>" class="form-control" id="ddate_end"> </td>
                <td> <button class="btn btn-primary" onclick="defaulters_breakdown();">GENERATE</button> </td>
            </tr>
        </table>
        <!-- small box -->
        <div id="defaulters_">
        <a onclick="defaulters_breakdown()" href="javascript:void(0)">Click to view</a>
        </div>

    </div>

    <!-- ./col -->
</div>