<section class="content-header">
    <h1>
        Branches
        <small>List</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Branch</li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">

            <!-- /.box -->

            <div class="box">
                <div class="box-header bg-info">
                    <div class="row">
                        <div class="col-md-10">
                            <h3 class="box-title font-16">
                                <select class="btn font-16 btn-md btn-default text-bold top-select" id="branch_order" onchange="branch_filters()">
                                    <option value="desc">Newest First</option>
                                    <option value="asc">Oldest First</option>
                                </select>

                                <select class="btn font-16 btn-md btn-default text-bold top-select" id="sel_branch_status" onchange="branch_filters()">
                                    <option value="0">All Statuses</option>
                                    <?php

                                    $recs = fetchtable('o_branch_statuses', "uid>0", "uid", "asc", "100", "uid ,name");
                                    while ($r = mysqli_fetch_array($recs)) {
                                        $uid = $r['uid'];
                                        $name = $r['name'];
                                        echo "<option value=\"$uid\">$name</option>";
                                    }
                                    ?>
                                </select>

                                <select class="btn font-16 btn-md btn-default text-bold top-select" id="sel_branch_region" onchange="branch_filters()">
                                    <option value="0">All Regions</option>
                                    <?php

                                    $recs = fetchtable('o_regions', "uid>0", "uid", "asc", "100", "uid ,name");
                                    while ($r = mysqli_fetch_array($recs)) {
                                        $uid = $r['uid'];
                                        $name = $r['name'];
                                        echo "<option value=\"$uid\">$name</option>";
                                    }
                                    ?>
                                </select>
                            </h3>
                        </div>
                        <div class="col-md-2">
                            <a href="branches?add-edit" class="btn btn-success float-right"><i class="fa fa-plus"></i> NEW BRANCH</a>
                        </div>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">

                    <table id="example1" class="display table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>UID</th>
                                <th>Name</th>
                                <th>Region</th>
                                <th>Address</th>
                                <th>Added Date</th>
                                <th>Status</th>
                                <th>Frozen</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="branch_list">
                            <tr>
                                <td colspan="8">Loading data...</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>UID</th>
                                <th>Name</th>
                                <th>Region</th>
                                <th>Address</th>
                                <th>Added Date</th>
                                <th>Status</th>
                                <th>Frozen</th>
                                <th>Action</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
</section>

<?php
echo "<div style='display: none;'>" . paging_values_hidden('uid > 0', 0, 10, 'uid', 'desc', '', 'branch_list') . "</div>"
?>