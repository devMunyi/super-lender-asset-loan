<?php
session_start();
include_once("../php_functions/functions.php");
include_once("../configs/conn.inc");


/////----------Session Check
$userd = session_details();
if ($userd == null) {
    exit(errormes("Your session is invalid. Please re-login"));
}

$create_loan = permission($userd['uid'], 'o_loans', "0", "create_");

/////--------This new code block will check if this user is a CO without a pair and give
///  them temporary rights to create a loan

if($userd['user_group'] == 8){
    $create_loan = 0;
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
///--------End of new code block to give CO temporary rights of LO


if ($create_loan == 1) {
    $customer_id = intval(decurl($_POST['customer_id']));
    if ($customer_id  > 0) {
        $cust = fetchonerow('o_customers', "uid='$customer_id'", "uid, national_id, full_name, primary_product");
        $client_details = $cust['full_name'] . ' (ID: ' . $cust['national_id'] . ')';
        $client_id = $cust['uid'];
        $product = $cust['primary_product'];
    }



    if ($customer_id > 0) {
?>
        <form class="form-horizontal" autocomplete="off" onsubmit="return false;" method="post">
            <div class="box-body">
                <div class="form-group">
                    <label for="customer" class="col-sm-3 control-label">Customer</label>

                    <div class="col-sm-9">
                        <input class="form-control" type="text" autocomplete="off" onkeyup="search_cust();" id="customer_search" value="<?php echo $client_details; ?>" placeholder="Start typing customer name ...">
                        <input type="hidden" id="customer_id_" value="<?php echo $customer_id; ?>">
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
                            $o_loan_products_ = fetchtable('o_loan_products', "status=1", "name", "asc", "0,100", "uid ,name ,description ");
                            while ($o = mysqli_fetch_array($o_loan_products_)) {
                                $uid = $o['uid'];
                                $name = $o['name'];
                                $description = $o['description'];

                                if ($product == $uid) {
                                    $selected_l = 'SELECTED';
                                } else {
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
                            <?php

                            $o_loan_types = fetchtable('o_loan_types', "status=1", "name", "asc", "0,10", "uid, icon ,name");
                            while ($t = mysqli_fetch_array($o_loan_types)) {
                                $uid = $t['uid'];
                                $name = $t['name'];
                                $icon = $t['icon'];

                                if($default_loan_type == $uid){
                                    $selected = 'SELECTED';
                                }
                                else{
                                    $selected = '';
                                }

                                echo "<option $selected value='$uid'>$name</option>";
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

                <div class="form-group" id="adv" style="display: none;">
                    <label for="other_" class="col-sm-3 control-label"><a onclick="showhide('#adv_field','#adv')">Advanced</a></label>
                </div>
                <div class="form-group" style="display: none" id="adv_field">
                    <label for="other_" class="col-sm-3 control-label">Period</label>

                    <div class="col-sm-4">
                        <input type="number" class="form-control" placeholder="Period">
                    </div>
                    <div class="col-sm-4">
                        <select class="form-control">
                            <option value="0">Select</option>
                            <option value="DAYS">Days</option>
                            <option value="MONTHS">Months</option>
                            <option value="YEARS">Years</option>

                        </select>
                    </div>
                </div>

                <div class="col-sm-3"></div>
                <div class="col-sm-9">
                    <div class="box-footer">
                        <br />
                        <button type="submit" class="btn btn-lg btn-default">Cancel</button>
                        <button type="submit" class="btn btn-success bg-green-gradient btn-lg pull-right" onclick="create_loan();">
                            Create
                        </button>
                    </div>
                </div>

            </div>
            <!-- /.box-body -->

            <!-- /.box-footer -->
        </form>
    <?php
    }

    ?>
<?php
} else {
    echo errormes("You don't have permission to view this page");
}
?>