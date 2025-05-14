<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$product = $_GET['prod'];
$amount = $_GET['amount'];

$prod = fetchonerow('o_loan_products',"uid='$product'");
$description = $prod['description'];
$period = $prod['period'];
$period_units = $prod['period_units'];
$min_amount = $prod['min_amount'];
$max_amount = $prod['max_amount'];
$pay_frequency = $prod['pay_frequency'];
if($pay_frequency > 0){
    $pay_freq = "Every $pay_frequency Days";
}
else{
    $pay_freq = "Any";
}


?>
<div class="alert alert-link text-orange"><i class="fa fa-info"></i> About Product:  <?php echo $description; ?></div>
<table class="table table-striped font-14">
    <tr><td>Period:</td><td class="text-bold"><?php echo $period."(X$period_units days)"; ?></td></tr>
    <tr><td>Minimum Amount:</td><td class="text-bold"> <?php echo money($min_amount); ?></td></tr>
    <tr><td>Maximum Amount:</td><td class="text-bold"> <?php echo money($max_amount); ?></td></tr>
    <tr><td>Pay Frequency:</td><td class="text-bold"><?php echo $pay_freq; ?></td></tr>
</table>
<h4>Addons</h4>
<table class="table table-striped table-bordered font-14">
    <?php
    $addons_array = array();
    $addons = table_to_array('o_product_addons',"product_id='$product' AND status=1","1000","addon_id");
    $addon_str = implode(',', $addons);

    $addon_total = 0;
    $add_det = fetchtable('o_addons',"uid in ($addon_str) AND status=1","uid","asc","1000");
    while($a = mysqli_fetch_array($add_det)){
        $auid = $a['uid'];
        $aname = $a['name'];
        $adescription = $a['description'];
        $aamount = $a['amount'];
        $aamount_type = $a['amount_type'];
        $aloan_stage = $a['loan_stage'];
        $applicable_loan = $a['applicable_loan'];
        $paid_upfront = $a['paid_upfront'];
        $addon_on = $a['addon_on'];
        $from_day = $a['from_day'];
        $to_day = $a['to_day'];

          if($aamount_type == 'PERCENTAGE'){
            if($addon_on == 'loan_amount'){
                $addon_amount = $amount * ($aamount /100);
                $addon_amount = "$addon_amount";
                $addon_total = $addon_total + $addon_amount;

            }
            else{
                $addon_amount = $aamount."% of $addon_on";
            }
        }
        else{
            $addon_amount = $aamount;
        }
        $day_applicable = "";
        if($from_day > 0){
           $day_applicable = "Applied from day $from_day";
        }
        if($to_day > 0 AND $to_day > $from_day){
            $day_applicable.=" To day $to_day";
        }


        echo "<tr><td>$aname</td><td> <b> $addon_amount </b> <i>$day_applicable</i></td></tr>";

    }
    include_once ("../../configs/close_connection.inc");

    ?>



</table>

<h4>Deductions</h4>
<table class="table table-striped font-14">

    <tr><td colspan="2"><i>None specified</i></td></tr>

</table>

