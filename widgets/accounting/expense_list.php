<?php
if(isset($_GET['start_date'])){
    $start_date = $_GET['start_date'];
}
if(isset($_GET['end_date'])){
    $end_date = $_GET['end_date'];
}

?>
<div class="row">
    <div class="col-sm-6">
<span class="font-24 ">Expense List</span>
    </div>
    <div class="col-sm-2"><input type="date" class="form-control" value="<?php echo $start_date; ?>" id="start_date" placeholder="Start Date" ></div> <div class="col-sm-2"><input class="form-control" type="date" id="end_date" placeholder="End Date" value="<?php echo $end_date; ?>"></div>
    <div class="col-sm-2"><button onclick="expense_filter()" class="btn btn-primary bg-blue-gradient form-control">GO</button></div>
    </div>
</div>
<div class="table-responsive">
    <table class="table table-bordered table-striped" id="example3">
        <thead>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Category</th>
            <th>Expense Date</th>
            <th>Branch</th>
            <th>Product</th>
            <th>Amount</th>
            <th>Recurring</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $total_amount = 0;
        $expenses_categories = table_to_obj('o_expense_categories',"status=1","1000","uid","name");
        $branches = table_to_obj('o_branches',"status=1","1000","uid","name");
        $loan_products = table_to_obj('o_loan_products',"status=1","1000","uid","name");

        $expenses = fetchtable('o_expenses',"status=1 AND expense_date BETWEEN '$start_date' AND '$end_date'","uid","asc","1000","uid, title, description, category, expense_date, branch_id, product_id, amount, added_by, recurring_expense");
        while($ex = mysqli_fetch_array($expenses)){
            $uid = $ex['uid']; $enc_uid = encurl($uid);
            $title = $ex['title'];
            $description = $ex['description'];
            $category = $ex['category'];  $category_name = $expenses_categories[$category];
            $expense_date = $ex['expense_date'];
            $branch_id = $ex['branch_id'];
               if($branch_id > 0){
                   $branch_name = $branches[$branch_id];
               }
               else{
                   $branch_name = "All";
               }
            $product_id = $ex['product_id'];
               if($product_id > 0){
                   $product_name = $loan_products[$product_id];
               }
               else{
                   $product_name = "All";
               }
            $amount = number_format($ex['amount']);
               $total_amount+=$ex['amount'];
            $recurring = $ex['recurring_expense']; $recurring_name = $expense_cat[$recurring];



            $action = "<a href='?view=$enc_uid' class='text-green'><i class='fa fa-eye'></i></a> | <a href='?edit=$enc_uid' class='text-red'><i class='fa fa-edit'></i></a> ";

            echo "<tr><td>$uid</td>
            <td>$description</td>
            <td>$category_name</td>
            <td>$expense_date</td>
            <td>$branch_name</td>
            <td>$product_name</td>
            <td>$amount</td>
            <td>$recurring_name</td>
            <td>$action</td></tr>";
        }
        ?>
        </tbody>
        <tfoot>
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Category</th>
            <th>Expense Date</th>
            <th>Branch</th>
            <th>Product</th>
            <th class="font-18 font-bold"><?php echo number_format($total_amount); ?></th>
            <th>Recurring</th>
            <th>Action</th>
        </tr>
        </tfoot>

    </table>
</div>