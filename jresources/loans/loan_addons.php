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
$addons = array();
$product_addons = fetchtable('o_addons',"status = 1","uid","asc","100","uid, name");
while($pa = mysqli_fetch_array($product_addons)){
    $addons[$pa['uid']] = $pa['name'];
}
?>

<h4>APPLIED</h4>
    <table class="table-bordered font-14 table table-hover">
    <thead><tr><th>Name</th><th>Amount</th><th>Date Applied</th><th>Action</th></tr></thead>
    <tbody>
    <?php
    $added_addons = fetchtable('o_loan_addons',"loan_id='".decurl($loan_id)."' AND status in (1,2)","uid","asc","100","uid, addon_id, addon_amount, added_date, status");
    while ($ad = mysqli_fetch_array($added_addons)){
        $auid = $ad['uid'];
        $addon_id = $ad['addon_id'];
        $addon_amount = $ad['addon_amount'];
        $total_amount = $total_amount + $addon_amount;
        $added_date = $ad['added_date'];
        $status = $ad['status'];
        $addon_name = $addons[$addon_id];

        //$action = "<a class='text-orange btn' onclick=\"edit_applied_addon($auid);\"><i class='fa fa-edit'></i></a>";
        if($status == 1) {
            $action = "<a class='text-orange btn' onclick=\"edit_applied_addon($auid);\"><i class='fa fa-edit'></i></a>";
        }
        else{
            $action = "<a title='No action can be taken'><i>N/A</i></a>";
        }

        echo "<tr><td>$addon_name</td><td>".money($addon_amount)."</td><td>$added_date ".fancydate($added_date)."</td><td>".$action."</td></tr>";
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
<h5 class="text-orange font-12  font-bold"><i class="fa fa-info-circle"></i> Most AddOns are Applied automatically. Add them manually if they were missed</h5>
  <table class="table-bordered font-14 table table-hover">
    <?php
    $o_product_addons_ = fetchtable('o_product_addons',"product_id=".$loan_d['product_id']." AND status=1", "uid", "desc", "0,100", "uid ,addon_id ,date_added ");
    if((mysqli_num_rows($o_product_addons_)) == 0){
        echo "<tr><td colspan='4'>No Additional AddOns specified in settings</td> </tr>";
    }
    while($d = mysqli_fetch_array($o_product_addons_))
    {
        $uid = $d['uid'];
        $addon_id = $d['addon_id'];
        $date_added = $d['date_added'];

        $addon_d = fetchonerow('o_addons',"uid='$addon_id'","name, description,amount, amount_type, automatic");
        $addon_exists = checkrowexists('o_loan_addons',"loan_id='".decurl($loan_id)."' AND status=1 AND addon_id = '$addon_id'");

        if($addon_exists == 1){
            $act = "<td><span class=\"text-success\"><i class=\"fa fa-check\"></i> Added </span></td><td> <a onclick=\"loan_addon_action('REMOVE', '$loan_id', '$addon_id')\" class=\"btn btn-danger btn-sm  btn-md\"><i class=\"fa  fa-minus\"></i> Remove</a>";
        }
        else{
            $act = "<td><span class=\"text-danger\"><i class=\"fa fa-times\"></i> Not Added </span></td><td> <a onclick=\"loan_addon_action('ADD','$loan_id','$addon_id')\" class=\"btn btn-success btn-sm  btn-md\"><i class=\"fa  fa-plus\"></i> Add</a>";
        }


        echo "<tr><td>".$addon_d['name']."</td><td>".$addon_d['amount']."</td>$act </tr>";
    }
    include_once ("../../configs/close_connection.inc");
    ?>
  </table>
</div>

