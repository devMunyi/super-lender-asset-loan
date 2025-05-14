<?php

//$userd = session_details_v2();
$userbranch = $userd['branch'];
$view_summary = permission($userd['uid'], 'o_summaries', "0", "read_");
?>
<div class="row">
    <div class="col-lg-12 col-xs-12" id="top-highlights">
        <i>Loading...</i>
    </div>

    <div class="col-lg-6 col-xs-6">
        <!-- small box -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Disburse Progress - MTD</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body" id="disburse-progress-mtd">
                <i>Loading...</i>
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
            <!-- Load resource -->
            <div class="box-body max-height" id="daily-performance">
                <a onclick="daily_performance_load()" href="javascript:void(0)">Click to view...</a>
            </div>
            <!-- /.box-body -->
        </div>
    </div>
</div>