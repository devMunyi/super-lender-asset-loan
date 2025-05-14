<?php
$eid = 0;
if(isset($_GET['add'])) {
    echo "<h3>Add Expense</h3>";
}
if(isset($_GET['edit'])) {
    echo "<h3>Edit Expense</h3>";
    $eid = $_GET['edit'];
    $exp = fetchonerow('o_expenses',"uid='".decurl($eid)."'");

}


?>

<div class="row">
    <div class="col-sm-3"></div>
    <div class="col-sm-6">
        <form class="form-horizontal" id="expense-save" method="POST" action="action/expenses/save-expense" enctype="multipart/form-data">
    <!-- Title Field -->
    <div class="form-group row">
        <label for="title" class="col-sm-2 col-form-label">Title</label>
        <div class="col-sm-10">
        <input type="hidden" id="expense_id" value="<?php echo $eid; ?>" name="expense_id">    <input type="text" class="form-control" id="title" value="<?php echo $exp['title']; ?>" placeholder="Enter title" name="expense_title">
        </div>
    </div>

    <!-- Description Field -->
    <div class="form-group row">
        <label for="description" class="col-sm-2 col-form-label">Description</label>
        <div class="col-sm-10">
            <textarea class="form-control"  name="expense_description" id="description" rows="3" placeholder="Enter description"><?php echo $exp['description']; ?></textarea>
        </div>
    </div>

    <!-- Category Field -->
    <div class="form-group row">
        <label for="category" class="col-sm-2 col-form-label">Category</label>
        <div class="col-sm-10">
            <select class="form-control" id="category" name="expense_category">
                <option value="0">--Select Category</option>
                <?php

                $expense_categories = fetchtable('o_expense_categories', "status=1", "name", "asc", "100", "uid, name");
                while ($b = mysqli_fetch_array($expense_categories)) {
                    $bid = $b['uid'];
                    $bname = $b['name'];
                    if($bid == $exp['category']) {
                        $selected = "selected";
                    }
                    else{
                        $selected = "";
                    }
                    echo "<option $selected value='$bid'>$bname</option>";
                }

                ?>
            </select>
        </div>
    </div>

    <!-- Expense Date Field -->
    <div class="form-group row">
        <label for="expenseDate" class="col-sm-2 col-form-label">Expense Date</label>
        <div class="col-sm-10">
            <input type="date" value="<?php echo   $exp['expense_date'];  ?>" class="form-control" name="expense_date" id="expenseDate">
        </div>
    </div>

    <!-- Branch Field -->
    <div class="form-group row">
        <label for="branch" class="col-sm-2 col-form-label">Branch</label>
        <div class="col-sm-10">
            <select class="form-control" id="branch" name="expense_branch">
                <option value="0">--All Branches</option>
                <?php
                $branches = fetchtable('o_branches',"status=1","name","asc","1000","uid, name");
                while($b = mysqli_fetch_array($branches)){
                    $bid = $b['uid'];
                    $bname = $b['name'];

                    if($bid == $exp['branch_id']) {
                        $selected_b = "selected";
                    }
                    else{
                        $selected_b = "";
                    }

                    echo "<option $selected_b value='$bid'>$bname</option>";
                }
                ?>

            </select>
        </div>
    </div>

    <!-- Product Field -->
    <div class="form-group row">
        <label for="product" class="col-sm-2 col-form-label">Product</label>
        <div class="col-sm-10">
            <select class="form-control" id="product" name="expense_product">
                <option value="0">All Products</option>
                <?php
                $loan_products = fetchtable('o_loan_products',"status=1","name","asc","100","uid, name");
                while($b = mysqli_fetch_array($loan_products)){
                    $bid = $b['uid'];
                    $bname = $b['name'];
                    if($bid == $exp['product_id']) {
                        $selected_p = "selected";
                    }
                    else{
                        $selected_p = "";
                    }
                    echo "<option $selected_p value='$bid'>$bname</option>";
                }
                ?>
            </select>
        </div>
    </div>

    <!-- Amount Field -->
    <div class="form-group row">
        <label for="amount" class="col-sm-2 col-form-label">Amount</label>
        <div class="col-sm-10">
            <input type="number" class="form-control" value="<?php echo $exp['product_id']; ?>" name="expense_amount" id="amount" placeholder="Enter amount">
        </div>
    </div>

    <!-- Recurring Field -->
    <div class="form-group row">
        <label for="recurring" class="col-sm-2 col-form-label">Recurring?</label>
        <div class="col-sm-10">
            <select class="form-control" name="expense_recurring" id="recurring">
                <?php
                /// write a foreach statement to loop through the items above
                foreach ($expense_cat as $key => $value) {
                    if($exp['recurring'] == $key) {
                        $selected_r = "selected";
                    }
                    else{
                        $selected_r = "";
                    }

                   echo "<option $selected_r value='$key'>$value</option>";
                }
                ?>
            </select>
        </div>
    </div>

    <!-- Status Field -->
    <div class="form-group row">
        <label for="status" class="col-sm-2 col-form-label">Status</label>
        <div class="col-sm-10">
            <select class="form-control" name="expense_status" id="status">
                <option value="1">Active</option>
                <option value="2">Draft</option>
            </select>
        </div>
    </div>

    <!-- Submit Button -->
        <div class="form-group row">
            <div class="col-sm-12">
                <div class="prgress">
                    <div class="messageexpense-save" id="message"></div>
                    <div class="progressexpense-save" id="progress">
                        <div class="bardo-upload" id="bar"></div>
                        <br/>
                        <div style="display: none;" class="percentexpense-save" id="percent"></div>
                    </div>
                </div>
            </div>
            <div for="status" class="col-sm-2"></div>
            <div class="col-sm-5">
                <button type="submit" class="btn btn-success btn-lg pull-right" onclick="formready('expense-save');">Submit </button>
            </div>
            <div class="col-sm-5">
               <button type="reset" class="btn btn-default btn-block">Reset</button>
            </div>
        </div>
        </form>
</div>
    <div class="col-sm-3"></div>
</div>

