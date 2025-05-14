<?php 

// include_once("../configs/conn.inc");
// include_once("../php_functions/functions.php");

$custq = "uid > 0 AND loan_limit > 100 AND status=1";

$customer_list = implode(',', table_to_array('o_customers',"$custq","10000000","uid"));
$loaned_cust = fetchtable("o_loans","customer_id in ($customer_list)", "uid", "DESC", 1000000,"customer_id, loan_amount, status");
$product_names = table_to_obj('o_loan_products',"uid > 0","1000","uid","name");
$customers = fetchtable("o_customers","$custq", "uid", "DESC", 1000000,"uid, full_name, primary_mobile, email_address, primary_product");

$all_data = array();

// re-organize customers list to objects and add loan_amount array;
while ($c = mysqli_fetch_assoc($customers)) {
    $key = $c["uid"];
    $cust_product_ = $c["primary_product"]; $cust_product = $product_names[$cust_product_];

    $all_data[$key] = array("full_name" => $c["full_name"], "primary_mobile" =>  $c["primary_mobile"], "email_address" =>  $c["email_address"], "primary_product" => $cust_product, "loans_amount" => array(), "loans_status" => array());
}

// update loans_amount based on key(customer id) matching
while($lc = mysqli_fetch_array($loaned_cust))
{
    $key = $lc["customer_id"];
    $value = $lc['loan_amount'];
    $value2 =$lc['status'];

    if (is_array($all_data[$key]['loans_amount']) && count($all_data[$key]['loans_amount']) < 3) {
        array_push($all_data[$key]['loans_amount'], $value);
        array_push($all_data[$key]['loans_status'], $value2);
    }   
}

function checkAllEqualToNum($array, $number) {
    foreach ($array as $item) {
        if ($item != $number) {
            return 0;
        }
    }
    return 1;
}

$filtered_data = array_filter($all_data, function ($item) {
    return count($item['loans_amount']) == 3 && count(array_unique($item['loans_amount'])) == 1 && checkAllEqualToNum($item['loans_status'], 5) == 1;
});


$entries = "";
if(count($filtered_data) > 0){
    foreach ($filtered_data as $key => $value) {
        $cust_id = $key;
        $cust_fullname = $value['full_name'];
        $cust_phone = $value['primary_mobile'];
        $cust_product = $value['primary_product'];
        $cust_email = $value['email_address'];
        $cust_amount = $value['loans_amount'][0];

        $entries .= "
                <tr>
                    <th>$cust_id</th>
                    <td>$cust_fullname</td>
                    <td>$cust_phone</td>
                    <td>$cust_email</td>
                    <td>$cust_product</td>
                    <td>$cust_amount</td>
                </tr>";

      }
}else {
    $entries .= "
        <tr>
            <i>No records found</i>
        </tr>";
}
  
?>

<table class="table table-condensed table-striped" id="example2">
    <thead>
    <tr><th>UID</th>
        <th>Full Name</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Product</th>
        <th>Amount</th>
    </tr>
    </thead>
    <tbody>
        <?php
            echo $entries
        ?>
    </tbody>
</table>