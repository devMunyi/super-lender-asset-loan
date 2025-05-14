

<section class="content-header">
    <h1>
      Blind Allocation
    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Allocations</li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-8">

            <div class="nav-tabs-custom">
                <div class="tab-content">

                    <!-- /.tab-pane -->
                    <!-- /.tab-pane -->
                    <div class="tab-pane active" id="tab_1">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-save"></i></span>
                            </div>
                            <div class="col-md-6">

                                <form class="form-horizontal" onsubmit="return false;">
                                    <div class="box-body">
                                        <div class="form-group">
                                            <label for="payment_code" class="col-sm-3 control-label">Transaction Code</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control"  id="payment_code" name="title">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="payment_code" class="col-sm-3 control-label">Amount</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control"  id="amount" name="amount">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="mobile_number" class="col-sm-3 control-label">Mobile Number</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control"   id="mobile_number" name="mobile_number">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="loan_code" class="col-sm-3 control-label">Loan Code</label>
                                            <div class="col-sm-9">
                                                <input type="text" class="form-control"  id="loan_code" name="loan_code">
                                            </div>
                                        </div>

                                        <div class="form-group">

                                            <label for="date_made" class="col-sm-3 control-label">Date Paid</label>

                                            <div class="col-sm-9">
                                                <input type="date" class="form-control"  id="date_made">
                                            </div>
                                        </div>


                                        <div class="col-sm-3">

                                        </div>
                                        <div class="col-sm-9">
                                            <div class="box-footer">
                                                <br>
                                                <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                                                <button type="submit" class="btn btn-success btn-lg pull-right" onclick="payment_allocate();">Allocate
                                                </button>
                                            </div>

                                        </div>

                                    </div>
                                    <!-- /.box-body -->

                                    <!-- /.box-footer -->
                                </form>
                            </div>
                            <div class="col-md-4">
                                <?php
                                if($rep_id  > 0){
                                    echo "<a class='btn btn-primary' href='incoming-payments?add-edit'><i class='fa fa-plus'></i> New Payment</a>";
                                }
                                ?>
                            </div>

                        </div>
                    </div>


                </div>
                <!-- /.tab-content -->
            </div>
        </div>
        <div class="col-xs-4">
            <div class="alert bg-red-active">
                <span class="font-16"><i class="fa fa-warning"></i> Blind Allocation helps assign payments to loans. It's important to know all payment details and enter values accurately on the first try. Making multiple attempts with errors will be treated as fraud.</span>
            </div>
        </div>
    </div>
</section>
