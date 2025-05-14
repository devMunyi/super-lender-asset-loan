<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");



$customer_list_array = array();

///------Read all customers
$customer_loan_limits = array();
$customer_details_array = array();
$branches_array = table_to_obj('o_branches',"uid>0","1000","uid","name");
$all_active_customers = fetchtable('o_customers',"status=1 AND total_loans > 2 AND DATE(added_date) BETWEEN '$start_date' AND '$end_date' $andbranch_client","uid","desc","100000","uid, full_name, loan_limit, primary_mobile, DATE(added_date) as added_date, branch, total_loans");
while ($ac=mysqli_fetch_array($all_active_customers)){
    $uid = $ac['uid'];
    $full_name = $ac['full_name'];
    $loan_limit = $ac['loan_limit'];
    $primary_mobile = $ac['primary_mobile'];
    $added_date = $ac['added_date'];
    $branch = $ac['branch'];
    $total_loans = $ac['total_loans'];


    $details = array("full_name"=>$full_name,"loan_limit"=>$loan_limit,"primary_mobile"=>$primary_mobile,"added_date"=>$added_date,"branch"=>$branch, "added_date"=>$added_date, "total_loans"=>$total_loans);

    $customer_details_array[$uid] = $details;
   array_push($customer_list_array, $uid);
   $customer_loan_limits[$uid] = $loan_limit;
}
$customer_list = implode(',', $customer_list_array);
//echo $customer_list.'<br/>';


////---Borrowed at least 2 loans (Done)

/// ---Borrowed the last 42 days

$ago_42 = datesub($date,0,0, 42);
$borrowers_last_42 = table_to_array('o_loans',"disbursed=1 AND status!=0 AND given_date >= '$ago_42' AND customer_id in ($customer_list)","1000000","customer_id");
$customer_list2 = implode(',', $borrowers_last_42); ///Client who have borrowed last 42 days

//echo $customer_list2.'<br/>';

///----80% of collateral
$customer_collateral_array = array();
$collateral = fetchtable('o_collateral',"status=1 AND customer_id in ($customer_list2)","customer_id","asc","10000000","customer_id, money_value");
while($col = mysqli_fetch_array($collateral)){
    $customer_id = $col['customer_id'];
    $money_value = $col['money_value'];
    $customer_collateral_array = obj_add($customer_collateral_array, $customer_id, $money_value);
}
$customers_collateral_okey = array();
foreach ($customer_collateral_array as $customer => $collateral_value ){
 $current_limit = $customer_loan_limits[$customer];
 $affordability = $collateral_value*0.8;
 if($affordability >= $current_limit){
    array_push($customers_collateral_okey, $customer);
 }
}

$customer_list3 = implode(',', $customers_collateral_okey);  ////---List of customers with collateral more than 80% of limit
//echo $customer_list3.'<br/>';

$customers_with_receent_increments = array();
$limits = fetchtable('o_customer_limits',"customer_id in ($customer_list3) AND status=1 AND DATE(given_date) >= $ago_42","customer_uid","asc","10000000","customer_uid");
while($limit = mysqli_fetch_array($limits)){
    ////-----Customers with limits given last 42 days
    $customer_id = $limit['customer_uid'];
    array_push($customers_with_receent_increments, $customer_id);
}


// Query to select the latest 2 loans per customer based on given_date where disbursed=1 AND status!=0
$customer_loan1_details = array();
$customer_loan2_details = array();
$customer_count_array = array();
$loan_ago = datesub($start_date,0,4, 0);
$loans = fetchtable('o_loans',"customer_id in ($customer_list3) AND disbursed=1 AND status!=0 AND given_date >= '$loan_ago'","uid","desc","10000000","uid,customer_id, loan_amount, final_due_date, status,cleared_date");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $loan_amount = $l['loan_amount'];
    $final_due_date = $l['final_due_date'];
    $cleared_date = $l['cleared_date'];
    $customer_id = $l['customer_id'];
    $status = $l['status'];

    $customer_count_array = obj_add($customer_count_array, $customer_id, 1);
    if($customer_count_array[$customer_id] == 1){
        $customer_loan1_details[$customer_id] = array('uid'=>$uid, 'loan_amount'=>$loan_amount, 'final_due_date'=>$final_due_date, 'cleared_date'=>$cleared_date, 'status'=>$status);
    }
    if($customer_count_array[$customer_id] == 2){
        $customer_loan2_details[$customer_id] = array('uid'=>$uid, 'loan_amount'=>$loan_amount, 'final_due_date'=>$final_due_date, 'cleared_date'=>$cleared_date, 'status'=>$status);
    }
    //$customer_loan_details[]

   // echo "Customer: $customer_id - $loan_amount - $final_due_date - $cleared_date<br>";
}

$entries = "";
//$final_list_array = array();
for($i=0; $i<count($customers_collateral_okey); $i++){
    $customer_id = $customers_collateral_okey[$i];

    $loan_1_details = $customer_loan1_details[$customer_id];
    $loan_2_details = $customer_loan2_details[$customer_id];
    $customer_limit = $customer_loan_limits[$customer_id];

    $loan_1_amount = $loan_1_details['loan_amount'];
    $loan_1_final_due_date = $loan_1_details['final_due_date'];
    $loan_1_cleared_date = $loan_1_details['cleared_date'];
    $loan_1_status = $loan_1_details['status'];

    $loan_2_amount = $loan_2_details['loan_amount'];
    $loan_2_final_due_date = $loan_2_details['final_due_date'];
    $loan_2_cleared_date = $loan_2_details['cleared_date'];
    $loan_2_status = $loan_2_details['status'];

    ///----check if client has defaulted latest loan
    if($loan_2_status == 7 || $loan_2_status == 9 || $loan_2_status == 10 ){
        ////-----Defaulted lastest loan
        continue;
       }

/// ---defaulted last 2 loans, ignore
      if(date_greater($loan_1_cleared_date, $loan_1_final_due_date)==1 || (date_greater($loan_2_cleared_date, $loan_2_final_due_date)==1) && $loan_2_status != 3){
          ///----Defaulted last 2 loans
          continue;
      }

/// ---No limit increments last 2 loans, ignore
     if(in_array($customer_id, $customers_with_receent_increments)){
         continue;
     }

     $customer_details = $customer_details_array[$customer_id];
     $full_name = $customer_details['full_name'];
     $loan_limit = $customer_loan_limits[$customer_id];
     $primary_mobile = $customer_details['primary_mobile'];
     $branch_name = $branches_array[$customer_details['branch']];
     $added_date = $customer_details['added_date'];

     if($loan_limit < 5000){
         $recommended_limit = $loan_limit+3000;
     }
     else {
         $recommended_limit = $loan_limit + 5000;
     }
     $difference = $recommended_limit - $loan_limit;

    ////----Final list
    $entries.="<tr><td>$customer_id</td>
            <td>$full_name</td>
            <td>$primary_mobile</td>
            <td>$branch_name</td>
            <td>$added_date</td>
            <td>$total_loans</td>
            <td>$loan_limit</td>
            <td>$recommended_limit</td>
            <td>$difference</td></tr>";

    $loan_limit_total+=$loan_limit;
    $recommended_limit_total+=$recommended_limit;
    $difference_total+=$difference;

}
?>

    <table class="table table-condensed table-striped" id="example2">
        <thead>
        <tr>
            <th>UID</th>
            <th>Customer Name</th>
            <th>Phone</th>
            <th>Branch</th>
            <th>Join Date</th>
            <th>Total Loans</th>
            <th>Current Limit</th>
            <th>Recommended Limit</th>
            <th>Difference</th>

        </tr>
        </thead>
        <tbody>
        <?php
        echo $entries
        ?>
        </tbody>
        <tfoot>
        <tr>
            <th>UID</th>
            <th>Customer Name</th>
            <th>Phone</th>
            <th>Branch</th>
            <th>Join Date</th>
            <th>--</th>
            <th><?php echo number_format($loan_limit_total); ?></th>
            <th><?php echo number_format($recommended_limit_total); ?></th>
            <th><?php echo number_format($difference_total); ?></th>

        </tr>
        </tfoot>
    </table>


<?php
// Free the result set
mysqli_free_result($result);
/// ---No increment last 2 loans

///----Partial payer





