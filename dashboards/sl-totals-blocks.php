<div class="box-separator text-purple">
    <i class="fa fa-glass"></i>  EXECUTIVE SUMMARY (*Under Review)
</div>



<?php
$last_month = datesub($date, 0, 1, 0);
$start_date = getFirstDayOfMonth($last_month);
$end_date = "$last_month";
?>

<div class="row">
    <div class="col-sm-12">
        <div class="row">

            <div class="col-sm-12">
                <div class="box box-warning  box-solid">
                    <!-- /.box-header -->
                    <div class="box-body scroll-hor" style="background: white;" id="numbers_crunch">
                            <a onclick="nd_numbers();" href="javascript:void(0)" class="font-bold text-blue hotbutton"><i class="fa fa-refresh"></i> Click to View</a>

                    </div>
                    <!-- /.box-body -->
                </div>
            </div>




        </div>


    </div>



</div>




