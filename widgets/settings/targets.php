<?php

/////----------Session Check
$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
/////---------End of session check
?>

<h3>Targets</h3>
<div class="row">
    <div class="col-md-10">
        <?php
        $currentMonth = new DateTime(); // Get the current date

        // Loop through the last 12 months
        for ($i = 0; $i < 6; $i++) {
            // Clone the current date and subtract $i months
            $month = (clone $currentMonth)->modify("-$i months");
            $real_date =  $month->format('Y-m-d');
            $real_date_1 = getFirstDayOfMonth($real_date);
            $real_date_31 = last_date_of_month($real_date);
            // Print in the format YYYY-MMM (e.g., 2024-Jan)
            echo " <a class='btn btn-default bg-gray-light' href='settings?targets&month=$real_date_1'>".$month->format('Y-M') . "</a> ";
        }
        ?>


        <table class="table table-bordered table-condensed">

            <thead><tr><th>Branch</th><th>Disbursements</th></tr></thead>
            <tbody>
            <?php
            //query platform settings table
            $month = $date;
            if(isset($_GET['month'])){
                $month = $_GET['month'];
            }

            $start_date = first_date_of_month($month);
            $end_date = last_date_of_month($month);

            $branch_names = table_to_obj('o_branches',"status = 1","1000","uid","name");
            $branch_targets = table_to_obj('o_targets',"target_type='DISBURSEMENTS' AND target_group='BRANCH' AND status=1 AND starting_date='$start_date' AND ending_date='$end_date'","1000","group_id","amount");



           foreach ($branch_names as $bid => $bname){
               $branch_t = $branch_targets[$bid];

               echo "<tr><td>$bname</td><td>".money($branch_t)."</td></tr>";

           }
            ?>
                       </tbody>
            <tfoot>
            <tr><td colspan="4"><br/></td></tr>
            <tr class="bg-gray-light well"><td class="text-success"> Add New Targets</td><td>
                    <select class="form-control" id="br_">
                         <option value="0">--Select</option>
                        <?php
                        foreach ($branch_names as $bid => $bname) {
                                echo "<option value='$bid'>$bname</option>";
                        }
                        ?>
                    </select></td><td>
                    <input type="number"  class="form-control" id="target_">

                </td>
                <td><input type="date" class="form-control" id="target_date" value="<?php echo $month; ?>"></td>
                <td><button class="btn btn-success" onclick="update_target('ONE');">Update One</button></td><td> <button class="btn btn-danger" onclick="update_target('ALL');">Update All</button></td></tr>

            <!--         Copy                         ------------>
            <tr><td colspan="6"><br/></td></tr>
            <tr class="bg-gray-light well"><td class="text-success"> Copy Targets</td>

                <td><input type="date" class="form-control" id="from_date"></td>
                <td><input type="date" class="form-control" id="to_date" value="<?php echo first_date_of_month($date); ?>"></td>
                <td colspan="2"><button class="btn btn-success" onclick="copy_targets();">Copy All</button></td><td> </td></tr>

            </tfoot>
        </table>
    </div>
</div>

