<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>Group Name</th>
                <th>Officer</th>
                <th>Meeting Day</th>
                <th>Meeting Time</th>
                <th>Meeting Venue</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $groups = fetchtable('o_customer_groups', "uid > 0", "uid", "asc", "1000", "uid, group_name, chair_name, meeting_day, meeting_time, meeting_venue");

            $days_obj = table_to_obj('o_days', "uid > 0", "1000", "uid", "name");
            while ($g = mysqli_fetch_array($groups)) {
                $gid = $g['uid'];
                $gname = $g['group_name'];
                $officer = $g['chair_name'];
                $meeting_day_ = intval($g['meeting_day']);
                if ($meeting_day_ > 0) {
                    $meeting_day = $days_obj[$meeting_day_];
                } else {
                    $meeting_day = "";
                }
                $meeting_time_ = trim($g['meeting_time']);
                if(strlen($meeting_time_) > 0){
                    $meeting_time = convertTimeTo12Hrs($meeting_time_);
                }else{
                    $meeting_time = "";
                }
                $meeting_venue = $g['meeting_venue'];

                echo "<tr><td>$gname</td><td>$officer</td><td>$meeting_day</td><td>$meeting_time</td><td>$meeting_venue</td></tr>";
            }
            ?>
        </tbody>

        <tfoot>
            <tr>
                <th>#</th>
                <th>--</th>
                <th>--</th>
                <th>--</th>
                <th>--</th>
            </tr>
        </tfoot>
    </table>


</div>

<?php
