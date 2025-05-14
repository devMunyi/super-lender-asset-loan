<?php
$create_loan = permission($userd['uid'],'o_loans',"0","create_");


if($userd['user_group'] == 8){
    $create_loan = 0;
    $userid = $userd['uid'];
    $lo_pair = fetchrow('o_pairing',"co='$userid' AND status=1","lo");
    //---Check if LO is still enabled
    $lo_status =  fetchrow('o_users',"uid='$lo_pair'","status");
    if($lo_status == 1){
        ////----LO pair is active, don't grant rights
    }
    else{
        ////----LO pair is inactive, grant the rights to CO
        $create_loan = permission($lo_pair, 'o_loans', "0", "create_");
        $co_is_also_lo = 1;

    }
}

if($create_loan == 1){
?>
<section class="content-header">
    <h1>
        <?php echo arrow_back('loans', 'loans'); ?>
        <?php
        $lid = $_GET['loan-add-edit'];
        if ($lid > 0) {
            $loan = fetchonerow('o_loans', "uid='" . decurl($lid) . "'");
            $loan_id = $_GET['loan-add-edit'];

            echo "Loan <small>Edit</small>";
        } else {
            $loan = array();
            $loan_id = "";
            echo "Loan <small>Add</small>";
        }

        ?>

    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Loan/Create</li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">

            <!-- /.box -->

            <div class="box">

                <!-- /.box-header -->
                <div class="row">
                    <div class="col-xs-1"></div>
                    <div class="col-sm-6">
                        <!-- /.box-header -->
                        <!-- form start -->

                            <h3>Create a New Loan </h3>
                            <form class="form-horizontal" autocomplete="off" onsubmit="return false;" method="post">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="customer" class="col-sm-3 control-label">Customer</label>

                                        <div class="col-sm-9">
                                            <input class="form-control" type="text" autocomplete="off" onkeyup="search_cust();" id="customer_search" value="<?php echo $loan['customer_id'];?>" placeholder="Start typing customer name ...">
                                            <input type="hidden" id="customer_id_">
                                            <div id="customer_results">

                                            </div>
                                        </div>

                                    </div>

                                    <div class="form-group">
                                        <label for="product" class="col-sm-3 control-label">Product</label>

                                        <div class="col-sm-9">
                                            <select class="form-control" name="type_" id="product">
                                                <option value="0">--Select One</option>
                                                <?php
                                                $o_loan_products_ = fetchtable('o_loan_products',"status=1", "name", "asc", "0,100", "uid ,name ,description ");
                                                while($o = mysqli_fetch_array($o_loan_products_))
                                                {
                                                    $uid = $o['uid'];
                                                    $name = $o['name'];
                                                    $description = $o['description'];

                                                    if(($loan['product_id']) == $uid){
                                                        $selected_l = 'SELECTED';
                                                    }else{
                                                        $selected_l = '';
                                                    }
                                                    echo "<option $selected_l value='$uid'>$name</option>";
                                                }

                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="product" class="col-sm-3 control-label">Loan Type</label>

                                        <div class="col-sm-9">
                                            <select class="form-control" name="type_" id="loan_type">
                                                <option value="0">--Select One</option>
                                                <?php

                                                $o_loan_types = fetchtable('o_loan_types', "status=1", "name", "asc", "0,10", "uid, icon ,name");
                                                while ($t = mysqli_fetch_array($o_loan_types)) {
                                                    $uid = $t['uid'];
                                                    $name = $t['name'];
                                                    $icon = $t['icon'];

                                                    echo "<option value='$uid'>$name</option>";
                                                }


                                                ?>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="amount" class="col-sm-3 control-label">Amount</label>

                                        <div class="col-sm-9">
                                            <input class="form-control text-bold font-18" type="number" value="<?php echo $loan['loan_amount']; ?>" id="amount" placeholder="0.00">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="comments" class="col-sm-3 control-label">Comments</label>

                                        <div class="col-sm-9">
                                           <textarea class="form-control" id="comments"></textarea>
                                        </div>
                                    </div>

                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-9">
                                        <div class="box-footer">
                                            <br/>
                                            <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                                            <button type="submit"
                                                    class="btn btn-success bg-green-gradient btn-lg pull-right"
                                                    onclick="create_loan();">
                                                Create
                                            </button>
                                        </div>
                                    </div>

                                </div>
                                <!-- /.box-body -->

                                <!-- /.box-footer -->
                            </form>
                        <?php
    $upload_ = permission($userd['uid'],'o_incoming_payments',"0","create_");
    if($upload_ == 1){
                        ?>
                        <hr/>
                       <div class="well box bg-light-blue">
                           <h4>Upload Loan Disbursement Report from Mpesa</h4>
                        <form method="POST" id="loans-upload"  action="action/loan/upload-statuses" enctype="multipart/form-data">
                            <div class="form-group">
                                <div class="col-sm-9">
                                    <input type="file" class="form-control" name="file_">
                                </div>
                                <div class="col-sm-3">
                                    <button type="submit"  class="btn bg-black-gradient pull-right" onclick="formready('loans-upload');">Upload</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">

                            <div class="prgress">
                                <div class="messageloans-upload" id="message"></div>
                                <div class="progressloans-upload" id="progress">
                                    <div class="barloans-upload" id="bar"></div>
                                    <br>
                                    <div class="percentloans-upload" id="percent"></div>
                                </div>
                            </div>
                                </div>
                            </div>



                        </form>
                           <br/>
                       </div>

                       <?php
                        }
                       ?>


                    </div>
                    <div class="col-xs-1"></div>
                    <div class="col-sm-4 card">
                        <?php
                        include_once ("widgets/calculator.php");
                        ?>

                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
</section>
<?php
}
else{
    echo errormes("You don't have permission to view this page");
}
    ?>