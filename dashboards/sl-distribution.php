<?php
$start_date = getFirstDayOfMonth($date);
$end_date = "$date";

//--This Week

//--This month

?>
<br/>
<div class="box-separator">
    <i class="fa fa-download"></i>  DISTRIBUTION

</div>
<div class="row">
    <div class="col-sm-12">
        <div class="row">

            <div class="col-sm-12">
                <div class="box bg-black-gradient box-solid">
                    <div class="box-header box-title">





                        <div class="pull-right">
                            <input type="date" value="<?php echo $start_date; ?>" id="start_date_dist" class="btn btn-default font-bold" style="color: white;">
                            <input type="date" value="<?php echo $end_date; ?>" id="end_date_dist" style="color: white;" class="btn btn-default font-bold">

                            <button onclick="nd_distribution_list();" class="btn btn-success font-bold bg-black-gradient">GO</button>
                        </div>


                    </div>
                    <!-- /.box-header -->
                    <div class="box-body scroll-hor text-black" style="background: white;" id="nd_dashboard_distribution">
                        <a onclick="nd_distribution_list();" href="javascript:void(0)" class="font-bold text-blue hotbutton"><i class="fa fa-refresh"></i> Click to View</a>

                    </div>
                    <!-- /.box-body -->
                </div>
            </div>
        </div>


    </div>



</div>



<script>

</script>
