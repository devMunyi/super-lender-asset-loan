<?php
$title = "Product Performance Summaries";
// echo "<h4>Product Performance</h4>";
echo "<table class='table table-bordered grid-width-50 col-lg-6'>";
echo "<thead><tr><th>Product</th><th>Disbursement</th><th>Collections</th></tr></thead>";

$products = table_to_obj('o_loan_products',"status=1","1000", "uid","name");
foreach($products as $pid => $pname) {
    $product_total = $product_disb_array[$pid];
    $collection_product = $product_coll_array[$pid];
    $prod_total_total = $prod_total_total + $product_total;
    $coll_total_total = $coll_total_total + $collection_product;
    echo "<tr><td>$pname</td><td>".money($product_total)."</td><td>".money($collection_product)."</td></tr>";
}



echo "<thead><tr><th>Total</th><th>".money($prod_total_total)."</th><th>".money($coll_total_total)."</th></tr></thead>";
echo "</table>";
