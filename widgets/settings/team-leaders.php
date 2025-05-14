<?php

/////----------Session Check
$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
/////---------End of session check
$leaders_obj = table_to_obj('o_users',"status=1 AND user_group in (15,16,17,23)","1000","uid","name");
$staff_obj = table_to_obj('o_users',"status=1 AND user_group in (12,13,14,21)","1000","uid","name");
$group_name = table_to_obj('o_user_groups',"uid>0","1000","uid","name");
$staff_groups = table_to_obj('o_users',"uid>0","100000","uid","user_group");

?>

<h3>Team Leaders</h3>
<div class="row">
    <div class="col-md-7">
        <h4>Existing Teams</h4>
        <table class="table table-bordered table-condensed">
            <thead><tr><th>#</th><th>Leader</th> <th>Agent</th><th>Group</th><th>Action</th></tr></thead>
            <tbody id="team_members">

            </tbody>
            <tfoot>
            <tr><td colspan="4"><br/></td></tr>
            <tr><td colspan="4"><h4>Add Team Members</h4></td></tr>
            <tr class="bg-gray-light well"><td colspan="2"> Team Leader
                    <select class="form-control" id="lo_">
                         <option value="0">--Select</option>
                        <?php
                        foreach ($leaders_obj as $staff_id => $staff_name) {

                                echo "<option value='$staff_id'>$staff_name (".$group_name[$staff_groups[$staff_id]].")</option>";
                        }
                        ?>
                    </select></td><td colspan="2"> Member
                    <select  class="form-control" id="co_">
                        <option value="0">--Select</option>
                        <?php
                        foreach ($staff_obj as $staff_id => $staff_name) {
                            echo "<option value='$staff_id'>$staff_name (".$group_name[$staff_groups[$staff_id]].")</option>";
                        }
                        ?>
                    </select>
                </td><td><br/>
                    <button title="Make the two a pair" class="btn btn-success bg-green-gradient btn-block" onclick="add_leader();">Add</button>




                </td></tr>


            </tfoot>
        </table>


    </div>

</div>

