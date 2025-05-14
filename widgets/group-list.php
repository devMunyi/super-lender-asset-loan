<section class="content-header">
    <h1>
        <?php


        echo "Groups"; ?>
        <small>List</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Groups</li>
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
                            <a class="btn" href="groups"><i class="fa fa-refresh"> </i> Reload All</a>
                            <select class="btn font-16 btn-md btn-default text-bold top-select" id="sel_branch" onchange="group_filters()">
                                <option value="0">All Branches</option>
                                <?php
                                $o_branches_ = fetchtable('o_branches', "status > 0", "name", "asc", "0,100", "uid ,name ");
                                while ($w = mysqli_fetch_array($o_branches_)) {
                                    $uid = $w['uid'];
                                    $name = $w['name'];
                                    echo "<option value='$uid'>$name</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-2">

                            <a class="btn btn-success float-right" href="?group-add-edit"><i class="fa fa-plus"></i> ADD NEW</a>
                        </div>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <table id="example1" class="table table-bordered table-condensed table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Group Name</th>
                                <th>Total Members</th>
                                <th>Total Loans</th>
                                <th>Branch</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="group_list">
                            <tr>
                                <td colspan="7">Loading data...</td>
                            </tr>

                        </tbody>
                        <tfoot>
                            <tr>
                                <th>ID</th>
                                <th>Group Name</th>
                                <th>Total Members</th>
                                <th>Total Loans</th>
                                <th>Branch</th>
                                <th>Status</th>
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
echo "<div style='display: none ;'>" . paging_values_hidden('uid > 0', 0, 10, 'uid', 'desc', '', 'group_list', 1) . "</div>"
?>