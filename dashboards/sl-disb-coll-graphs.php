<?php
$start_date = first_date_of_month($date);
?>
<div class="box-separator" id="time_performance"><i class="fa fa-line-chart"></i> TIME PERFORMANCE </div>


    <!-- small box -->
    <div class="box box-solid">

        <table class="table table-striped">
            <tr>
                <td> <input type="date" value="<?php echo $start_date; ?>" class="form-control" id="date_start"> </td>
                <td> <input type="date" value="<?php echo $date; ?>" class="form-control" id="date_end"> </td>
                <td> <select id="select_type" class="form-control">
                        <option value="DAILY">DAILY</option>
                        <option value="MONTHLY">MONTHLY</option>
                    </select>
                </td>
                <td> <button class="btn btn-primary" onclick="graph2_load(); scrollToDiv('time_performance',500)">GENERATE</button> </td>
            </tr>
        </table>
        <div id="graph1" style="min-height: 250px;">
            <i>Loading...</i>
        </div>
    </div>
