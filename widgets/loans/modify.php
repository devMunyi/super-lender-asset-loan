<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
/////----------Session Check
$loan_id = $_POST['loan_id'];
$loan_d = fetchonerow('o_loans',"uid='".decurl($loan_id)."'");
$current_lo = $loan_d['current_lo'];
$current_co = $loan_d['current_co'];
$given_date = $loan_d['given_date'];
$next_due_date = $loan_d['next_due_date'];
$final_due_date = $loan_d['final_due_date'];
$income_earned = $loan_d['income_earned'];
$loan_amount = $loan_d['loan_amount'];
$disbursed_amount = $loan_d['disbursed_amount'];
$product_id = $loan_d['product_id'];

$current_branch = $loan_d['current_branch'];
$current_group = $loan_d['group_id'];
$disbursed = $loan_d['disbursed'];
$paid = $loan_d['paid'];
$userd = session_details();
if ($userd == null) {
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

?>
<form class="form-horizontal" onsubmit="return false;" method="post">
    <div class="box-body">
        <div class="form-group">
            <input type="hidden" id="lid" value="<?php echo $loan_id; ?>">
            <label for="loan_amount" class="col-sm-3 control-label">Loan Amount</label>

            <div class="col-sm-9">
                <input type="number" class="form-control"  id="loan_amount" value="<?php echo $loan_amount; ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="disbursed_amount"  class="col-sm-3 control-label">Disbursed Amount</label>

            <div class="col-sm-9">
                <input type="number" class="form-control"  id="disbursed_amount" value="<?php echo $disbursed_amount ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="disbursed_date" class="col-sm-3 control-label">Disbursed Date</label>

            <div class="col-sm-9">
                <input type="date" class="form-control" value="<?php echo $given_date; ?>"  id="disbursed_date" placeholder="Preferably a work email">
            </div>
        </div>
        <div class="form-group">
            <label for="n" class="col-sm-3 control-label">Next Due Date</label>

            <div class="col-sm-9">
                <input type="date" class="form-control" value="<?php echo $next_due_date; ?>" id="next_due_date">
            </div>
        </div>
        <div class="form-group">
            <label for="final_due_date" class="col-sm-3 control-label">Final Due Date</label>

            <div class="col-sm-9">
                <input type="date" class="form-control" value="<?php echo $final_due_date; ?>"  id="final_due_date">
            </div>
        </div>
        <div class="form-group">
            <label for="loan_prod" class="col-sm-3 control-label">Loan Product</label>

            <div class="col-sm-9">
                <select class="form-control" id="loan_prod">
                    <option value="0">--Unspecified</option>
                    <?php

                    $prod = fetchtable('o_loan_products',"status=1","name","asc","1000","uid, name");
                    while($p = mysqli_fetch_array($prod)){
                        $uid = $p['uid'];
                        $name = $p['name'];

                        if($product_id == $uid){
                            $selectedp = "SELECTED";
                        }
                        else{
                            $selectedp = "";
                        }

                        echo "<option $selectedp value='$uid'> $name</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="email_" class="col-sm-3 control-label">Current LO</label>

            <div class="col-sm-9">
                <select class="form-control" id="current_lo">
                    <option value="0">--Unspecified</option>
                    <?php

                    $users = fetchtable('o_users',"status=1","name","asc","100000","uid, name, email");
                    while($u = mysqli_fetch_array($users)){
                       $uid = $u['uid'];
                       $name = $u['name'];
                       $email = $u['email'];

                       if($current_lo == $uid){
                           $selected = "SELECTED";
                       }
                       else{
                           $selected = "";
                       }

                       echo "<option $selected value='$uid'> $name [$email]</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="email_" class="col-sm-3 control-label">Current CO</label>

            <div class="col-sm-9">
                <select class="form-control" id="current_co">
                    <option value="0">--Unspecified</option>
                    <?php

                    $users = fetchtable('o_users',"status=1","name","asc","100000","uid, name, email");
                    while($u = mysqli_fetch_array($users)){
                        $uid = $u['uid'];
                        $name = $u['name'];
                        $email = $u['email'];

                        if($current_co == $uid){
                            $selected = "SELECTED";
                        }
                        else{
                            $selected = "";
                        }

                        echo "<option $selected value='$uid'> $name [$email]</option>";
                    }
                    ?>

                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="email_" class="col-sm-3 control-label">Current Branch</label>

            <div class="col-sm-9">
                <select class="form-control" id="current_branch">
                    <option value="0">--Select One</option>
                    <?php
                    $o_branches_ = fetchtable('o_branches', "status=1", "uid", "desc", "0,1000", "uid ,name ");
                    while ($u = mysqli_fetch_array($o_branches_)) {
                        $uid = $u['uid'];
                        $name = $u['name'];
                        if($current_branch == $uid){
                            $selected_br = 'SELECTED';
                        }else{
                            $selected_br = '';
                        }
                        echo "<option $selected_br value='$uid'>$name</option>";
                    }

                    ?>

                </select>
            </div>
        </div>
        <?php
        if($group_loans == 1){
             $grou = 'All';
        }
        else{
            $grou = 'None';
        }
        ?>
        <div class="form-group" style="display:<?php echo $grou; ?> ;">
            <label for="group_" class="col-sm-3 control-label">Current Group</label>

            <div class="col-sm-9">
                <select class="form-control" id="group_">
                    <option value="0">--None</option>
                    <?php
                    $groups = fetchtable('o_customer_groups', "status=1", "uid", "asc", "0,1000", "uid ,group_name");
                    while ($g = mysqli_fetch_array($groups)) {
                        $uid = $g['uid'];
                        $name = $g['group_name'];
                        if($current_group == $uid){
                            $selected_gr = 'SELECTED';
                        }else{
                            $selected_gr = '';
                        }
                        echo "<option $selected_gr value='$uid'>$name</option>";
                    }

                    ?>

                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="email_" class="col-sm-3 control-label">Disbursed Already</label>

            <div class="col-sm-9">
                <label> <input type="radio" class="" name="disbursed_already"> Yes </label> --
                <label> <input type="radio" class="" name="disbursed_already"> No </label>
            </div>
        </div>
        <div class="form-group">
            <label for="email_" class="col-sm-3 control-label">Paid Already</label>

            <div class="col-sm-9">
                <label> <input type="radio" class="" name="paid_already"> Yes </label> --
                <label> <input type="radio" class="" name="paid_already"> No </label>
            </div>
        </div>
        <div class="form-group">
            <label for="disbursed_amount" class="col-sm-3 control-label">Income Earned</label>

            <div class="col-sm-9">
                <input type="number" class="form-control" value="<?php echo $income_earned; ?>"  id="income_earned">
            </div>
        </div>
        <div class="form-group">
             <div class="col-sm-9">
                <button class="btn btn-success" onclick="update_loan('<?php echo $loan_id; ?>');"> Save </button>
            </div>
        </div>

    </div>
</form>


