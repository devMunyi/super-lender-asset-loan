<div class="box" style="padding: 15px; border: 1px solid lightgrey">
    <div class="box-header">
        <i class="fa fa-calculator"></i>

        <h3 class="box-title">Loan Calculator</h3>
        <!-- tools box -->

        <!-- /. tools -->
    </div>
    <div class="box-body bg-gray-light">
            <table class="table table-bordered">
                <tr><td>Product:</td><td><select class="form-control" id="prod_calc" onchange="loan_calc();">
                                <option value="0">--Select One</option>
                            <?php
                            $o_loan_products_ = fetchtable('o_loan_products', "status=1", "uid", "desc", "0,10", "uid ,name ");
                            while ($y = mysqli_fetch_array($o_loan_products_)) {
                                $uid = $y['uid'];
                                $name = $y['name'];
                                echo "<option value='$uid'>$name</option>";

                            }
                            ?>
                        </select></td></tr>
                <tr><td>Amount</td><td><input type="number" onkeyup="loan_calc();" class="form-control" id="calc_amount"></td></tr>
            </table>
       <div class="card" id="calc_">

       </div>

    </div>
    <div class="box-footer clearfix">
        <button type="button" class="btn btn-block btn-lg btn-lg bg-blue-gradient" id="print">Print
            <i class="fa fa-print"></i></button>
    </div>
</div>