<?php
{
if($b > 0){
    $staff_obj = table_to_obj('o_users',"branch='$b'","100000","uid","name");
    $staff_statuses = table_to_obj('o_users',"uid > 0","100000","uid","status");
    $staff_branches_obj = table_to_obj('o_users',"status=1","100000","uid","branch");
    $branch_names = table_to_obj('o_branches',"uid > 0","1000","uid","name");
    $pairs = fetchtable('o_pairing',"status=1","lo","asc","1000");

    $kpi_measured = table_to_obj('o_user_groups',"uid>0","1000","uid","kpi_measured");
    $group_name = table_to_obj('o_user_groups',"uid>0","1000","uid","name");
    $staff_groups = table_to_obj('o_users',"uid>0","100000","uid","user_group");
?>

<h3>Pairs </h3>
   <div class="alert alert-warning bg-blue-gradient">
       Please make sure the pairing is done correctly before uploading new loan pairs above
   </div>
<div class="row">
    <div class="col-md-7">
        <h4>Paired</h4>
        <table class="table table-bordered table-condensed">

            <thead><tr><th>#</th><th>LO</th> <th>CO</th><th>Branch</th><th>Action</th></tr></thead>
            <tbody>
            <?php
            $paired = fetchtable('o_pairing',"status=1 AND branch='$b' AND status=1","uid","asc","1000");
            while($p = mysqli_fetch_array($paired))
            {
                $pid = $p['uid'];
                $lo = $p['lo'];
                $co = $p['co'];

                array_push($all_paired_array, $lo);
                array_push($all_paired_array, $co);

                $lo_name = $staff_obj[$lo];
                $co_name = $staff_obj[$co];
                $lo_branch = $branch_names[$staff_branches_obj[$lo]];

                $lo_status = $staff_statuses[$lo];
                if ($lo_status == 1) {
                    $color1 = "";
                } else {
                    $color1 = "<i title='User is inactive' class='fa fa-hazard'></i>";
                }
                $co_status = $staff_statuses[$co];
                if ($co_status == 1) {
                    $color2 = "";
                } else {
                    $color2 = "<i title='User is inactive' class='fa fa-hazard'></i>";
                }


                $act = "<a class='btn btn-danger' onclick=\"delink($pid);\"><i class='fa fa-chain-broken'></i> Break</a>";

                echo "<tr><td>$pid</td><td>$lo_name (" . $group_name[$staff_groups[$lo]] . ") $color1 ($lo)</td> <td>$co_name (" . $group_name[$staff_groups[$co]] . ") $color2($co)</td><td>$lo_branch</td><td>$act</td></tr>";
            }
            ?>
                       </tbody>
            <tfoot>
            <tr><td colspan="4"><br/></td></tr>
            <tr><td colspan="4"><h4>Pair Users</h4></td></tr>
            <tr class="bg-gray-light well"><td colspan="2"> LO
                    <select class="form-control" id="lo_">
                         <option value="0">--Select</option>
                        <?php
                        foreach ($staff_obj as $staff_id => $staff_name) {
                            if($staff_groups[$staff_id] == 7) {
                                echo "<option value='$staff_id'>$staff_name (".$group_name[$staff_groups[$staff_id]].")</option>";
                            }
                        }
                        ?>
                    </select></td><td colspan="2"> CO
                    <select  class="form-control" id="co_">
                        <option value="0">--Select</option>
                        <?php
                        foreach ($staff_obj as $staff_id => $staff_name) {
                        if($staff_groups[$staff_id] == 8)
                             {
                            echo "<option value='$staff_id'>$staff_name (".$group_name[$staff_groups[$staff_id]].")</option>";
                             }
                        }
                        ?>
                    </select>
                </td><td><br/>
                    <button title="Make the two a pair" class="btn btn-success bg-green-gradient btn-block" onclick="pair_users();">Pair</button>




                </td></tr>


            </tfoot>
        </table>



    </div>
    <div class="col-md-5 bg-info">
        <h4>Unpaired</h4>
        <table class="table table-condensed table-striped">
            <thead>
            <tr><th>BDO</th><th>Group</th><th>Branch</th></tr>
            </thead>
            <tbody>
            <?php
            foreach ($staff_obj as $staff_id => $staff_name) {
                $group_id = $staff_groups[$staff_id];

                $in_pair = in_array($staff_id, $all_paired_array);
                if($in_pair == false && ($group_id == 7 || $group_id == 8)) {
                    $staff_group = $group_name[$staff_groups[$staff_id]];
                    $branch = $branch_names[$staff_branches_obj[$staff_id]];
                    echo "<tr><td>$staff_name</td><td>$staff_group</td><td>$branch</td></tr>";
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
<?php
}
}
    ?>
