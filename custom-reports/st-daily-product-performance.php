<?php
session_start();
$_SESSION['db_name'] = 'stawika_db';
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");


$products = table_to_obj('o_loan_products',"status=1","100","uid","name");
$total_customers_per_product = array();
$main_product = array();
$salary_advance = array();
$nawiri_15 = array();
$women_empowerment = array();
$sme_loan = array();
$all_days = array();

$all_loans = fetchtable('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date'","uid","asc","1000000","distinct customer_id, product_id, loan_amount, given_date");
while($l = mysqli_fetch_array($all_loans)){
    $cust = $l['customer_id'];
    $product_id = $l['product_id'];
    $loan_amount = $l['loan_amount'];
    $given_date = $l['given_date'];

    if($product_id == 1){
        $main_product = obj_add($main_product, $given_date, $loan_amount);
    }
    if($product_id == 9){
        $nawiri_15 = obj_add($nawiri_15, $given_date, $loan_amount);
    }
    if($product_id == 5 || $product_id == 7 || $product_id == 8){
        $salary_advance = obj_add($salary_advance, $given_date, $loan_amount);
    }
    if($product_id == 10){
        $women_empowerment = obj_add($women_empowerment, $given_date, $loan_amount);
    }
    if($product_id == 11){
        $sme_loan = obj_add($sme_loan, $given_date, $loan_amount);
    }

if (!in_array($given_date, $all_days)) {
    array_push($all_days, $given_date);
}

}

?>
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
        <tr><th>#</th><th>Day</th><th>Main Product</th> <th>Nawiri na 15%</th> <th>Salary Advance</th><th>Women Empowerment</th><th>SME Loan</th><th>Total</th></tr>
        </thead>
        <tbody>
<?php
$nawiri_total = $main_total = $salary_total = $women_total = $sme_total = $total = 0;
for($i=0; $i<=sizeof($all_days); ++$i){
    $day = $all_days[$i];
    $main = false_zero($main_product[$day]);
    $nawiri_15_ = false_zero($nawiri_15[$day]);
    $salary = false_zero($salary_advance[$day]);
    $women = false_zero($women_empowerment[$day]);
    $sme = false_zero($sme_loan[$day]);

    $total = $main + $nawiri_15_ + $salary + $women + $sme;

    $main_total+=$main;
    $nawiri_total+=$nawiri_15_;
    $salary_total+=$salary;
    $women_total+=$women;
    $sme_total+=$sme;
    echo "<tr><td>$i</td><td>$day</td><td>".number_format($main)."</td><td>".number_format($nawiri_15_)."</td><td>".number_format($salary)."</td><td>".number_format($women)."</td><td>".number_format($sme)."</td><td>".number_format($total)."</td></tr>";
    $total = 0;

}



$all_total = $main_total+$nawiri_total+$salary_total+$women_total+$sme_total;


?>


        </tbody>
        <tfoot>
        <tr><th>#</th><th>Total</th><th><?php echo number_format($main_total); ?></th> <th><?php echo number_format($nawiri_total); ?></th> <th><?php echo number_format($salary_total); ?></th><th><?php echo number_format($women_total); ?></th><th><?php echo number_format($sme_total); ?></th><th><?php echo number_format($all_total); ?></th></tr>
        </tfoot>
    </table>
