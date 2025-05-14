<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}
/////---------End of session check
$proceed = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form data
    $expense_id = intval($_POST['expense_id']);

    $title = mysqli_real_escape_string($con, $_POST['expense_title']);
    $description = mysqli_real_escape_string($con, $_POST['expense_description']);
    $category = intval($_POST['expense_category']);
    $expense_date = mysqli_real_escape_string($con, $_POST['expense_date']);
    $branch_id = intval($_POST['expense_branch']);
    $product_id = intval($_POST['expense_product']);
    $amount = floatval($_POST['expense_amount']);
    $added_by = $userd['uid']; // Assuming this is set elsewhere, e.g., from session
   // $approved_by = intval($_POST['approved_by']); // Assuming this is set elsewhere
    //$added_date = mysqli_real_escape_string($con, $_POST['added_date']); // Assuming this is set elsewhere
    $recurring_expense = intval($_POST['expense_recurring']);
    $status = intval($_POST['expense_status']);

    ////---Permission
    if($expense_id > 0){
        $update_permission  = permission($userd['uid'],'o_expenses',"0","update_");
        if($update_permission == 0) {
            die(errormes("You don't have permission to update expense"));
            exit();
        }
    }
    else{

            $save_permission  = permission($userd['uid'],'o_expenses',"0","create_");
            if($save_permission == 0) {
                die(errormes("You don't have permission to add expense"));
                exit();
            }

    }

    ///////-------Validation
    if(input_length($title, 2) == 0){
        die(errormes("Title is too short"));
    }
    if($category == 0){
        die(errormes("Category is required"));
    }

    if(!validate_date($expense_date, 2)){
        die(errormes("Date is invalid"));
    }



    // Check if expense_id is greater than 0 to determine if it's an update or insert
    if ($expense_id > 0) {
        // Update existing record
        $update = updatedb("o_expenses","title = '$title', 
                  description = '$description', 
                  category = $category, 
                  expense_date = '$expense_date', 
                  branch_id = $branch_id, 
                  product_id = $product_id, 
                  amount = $amount,
                  recurring_expense = $recurring_expense, 
                  status = $status","uid = ".decurl($expense_id)."");
        if($update == 1){
            echo sucmes("Expense updated successfully");
            $proceed =  1;
        }
        else{
            echo errormes("Error updating expense");
        }
    } else {
        // Insert new record
        $flds = array('title', 'description', 'category', 'expense_date', 'branch_id', 'product_id', 'amount', 'added_by', 'approved_by', 'added_date', 'recurring_expense', 'status');
        $vals = array("$title", "$description", $category, "$expense_date", $branch_id, $product_id, $amount, $added_by, 0, "$fulldate", "$recurring_expense", $status);
        $save = addtodb('o_expenses', $flds, $vals);
        if($save == 1){
            echo sucmes("Expense added successfully");
            $proceed = 1;
        }
        else{
            echo errormes("Error adding expense");
        }

    }


    // Close the connection
    mysqli_close($con);
}
else{
    echo errormes("An error occurred while processing your request");
}
?>

<script>
    if ('<?php echo $proceed; ?>' === '1') {
        setTimeout(function() {
            reload();
        }, 2000);

    }
</script>
