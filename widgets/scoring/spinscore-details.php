<?php
$sid = decurl($_GET['score']);

$spin_status_names = [
    1 => 'Completed',
    2 => 'Processing',
];

$customer_score_details_query = "SELECT ss.uid, c.full_name as customer_name, c.primary_mobile as phone, b.name as branch, ss.added_date, ss.processed_date, ss.spin_status  FROM o_spin_scoring ss INNER JOIN o_customers c ON ss.customer_id = c.uid INNER JOIN o_branches b ON b.uid = c.branch where ss.uid = '$sid'";
$sd = mysqli_query($con, $customer_score_details_query);
$sd = mysqli_fetch_array($sd);

?>
<section class="content-header">
    <h1>
        Score Details
        <small><?php echo $p['name']; ?> </small>
        <a title='Back to Store List' class='font-16' href="scoring?tab=spinscore"><i class='fa fa-arrow-circle-up'></i></a>
    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Score</li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs nav-justified font-16">
                    <li class="nav-item nav-100 active"><a href="#tab_1" data-toggle="tab" aria-expanded="false"><i class="fa fa-info"></i> Info</a></li>
    
                    <li onclick="spinScoreEvents(<?php echo encurl($sid); ?>)" class="nav-item nav-100 <?php echo $_GET['tab'] == 'tab_2' ? 'active' : '' ?>"><a href="#tab_2" data-toggle="tab" aria-expanded="false"><i class="fa fa-clock-o"></i> Events</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-info"></i></span>
                            </div>
                            <div class="col-md-7">
                                <table class="table-bordered font-14 table table-hover">
                                    <?php

                                    $uid = $sd['uid'];
                                    $name = $sd['customer_name'];
                                    $phone = $sd['phone'];
                                    $branch_name = $sd['branch'];
                                    $join_date = $sd['added_date'];
                                    $status = $sd['spin_status'];
                                    $status_name = $spin_status_names[$status];

                                    ?>
                                    <tr>
                                        <td class="text-bold">UID</td>
                                        <td><?php echo encurl($uid); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Name</td>
                                        <td><?php echo $name; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Phone</td>
                                        <td><?php echo $phone; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Branch</td>
                                        <td><?php echo $branch_name; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Join Date</td>
                                        <td><?php echo $join_date; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Status</td>
                                        <td> <?php echo $status_name; ?> </td>
                                    </tr>

                                </table>
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr>
                                        <td><a href="scoring?add-edit=<?php echo encurl($sid); ?>" class="btn btn-warning btn-block btn-md"><i class="fa fa-edit"></i> Edit Scoring</a></td>
                                    </tr>
                                    <tr>
                                        <td><a href="scoring?add-edit" class="btn btn-success btn-block btn-md"><i class="fa fa-plus"></i> New Scoring</a></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_2">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                            </div>
                            <div class="col-md-10">
                                <div class="scroll-hor body-box">
                                <table class="table-bordered font-14 table table-hover" id="example2">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Details</th>
                                            <th>UID </th>
                                        </tr>
                                    </thead>
                                    <tbody id="events_on_user">
                                        <tr>
                                            <td colspan='3'>
                                                <i>Loading events...</i>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                </div>
                            </div>

                        </div>
                    </div>
                    <!-- /.tab-pane -->

                </div>
                <!-- /.tab-content -->
            </div>
        </div>
    </div>
</section>