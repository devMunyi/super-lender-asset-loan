<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$loan_id = $_GET['loan_id'];
if($loan_id > 0){
    $loan_d = fetchonerow('o_loans',"uid='".decurl($loan_id)."'","product_id");
}
else{
    echo "<i>Loan ID is invalid</i>";
}
$deductions = array();
$product_deductions = fetchtable('o_deductions',"status = 1","uid","asc","100","uid, name");
while($pa = mysqli_fetch_assoc($product_deductions)){
    // echo json_encode($pa);
    $deductions[$pa['uid']] = $pa['name'];
}
?>

<h4>APPLIED</h4>
    <table class="table-bordered font-14 table table-hover">
    <thead><tr><th>Name</th><th>Amount</th><th>Date Applied</th><th>Action</th></tr></thead>
    <tbody>
    <?php
    $added_deductions = fetchtable('o_loan_deductions',"loan_id='".decurl($loan_id)."' AND status=1","uid","asc","100","uid, deduction_id, deduction_amount, added_date");
    while ($dd = mysqli_fetch_array($added_deductions)){
        $auid = $dd['uid'];
        $deduction_id = $dd['deduction_id'];
        $deduction_amount = $dd['deduction_amount'];
        $total_amount = $total_amount + $deduction_amount;
        $added_date = $dd['added_date'];
        $deduction_name = $deductions[$deduction_id];
        $action = "<a class='text-orange btn' onclick=\"edit_applied_deduction($auid);\"><i class='fa fa-edit'></i></a>";
        echo "<tr><td>$deduction_name</td><td>".money($deduction_amount)."</td><td>$added_date ".fancydate($added_date)."</td><td>".$action."</td></tr>";
    }

    ?>
    </tbody>
</table>
<?php
echo "<h4>Total: <b>".money($total_amount)."</b></h4>";
?>
<br/>
<div class="card card-body">
<h4>NOT APPLIED</h4>
<h5 class="text-orange font-12  font-bold"><i class="fa fa-info-circle"></i> Most Deductions are Applied automatically. Add them manually if they were missed</h5>
  <table class="table-bordered font-14 table table-hover">
    <?php
    $o_product_deductions_ = fetchtable('o_product_deductions',"product_id=".$loan_d['product_id']." AND status=1", "uid", "desc", "0,100", "uid ,deduction_id ,date_added ");
    if((mysqli_num_rows($o_product_deductions_)) == 0){
        echo "<tr><td colspan='4'>No Additional Deductions specified in settings</td> </tr>";
    }
    while($d = mysqli_fetch_array($o_product_deductions_))
    {
        $uid = $d['uid'];
        $deduction_id = $d['deduction_id'];
        $date_added = $d['date_added'];

        $deduction_d = fetchonerow('o_deductions',"uid='$deduction_id'","name, description,amount, amount_type, automatic");
        $deduction_exists = checkrowexists('o_loan_deductions',"loan_id='".decurl($loan_id)."' AND status=1 AND deduction_id = '$deduction_id'");
        if($deduction_exists == 1){
            $act = "<td><span class=\"text-success\"><i class=\"fa fa-check\"></i> Added </span></td><td> <a onclick=\"loan_deduction_action('REMOVE', '$loan_id', '$deduction_id')\" class=\"btn btn-danger btn-sm  btn-md\"><i class=\"fa  fa-minus\"></i> Remove</a>";
        }
        else{
            $act = "<td><span class=\"text-danger\"><i class=\"fa fa-times\"></i> Not Added </span></td><td> <a onclick=\"loan_deduction_action('ADD','$loan_id','$deduction_id')\" class=\"btn btn-success btn-sm  btn-md\"><i class=\"fa  fa-plus\"></i> Add</a>";
        }


        echo "<tr><td>".$deduction_d['name']."</td><td>".$deduction_d['amount']."</td>$act </tr>";
    }
    include_once ("../../configs/close_connection.inc");
    ?>
  </table>
</div>

