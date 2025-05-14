<?php
if(isset($_GET['type'])){
    $typ = $_GET['type'];
}

$branches_array = table_to_obj('o_branches', "uid>0", "1000", "uid", "name");
$staff_obj = table_to_obj('o_users',"uid>0","100000","uid","name");
$statuses = table_to_obj('o_customer_statuses',"uid > 0","100","code","name");

$branch_leads = array();
$leads = fetchtable('o_customers',"status in (3) $andbranch_client AND date(added_date) >= '$start_date' AND date(added_date) <= '$end_date'","uid","asc","1000000","uid, branch, added_date");
while($l = mysqli_fetch_array($leads)){
    $lid = $l['uid'];
    $branch = $l['branch'];
    $customer_id = $l['uid'];
    $added_date = $l['added_date'];
  //  $branch_leads = obj_add($branch_leads, $branch, $customer_id);
    array_push($branch_leads, $customer_id);


}


$customer_l = table_to_array('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date' AND disbursed=1 AND status!=0 $andbranch_loan","1000000","customer_id");
$customer_list = implode(',', $customer_l);
$customer_det = table_to_obj2('o_customers',"uid in ($customer_list)","100000","uid", array("total_loans", "branch"));


$new_branch_loans = array();
$new_branch_loans_sum = array();
$repeat_branch_loans = array();
$repeat_branch_loans_sum = array();
$active_custs = []; // store already iterated active customers
$active_custs_branches = []; // to store key value pair branch uid => customer uid


/////-------------------Loans taken previous month
$older_customer_loans = array();
$during_customer_loans = array();
$loans_older = fetchtable('o_loans',"given_date < '$start_date'  $andbranch_loan AND disbursed=1 AND status!=0 AND customer_id in ($customer_list)","uid","asc","1000000","uid, current_branch, current_lo,customer_id, loan_amount");
while($p = mysqli_fetch_array($loans_older)) {
    $loid = $p['uid'];
    $branch_l = $p['current_branch'];
    $customer_id = $p['customer_id'];
    $current_lo = $p['current_lo'];
    $older_customer_loans = obj_add($older_customer_loans, $customer_id, 1); ///Loans taken before this period selected

}


///------------End of loans taken before
///------------Loans taken during
$loans_during = fetchtable('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date'  $andbranch_loan AND disbursed=1 AND status!=0 AND customer_id in ($customer_list)","uid","asc","1000000","uid, current_branch, current_lo,customer_id, loan_amount");
while($d = mysqli_fetch_array($loans_during)) {
    $loid = $d['uid'];
    $branch_l = $d['current_branch'];
    $customer_id = $d['customer_id'];
    $current_lo = $d['current_lo'];
    $during_customer_loans = obj_add($during_customer_loans, $customer_id, 1); ///Loans taken during this period

}


///-------------End of loans taken during

$new_branch_loans_array = array();
$repeat_branch_loans_array = array();
$active_customers_array = array();
$loans = fetchtable('o_loans',"given_date >= '$start_date' AND given_date <= '$end_date' $andbranch_loan AND disbursed=1 AND status!=0","uid","asc","1000000","uid, current_branch, customer_id, loan_amount");
while($l = mysqli_fetch_array($loans)){
    $loid = $l['uid'];
    $branch_l = $l['current_branch'];
    $customer_id = $l['customer_id'];
    $loan_amount = $l['loan_amount'];
    $customer_info = $customer_det[$customer_id] ?? [];
    $customer_total_loans = $customer_info['total_loans'] ?? 0;
    $customer_branch = $customer_info['branch'] ?? 0;

    $customer_older_loans = $older_customer_loans[$customer_id];
    $customer_during_loans = $during_customer_loans[$customer_id];
    //$customer_total_loans = $customer_loans[$customer_id];
    $customer_total_loans = $customer_older_loans + $customer_during_loans;
    $customer_lo = $customer_branches[$customer_id];

    if($customer_during_loans > 0 && $customer_older_loans < 1){
        $new_branch_loans = obj_add($new_branch_loans, $branch_l, 1);
        array_push($new_branch_loans_array, $customer_id);
        $new_branch_loans_sum = obj_add($new_branch_loans_sum, $branch_l, $loan_amount);
    }
    else if($customer_during_loans > 0 && $customer_older_loans > 0){
        $repeat_branch_loans = obj_add($repeat_branch_loans, $branch_l, 1);
        array_push($repeat_branch_loans_array, $customer_id);
        $repeat_branch_loans_sum = obj_add($repeat_branch_loans_sum, $branch_l, $loan_amount);
    }

    // add active customer to array if not yet added
    if(!isset($active_custs[$customer_id])){
        $active_custs[$customer_id] = 1;
        $active_custs_branches = obj_add($active_custs_branches, $branch_l, 1);
        array_push($active_customers_array, $customer_id);
    }
}


if($typ == 'LEADS') {
    echo "<h3>Leads</h3>";
    $the_list = implode(',', $branch_leads);
}
else if($typ == 'NEW-CUSTOMERS'){
    echo "<h3>NEW CUSTOMERS</h3>";
    $the_list = implode(',', $new_branch_loans_array);
}
else if($typ == 'ACTIVE-CUSTOMERS'){
    echo "<h3>ACTIVE CUSTOMERS</h3>";
    $the_list = implode(',', $active_customers_array);
}

else if($typ == 'REPEAT-CUSTOMERS'){
    echo "<h3>REPEAT CUSTOMERS</h3>";
    $the_list = implode(',', $repeat_branch_loans_array);
}

//echo $the_list;
?>


<table class="table table-condensed table-striped" id="example2">
    <thead>
    <tr><th>UID</th>
        <th>Full Name</th>
        <th>Branch</th>
        <th>Phone</th>
        <th>Agent</th>
        <th>Added Date</th>
        <th>Status</th>
        <th>Action</th>
    </tr>
    </thead>
       <tbody>
       <?php



      //echo "<tr><td>$leads_list_str</td></tr>";
         $b_leads = fetchtable('o_customers',"uid in ($the_list)","uid","asc","1000000","uid, full_name,branch, primary_mobile, date(added_date) as added_date, status, added_by");
         while($b = mysqli_fetch_array($b_leads)) {
             $buid = $b['uid'];
             $bname = $b['full_name'];
             $branch = $b['branch'];
             $branch_name = $branches_array[$branch];
             $primary_mobile = $b['primary_mobile'];
             $added_by = $b['added_by'];
             $date_added = $b['added_date'];
             $status = $b['status'];
             $agent_name = $staff_obj[$added_by];
             $status_name = $statuses[$status];

             $act = "<a href=\"#\" title='Popup' onclick=\"interactions_popup('" . encurl($buid) . "')\"><i class=\"fa fa-comments-o text-orange\"></i></a>";

             echo " <tr><td>$buid</td>
        <td>$bname</td>
        <td>$branch_name</td>
        <td>$primary_mobile</td>
        <td>$agent_name</td>     
        <td>$date_added</td>
        <td>$status_name</td>
        <td>$act</td>
    </tr>";


         }
       ?>

       </tbody>

    <tfoot>
    <tr><th>UID</th>
        <th>Full Name</th>
        <th>Branch</th>
        <th>Phone</th>
        <th>Agent</th>
        <th>Added Date</th>
        <th>Status</th>
        <th>Action</th>

    </tr>
    </tfoot>

</table>


