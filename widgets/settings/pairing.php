<?php

/////----------Session Check
$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
/////---------End of session check
?>

<h3>Pairing</h3>
<div class="row">
    <div class="col-md-7">
        <h4>Paired</h4>
        <table class="table table-bordered table-condensed">

            <thead><tr><th>#</th><th>LO</th> <th>CO</th><th>Branch</th><th>Action</th></tr></thead>
            <tbody>
            <?php
            //query platform settings table
            $staff_obj = table_to_obj('o_users',"status=1","100000","uid","name");
            $staff_branches_obj = table_to_obj('o_users',"status=1","100000","uid","branch");
            $branch_names = table_to_obj('o_branches',"uid > 0","1000","uid","name");
            $pairs = fetchtable('o_pairing',"status=1","lo","asc","1000");

            $kpi_measured = table_to_obj('o_user_groups',"uid>0","1000","uid","kpi_measured");
            $group_name = table_to_obj('o_user_groups',"uid>0","1000","uid","name");
            $staff_groups = table_to_obj('o_users',"uid>0","100000","uid","user_group");

            $total = mysqli_num_rows($pairs);
            if($total == 0){
                echo "<tr><td colspan='4'><i>No pairs set</i></td></tr>";
            }
            else {
                $all_paired_array = array();
                $n = 0;
                while ($p = mysqli_fetch_array($pairs)) {
                    $pid = $p['uid'];
                    $lo = $p['lo'];
                    $co = $p['co'];

                    $lo_name = $staff_obj[$lo];
                    $co_name = $staff_obj[$co];

                    $lo_branch = $branch_names[$staff_branches_obj[$lo]];
                    array_push($all_paired_array, $lo);
                    array_push($all_paired_array, $co);
                    $n+=1;
                    $act = "<a class='btn btn-danger' onclick=\"delink($pid);\"><i class='fa fa-chain-broken'></i> Break</a>";

                    echo "<tr><td>$n</td><td>$lo_name (".$group_name[$staff_groups[$lo]].")</td> <td>$co_name (".$group_name[$staff_groups[$co]].")</td><td>$lo_branch</td><td>$act</td></tr>";
                }
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
                            if($kpi_measured[$staff_groups[$staff_id]] == 1 && ($staff_groups[$staff_id] == 7 || $group_name[$staff_groups[$staff_id]] == 'Loan Officer')) {
                                echo "<option value='$staff_id'>$staff_name (".$group_name[$staff_groups[$staff_id]].")</option>";
                            }
                        }
                        ?>
                    </select></td><td colspan="2"> CO
                    <select  class="form-control" id="co_">
                        <option value="0">--Select</option>
                        <?php
                        foreach ($staff_obj as $staff_id => $staff_name) {
                        if($kpi_measured[$staff_groups[$staff_id]] == 1 && ($staff_groups[$staff_id] == 8 || $group_name[$staff_groups[$staff_id]] == 'Collections Officer'))
                             {
                            echo "<option value='$staff_id'>$staff_name (".$group_name[$staff_groups[$staff_id]].")</option>";
                             }
                        }
                        ?>
                    </select>
                </td><td><br/>
                    <button title="Make the two a pair" class="btn btn-success bg-green-gradient btn-block" onclick="pair_users();">Pair</button>




                </td></tr>
            <tr><td colspan="5">
                    <hr/>

                    <h4>Move accounts</h4>
                </td></tr>
            <tr class="bg-gray-light well"><td colspan="1"> User1
                    <select class="form-control" id="user1_">
                        <option value="0">--Select</option>
                        <?php
                        foreach ($staff_obj as $staff_id => $staff_name) {
                            if($kpi_measured[$staff_groups[$staff_id]] == 1) {
                                echo "<option value='$staff_id'>$staff_name (".$group_name[$staff_groups[$staff_id]].")</option>";
                            }
                        }
                        ?>
                    </select></td><td colspan="1"> User2
                    <select  class="form-control" id="user2_">
                        <option value="0">--Select</option>
                        <?php
                        foreach ($staff_obj as $staff_id => $staff_name) {
                            if($kpi_measured[$staff_groups[$staff_id]] == 1)
                            {
                                echo "<option value='$staff_id'>$staff_name (".$group_name[$staff_groups[$staff_id]].")</option>";
                            }
                        }
                        ?>
                    </select>
                </td>
                <td>
                    Update Only
                <select class="form-control" id="group_cat_">
                    <option class="LO">LO</option>
                    <option class="CO">CO</option>
                </select>

                </td>
                <td><br/>
                    <button title="Make the two a pair" class="btn btn-success bg-blue-gradient btn-block" onclick="move_accounts();">Move</button>




                </td></tr>

            </tfoot>
        </table>
        <hr/>
        <h4>Additional Actions</h4>
        <table class="table table-striped table-hover">
            <thead>
            <tr><th>Branch</th><th>Allocate Missing</th><th>Re-Allocate All</th><th>Re-Allocate To Original <a title="When this is initiated, accounts will be allocated to the BDOs who added the customer"><i class="fa fa-info"></i></a></th></tr>
            </thead>
            <?php
            foreach ($branch_names as $bid => $bname) {
               echo "<tr>"."<td>$bname</td><td><button class=\"btn btn-sm bg-orange-active\" onclick=\"allocate_missing_loans($bid);\"><i class=\"fa fa-rocket\"></i> GO</button></td><td><button class=\"btn btn-sm bg-red-gradient\" onclick=\"allocate_all_loans($bid);\"><i class=\"fa fa-warning\"></i> GO</button></td><td><button class=\"btn btn-sm bg-black-active\" onclick=\"allocate_original_loans($bid);\"><i class=\"fa fa-bolt\"></i> GO</button></td></tr>";
            }
            ?>

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
                $in_pair = in_array($staff_id, $all_paired_array);
                if($kpi_measured[$staff_groups[$staff_id]] == 1 && $in_pair == false) {
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

