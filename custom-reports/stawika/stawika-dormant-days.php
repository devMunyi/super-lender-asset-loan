<?php
$all_loans_array = array();
$loan_due_dates_array = array();
$customers_array = array();
$latest_loan_amount = array();
$loan_customer_array = array();
$unpaid_array = array();
$latest_loan_id = array();
$customer_phones = array();
$customer_limits = array();
$latest_loan_status = array_push();
$customer_names = array();
$emails_array = array();
$products_array = array();

$product_names = table_to_obj('o_loan_products',"uid>0","100","uid","name");
////-----Get loans taken in particular period
$customers = fetchtable('o_customers',"loan_limit>=500","uid","asc","100000","uid, full_name, loan_limit, primary_product, primary_mobile, email_address");
while($c = mysqli_fetch_array($customers)){
    $uid = $c['uid'];
    $full_name = $c['full_name'];
    $loan_limit = $c['loan_limit'];
    $primary_mobile = $c['primary_mobile'];
    $primary_product = $c['primary_product'];

    $product_name = $product_names[$primary_product];
    array_push($customers_array, $uid);
    $customer_phones[$uid] = $primary_mobile;
    $customer_limits[$uid] = $loan_limit;
    $customer_names[$uid] = $full_name;
    $products_array[$uid] = $product_name;
    $emails_array[$uid] = $c['email_address'];
   // echo "Customer: $uid $full_name $loan_limit $primary_mobile <br/>";
}

$customers_list = implode(',', $customers_array);

//$limits_ = table_to_array('o_customer_limits',"given_date >= '2022-09-01 10:04:25'",100000, "customer_uid");
//var_dump($limits_);

///-------------------Customers with active loans
$customers_with_loans_array = array();
$loans = fetchtable('o_loans',"customer_id in ($customers_list) AND disbursed=1 AND paid=0 AND status!=0","uid","desc","10000","uid, customer_id");
while($l = mysqli_fetch_array($loans)){
     $customer_id = $l['customer_id'];
     array_push($customers_with_loans_array, $customer_id);

}

$last_loan_day = array();

$last_loans = fetchtable('o_loans',"customer_id in ($customers_list)  AND disbursed=1 AND paid=1 AND status!=0","uid","asc","100000000","customer_id, given_date, final_due_date");
while($ll = mysqli_fetch_array($last_loans)){
    $cus = $ll['customer_id'];
    $cus_given_date = $ll['given_date'];
    $cus_final_date = $ll['final_due_date'];
    $last_loan_day[$cus] = $cus_final_date;
}
?>
    <div class="col-sm-12">
        <table class="table table-striped table-bordered table-condensed" id="example2">
            <thead>
            <tr><th>cid</th><th>Name</th><th>Phone</th><th>Email</th><th>Product</th> <th>Limit</th><th>ago</th></tr>
            </thead>
            <tbody>
<?php

for($i = 0; $i <= sizeof($customers_array); ++$i){
    $cid = $customers_array[$i];
    if(in_array($customers_array[$i], $customers_with_loans_array)){

      // echo "Has Loan". $customers_array[$i]."<br/>";
    /*  $phone = $customer_phones[$customers_array[$i]];
      $limit = $customer_limits[$customers_array[$i]];

      if($limit <= 10000){
         $new_limit = $limit + 1000;
      }
      if($limit > 10000 && $limit <= 20000){
          $new_limit = $limit + 2000;
      }
      if($limit > 20000){
          $new_limit = $limit + 3000;
      }
   //  echo update_limit($customers_array[$i], $new_limit, "Added Manually in Bulk");

      echo "$phone, $limit , $new_limit <br/>"; */
    }
    else{

        $phone = $customer_phones[$customers_array[$i]];
        $limit = $customer_limits[$customers_array[$i]];
        $last_loan_day_ = $last_loan_day[$customers_array[$i]];
        $ago = datediff3($last_loan_day_, $date);
        $customer_name = $customer_names[$customers_array[$i]];
        $customer_email = $emails_array[$customers_array[$i]];
        $customer_idd = $customers_array[$i];
        $product = $products_array[$customer_idd];

      /*  if($ago >= 60){
            $upd = updatedb('o_customers',"primary_product=9","uid='$customer_idd'");
            if($upd == 1){
                store_event('o_customers',"$customer_idd","Product updated to product (9)");
            }
            echo "Update Product: $upd <br/>";
        } */
       echo "<tr><td>$cid</td><td>$customer_name</td><td>$phone</td><td>$customer_email</td><td>$product</td> <td>$limit</td><td>$ago</td></tr>";
       // echo "$customer_name, $phone, $limit, $ago <br/>";


    }
}
?>
            </tbody>
        </table>
