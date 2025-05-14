<h3>Expense View</h3>
<?php
if(isset($_GET['view'])) {
    $eid = $_GET['view'];
    $ex = fetchonerow('o_expenses',"uid='".decurl($eid)."'");
}

$expenses_categories = table_to_obj('o_expense_categories',"status=1","1000","uid","name");
$branches = table_to_obj('o_branches',"status=1","1000","uid","name");
$loan_products = table_to_obj('o_loan_products',"status=1","1000","uid","name");


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
    $amount = $ex['amount'];
    $recurring = $ex['recurring_expense']; $recurring_name = $expense_cat[$recurring];



    $action = "<a href='?edit=$eid' class='text-red'><i class='fa fa-edit'></i></a>";

echo "<table class='tablex'>";
echo "  <thead>";
echo "    <tr>";
echo "      <th>Item</th>";
echo "      <th>Value</th>";
echo "    </tr>";
echo "  </thead>";
echo "  <tbody>";
echo "    <tr>";
echo "      <td>UID</td>";
echo "      <td>$eid</td>";
echo "    </tr>";
echo "    <tr>";
echo "      <td>Title</td>";
echo "      <td>$title</td>";
echo "    </tr>";
echo "    <tr>";
echo "      <td>Description</td>";
echo "      <td>$description</td>";
echo "    </tr>";
echo "    <tr>";
echo "      <td>Category</td>";
echo "      <td>$category_name</td>";
echo "    </tr>";
echo "    <tr>";
echo "      <td>Expense Date</td>";
echo "      <td>$expense_date</td>";
echo "    </tr>";
echo "    <tr>";
echo "      <td>Branch</td>";
echo "      <td>$branch_name</td>";
echo "    </tr>";
echo "    <tr>";
echo "      <td>Product</td>";
echo "      <td>$product_name</td>";
echo "    </tr>";
echo "    <tr>";
echo "      <td>Amount</td>";
echo "      <td>$amount</td>";
echo "    </tr>";
echo "    <tr>";
echo "      <td>Recurring</td>";
echo "      <td>$recurring_name</td>";
echo "    </tr>";
echo "    <tr>";
echo "      <td>Action</td>";
echo "      <td>$action</td>";
echo "    </tr>";
echo "  </tbody>";
echo "</table>";

?>



