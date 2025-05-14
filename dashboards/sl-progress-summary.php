<div class="box-separator">
  <i class="fa fa-clock-o"></i>  PERFORMANCE PROGRESS
</div>
<?php
$start_date = getFirstDayOfMonth($date);
$end_date = "$date";




?>
<div class="row">
    <div class="col-sm-6">
        <div class="container-fluid">
        <div class="font-18 font-bold text text-blue well well-sm bar_sep pull-left"><i class="fa fa-arrow-circle-o-up"></i> Disbursements </div>
        <div class="pull-right">
             <input type="date" value="<?php echo $start_date; ?>" id="start_date_progress" class="btn btn-default">
            <input type="date" value="<?php echo $end_date; ?>" id="end_date_progress" class="btn btn-default">
            <button id="btn_nd_loan_progress" onclick="nd_loan_progress();" class="btn btn-success hotbutton bg-black-gradient">GO</button>
        </div>
        </div>
       <div id="nd_disb_progress" class="container-fluid scrollable-container" style="max-height: 400px;">
           <a onclick="nd_loan_progress();" href="javascript:void(0)" class="font-bold text-blue"><i class="fa fa-refresh"></i> Click to View</a>

       </div>
    </div>


    <div class="col-sm-6">
        <div class="container-fluid">
        <div class="font-18 font-bold text text-green well well-sm bar_sep pull-left"><i class="fa fa-arrow-circle-o-down"></i> Collections </div>
        <div class="pull-right">
            <input type="date" value="<?php echo $start_date; ?>" id="start_date_payprogress" class="btn btn-default">
            <input type="date" value="<?php echo $end_date; ?>" id="end_date_payprogress" class="btn btn-default">
            <button onclick="nd_payments_progress();" class="btn btn-success bg-black-gradient">GO</button>
        </div>
        </div>
       <div id="nd_collections_progress" class="container-fluid scrollable-container" style="max-height: 400px;">
           <a onclick="nd_payments_progress();" href="javascript:void(0)" class="font-bold text-blue hotbutton"><i class="fa fa-refresh"></i> Click to View</a>

       </div>
    </div>
</div>
