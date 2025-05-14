<?php
$group_id = decurl($_GET['group']);

if($group_id > 0) {
    $gro = fetchonerow('o_customer_groups', "uid='$group_id'");
    $branch_id = $gro['branch'];
    $branche_name = fetchrow('o_branches', "uid=$branch_id", "name");
}
?>
<section class="content-header">

    <h1>
        Group Details
        <small><?php echo $gro['group_name']; ?></small>
        <small><a href="groups">Back to groups <i class="fa fa-arrow-up"></i></a>  </small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?php echo $gro['full_name']; ?></li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs nav-justified font-16">
                    <li class="nav-item nav-100 active"><a href="#tab_1" data-toggle="tab" aria-expanded="false"><i class="fa fa-info"></i> Details</a></li>
                    <li class="nav-item nav-100"><a href="#tab_2" onclick="group_members_list('<?php echo $group_id; ?>');" data-toggle="tab" aria-expanded="false"><i class="fa fa-user"></i> Customer List</a></li>
                    <li class="nav-item nav-100"><a href="#tab_3" onclick="group_loan_list('<?php echo $group_id; ?>');"  data-toggle="tab" aria-expanded="false"><i class="fa fa-money"></i> Loan List</a></li>
                    <li class="nav-item nav-100"><a href="#tab_4" onclick="group_payment_list('<?php echo $group_id; ?>');"  data-toggle="tab" aria-expanded="false"><i class="fa fa-money"></i> Payments</a></li>
                    <li class="nav-item nav-100"><a href="#tab_5" onclick="group_savings_list('<?php echo $group_id; ?>');"  data-toggle="tab" aria-expanded="false"><i class="fa fa-hourglass-half"></i> Savings</a></li>
                    
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">
                        <div class="row">
                            <div style="margin-top: 25px;" class="col-md-2">
                                <?php echo $profile; ?>
                            </div>
                            <div class="col-md-7">
                                <h3>Customer Group Information</h3>

                                <?php
                                $group_members_array = array();
                                $group_loans_array = array();

                                $total_members = countotal('o_group_members',"group_id='$group_id' AND status=1","uid");
                                $total_loans = countotal('o_loans',"group_id='$group_id'  AND disbursed=1 AND status!=0 AND paid=0","uid");
                                $loans_total = totaltable('o_loans',"group_id='$group_id'  AND disbursed=1 AND status!=0 AND paid=0","loan_amount");

                                $outstanding_amount = totaltable('o_loans',"group_id='$group_id'  AND disbursed=1 AND paid=0 AND final_due_date >= '$date' AND status!=0","loan_balance");

                                $overdue_amount = totaltable('o_loans',"group_id='$group_id'  AND disbursed=1 AND paid=0 AND final_due_date < '$date' AND status!=0","loan_balance");
                                $down_pays = totaltable('o_incoming_payments',"group_id='$group_id' AND status=1 AND payment_category=3","amount");

                                $savings_total = totaltable('o_incoming_payments',"group_id='$group_id' AND status=1 AND payment_category=4","amount");






                                ?>



                                <table class="table-bordered font-14 table table-hover">
                                    <tr><td class="text-bold">UID</td><td><?php echo $group_id; ?></td></tr>
                                    <tr><td class="text-bold">Group Name</td><td class="font-18 font-bold"><?php echo $gro['group_name']; ?></td></tr>
                                    <tr><td class="text-bold">Description</td><td class="font-14 font-bold"><?php echo $gro['group_description']; ?></td></tr>
                                    <tr><td class="text-bold">Branch</td><td class="font-18 font-bold"><?php echo $branche_name; ?></td></tr>
                                    <tr><td class="text-bold">Chairman</td><td class="font-14 font-bold"><?php echo $gro['chair_name']; ?></td></tr>
                                    <tr><td class="text-bold">Group Phone</td><td class="font-14 font-bold"><?php echo $gro['group_phone']; ?></td></tr>
                                    <tr><td class="text-bold">Group Paybill/Till</td><td class="font-14 font-bold"><?php echo $gro['till']; ?></td></tr>
                                    <tr><td class="text-bold">Group Account</td><td class="font-14 font-bold"><?php echo $gro['account_number']; ?></td></tr>
                                    <tr><td class="text-bold">Added_date</td><td><?php echo $gro['added_date'].' '.fancydate($gro['added_date']); ?></td></tr>
                                    <tr><td class="text-bold">Status</td><td><?php echo status($gro['status']); ?></td></tr>
                                    <tr><td colspan="2"> _</td></tr>
                                    <tr><td class="text-bold">Total Members</td><td class="font-16 font-bold"><?php echo number_format($total_members); ?></td></tr>
                                    <tr><td class="text-bold">Number of Loans</td><td><?php echo number_format($total_loans); ?></td></tr>
                                    <tr><td class="text-bold">Loan Total</td><td class="font-16 font-bold"><?php echo money($loans_total); ?></td></tr>
                                    <tr><td class="text-bold">Outstanding Amount</td><td><?php echo money($outstanding_amount); ?></td></tr>
                                    <tr><td class="text-bold">Overdue Amount</td><td><?php echo money($overdue_amount); ?></td></tr>
                                    <tr class="bg-success"><td class="text-bold">Total Down payments</td><td class="font-16 font-bold text-green"><?php echo money($down_pays); ?></td></tr>
                                    <tr class="bg-gray-light"><td class="text-bold">Total Savings</td><td class="font-16 font-bold text-green"><?php echo money($savings_total); ?></td></tr>



                                </table>

                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <?php
                                    $update_addon = permission($userd['uid'],'o_customer_groups',"0","update_");
                                    if($update_addon == 1){
                                    ?>
                                    <tr><td><a href="groups?group-add-edit=<?php echo  encurl($group_id); ?>" class="btn btn-primary btn-block  btn-md grid-width-10"><i class="fa fa-pencil"></i> Update Group</a></td></tr>

                                    <?php
                                    }
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_2">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-users"></i></span>
                            </div>
                            <div class="col-md-7">
                                <table class="table-bordered font-14 table table-hover">
                                    <tr><th>UID</th><th>Name</th><th>Phone</th><th>National Id</th><th>Added Date</th><th>Limit</th><th>Action</th></tr>
                                    <tbody id="gmembers">

                                    </tbody>

                                </table>
                                <?php
                                $permi = permission($userd['uid'],'o_group_members',"0","create_");
                                if($permi == 1)
                                {
                                ?>
                                <hr/>
                                <form class="form-horizontal" autocomplete="off" onsubmit="return false;" method="post">
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label for="customer" class="col-sm-3 control-label">Customer</label>

                                            <div class="col-sm-7">
                                                <input class="form-control" type="text" autocomplete="off" onkeyup="search_cust();" id="customer_search" value="<?php echo $loan['customer_id'];?>" placeholder="Start typing customer name ...">
                                                <input type="hidden" id="customer_id_">
                                                <div id="customer_results">

                                                </div>
                                            </div>
                                            <div class="col-sm-2">

                                                <button onclick="addtogroup('<?php echo $group_id; ?>');" class="btn btn-primary"><i class="fa fa-plus"></i> Add</button>


                                            </div>

                                        </div>





                                    </div>
                                    <!-- /.box-body -->

                                    <!-- /.box-footer -->
                                </form>
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="col-md-3">

                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_3">
                        <div class="row">

                            <div class="col-md-7">
                                <div id="loan_info" class="scroll-hor">
                                    Loading Loans...
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div id="payments_info">
                                    Loading payments...
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane" id="tab_4">
                        <div class="row">

                            <div class="col-md-7">
                                <div id="payments_list" class="scroll-hor">
                                    Loading payments
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div id="payments_breakdown_list">
                                    Allocations will load here...
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane" id="tab_5">
                        <div class="row">

                            <div class="col-md-7">
                                <div id="savings_info" class="scroll-hor">
                                    Loading savings...
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="alert alert-info">
                                    <i class="fa fa-info-circle"></i>
                                    To record a saving, edit the incoming payment and select 'Savings' under the 'Payment for' section
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
