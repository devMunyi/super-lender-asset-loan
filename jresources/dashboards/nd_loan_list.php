<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../php_functions/secondary-functions.php");
include_once ("../../configs/conn.inc");

$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];
$obj = $_GET['obj'] ?? "BRANCH";

$userd = session_details();
$loan_branches = branch_permissions($userd, 'o_loans');
$branches_list = branch_permissions($userd, 'o_branches');
$pay_branches = branch_permissions($userd, 'o_incoming_payments');
$customer_branches = $user_branches = branch_permissions($userd, 'o_customers');

$obj_array = array();
if($obj == 'BRANCH'){
    $obj_array = table_to_obj('o_branches',"status=1 $branches_list","1000","uid","name");
}
elseif ($obj == 'PRODUCT'){
    $obj_array = table_to_obj('o_loan_products',"status=1","1000","uid","name");
}
elseif ($obj == 'AGENT'){
    $obj_array = table_to_obj('o_users',"status=1 AND user_group in (7,8) $user_branches","1000","uid","name");
}
elseif ($obj == 'GROUP'){
    $obj_array = table_to_obj('o_customer_groups',"status=1","1000","uid","group_name");
}
elseif ($obj == 'MONTHLY'){
    //$obj_array = array();
}
elseif ($obj == 'WEEKLY'){
    //$obj_array = array();
}
elseif ($obj == 'DAILY'){
    //$obj_array = array();
}
else{
    $obj_array = table_to_obj('o_branches',"status=1 $branches_list","1000","uid","name");
}






$loan_q = "status!=0 AND given_date >= '$start_date' AND given_date <= '$end_date' AND disbursed=1 $loan_branches";
$loan_array = table_to_array('o_loans', "$loan_q", "100000000", "uid");



/////----Per branch, Per Product, Per Agent, Per Group, Per Month, Per Week, Per Day
///-----------Loans during
///
$principal_array = array();
$interest_array = array();
$other_charges_array = array();
$total_repaid_array = array();
$total_repayable_array = array();
$loan_balance_array = array();
$principal_paid_array = array();
$interest_paid_array = array();
$other_charges_paid_array = array();
$total_balance_array = array();
$overdues_array = array();
$total_overdue_balance_array = array();
$npl_array = array();

$overdue_balance = 0;
$loan_list = implode(',', $loan_array);
$addons = loan_addons_array($loan_array);
$loan_interest_array = $addons[0];
$loan_other_charges_array = $addons[1];
$loans = fetchtable('o_loans',"uid in ($loan_list)","uid","asc","1000000000","uid, given_date, customer_id,loan_amount, product_id ,status, total_repaid, current_branch ,loan_balance, paid, total_repayable_amount, current_lo, current_co,group_id, group_id, loan_balance, status");
while($l = mysqli_fetch_array($loans)) {
    $luid = $l['uid'];
    $given_date = $l['given_date'];
    $status = $l['status'];
    $principal = $l['loan_amount'];
    $total_repaid = $l['total_repaid'];
    $loan_balance = $l['loan_balance'];
    $current_branch = $l['current_branch'];
    $product_id = $l['product_id'];
    $current_lo = $l['current_lo'];
    $current_co = $l['current_co'];
    $group_id = $l['group_id'];

    if($status == 7){
        $overdue_balance = $l['loan_amount'];
    }

    $gd = explode('-', $given_date);
    $ym = $gd[0].'-'.$gd[1];
    $week_of_month = getWeekOfMonth($given_date);
    $week = $gd[0].'-'.$gd[1].'-W'.$week_of_month;
    $interest = $loan_interest_array[$luid];
    $other_charges = $loan_other_charges_array[$luid];


    $total_repayable = $l['total_repayable_amount'];
    $total_repaid = $l['total_repaid'];
    $pi_repaid = false_zero($total_repaid - $other_charges);
    $principal_paid = false_zero($pi_repaid - $interest);
    $interest_paid = false_zero($pi_repaid - $principal_paid);
    $other_charges_paid = false_zero($total_repaid - $pi_repaid);

    $disbursed = $l['disbursed'];
    $customer_id = $l['customer_id'];
    $paid = $l['paid'];

    $obj_id = 0;  ///// We are attempting to crunch all summaries in one go without repeating
    if($obj == 'BRANCH'){
        $obj_id = $current_branch;
    }
    elseif ($obj == 'PRODUCT'){
        $obj_id = $product_id;
    }
    elseif ($obj == 'AGENT'){
        $obj_id = $current_lo;
    }
    elseif ($obj == 'GROUP'){
        $obj_id = $group_id;
    }
    elseif ($obj == 'MONTHLY'){
        $obj_id = $ym;
        $obj_array[$ym] = $ym;
    }
    elseif ($obj == 'WEEKLY'){
        $obj_id = $week;
        $obj_array[$week] = $week;
    }
    elseif ($obj == 'DAILY'){
        $obj_id = $given_date;
        $obj_array[$given_date] = $given_date;
    }
    else{
        echo errormes("Select Category above");
    }


   // $principal_array[$obj_id] = $principal;
    $principal_array = obj_add($principal_array, $obj_id, $principal);
    $total_repaid_array = obj_add($total_repaid_array, $obj_id, $total_repaid);
    $total_balance_array = obj_add($total_balance_array, $obj_id, $loan_balance);
    $total_overdue_balance_array = obj_add($total_overdue_balance_array, $obj_id, $overdue_balance);
    $interest_array = obj_add($interest_array, $obj_id, $interest);
    $other_charges_array = obj_add($other_charges_array,$obj_id, $other_charges);
    $total_repayable_array = obj_add($total_repayable_array, $obj_id, $total_repayable);
    $interest_paid_array = obj_add($interest_paid_array, $obj_id, $interest_paid);
    $principal_paid_array = obj_add($principal_paid_array, $obj_id, $principal_paid);
    $other_charges_paid_array = obj_add($other_charges_paid_array, $obj_id, $other_charges_paid);

}

?>

<table class="tablex">
    <thead class="bg-gray font-bold">
    <tr><th><?php echo $obj; ?></th><th>Principle</th><th>Interest</th><th>Other Charges</th><th>Total Repayable</th><th>Princ. Paid </th><th>Int. Paid</th><th>P+I Paid</th><th>Charges Paid</th><th>Total Paid</th><th>Total Bal.</th><th>Overdue</th></tr>
    </thead>
    <tbody>
    <?php
    foreach ($obj_array as $obj_id => $obj_name ){
       $principal = $principal_array[$obj_id];     $principal_total+=$principal;
       $total_repaid = $total_repaid_array[$obj_id];  $total_total_repaid+=$total_repaid;
       $total_balance = $total_balance_array[$obj_id]; $total_balance_total+=$total_balance;
       $interest = $interest_array[$obj_id];           $interest_total+=$interest;
          $interest_p = false_zero(round((($interest/$principal)*100), 2));
       $other_charges = $other_charges_array[$obj_id];                                   $other_charges_total+=$other_charges;
       $total_repayable = $total_repayable_array[$obj_id];                               $total_repayable_total+=$total_repayable;
          $total_repaid_p = false_zero(round((($total_repaid/$total_repayable)*100), 2));
       $principal_paid = $principal_paid_array[$obj_id];                                 $principal_paid_total+=$principal_paid;
          $principal_paid_p = false_zero(round((($principal_paid/$principal)*100), 2));
       $interest_paid = $interest_paid_array[$obj_id];                                   $interest_total_paid+=$interest_paid;
           $interest_paid_p = false_zero(round((($interest_paid/$interest)*100), 2));
       $pi_paid = $principal_paid + $interest_paid;                                     $pi_total_paid+=$pi_paid;
            $pi_paid_p =  false_zero(round((($pi_paid/($interest+$principal))*100), 2));
       $other_charges_paid = $other_charges_paid_array[$obj_id];                        $other_charges_total_paid+=$other_charges_paid;
          $other_charges_paid_p = false_zero(round((($other_charges_paid/$other_charges)*100), 2));
       $overdue_balance = $total_overdue_balance_array[$obj_id];                       $overdue_balance_total+=$overdue_balance;

       echo " <tr><td class='font-bold text-black' style='background: white;'>$obj_name</td><td class='text-bold'>".number_format($principal)."</td><td class='text-bold text-blue'>".number_format($interest)." <span class=\"label bg-black-gradient pull-right\">$interest_p%</span> </td><td>".number_format($other_charges)."</td><td class='text-purple'>".number_format($total_repayable)." </td><td>".number_format($principal_paid)." <span class=\"label bg-black-gradient pull-right\">$principal_paid_p%</span> </td><td>".number_format($interest_paid)." <span class=\"label bg-black-gradient pull-right\"> $interest_paid_p%</span> </td><td>".number_format($pi_paid)." <span class=\"label bg-black-gradient pull-right\">$pi_paid_p%</span></td><td>".number_format($other_charges_paid)."<span class=\"label bg-info pull-right text-black\">$other_charges_paid_p%</span> </td><td class='text-purple text-bold'>".number_format($total_repaid)." <span class=\"label bg-black-gradient pull-right\">$total_repaid_p%</span> </td><td class='font-italic'>".number_format($total_balance)."</td><td class='text-red'>".number_format($overdue_balance)."</td></tr>";

    }
    $interest_total_p = false_zero(round((($interest_total/$principal_total)*100), 2));
    $principal_paid_total_p = false_zero(round((($principal_paid_total/$principal_total)*100), 2));
    $interest_total_paid_p  = false_zero(round((($interest_total_paid/$interest_total)*100), 2));
    $pi_total_paid_p =  false_zero(round((($pi_total_paid/($interest_total+$principal_total))*100), 2));
    $other_charges_total_paid_p = false_zero(round((($other_charges_total_paid/$other_charges_total)*100), 2));
    $total_total_repaid_p = false_zero(round((($total_total_repaid/$total_repayable_total)*100), 2));

    ?>
    </tbody>
    <tfoot>
    <?php
    echo " <tr class='bg-gray font-bold'><td>--</td><td class='text-bold'>".number_format($principal_total)."</td><td class='text-bold text-blue'>".number_format($interest_total)."<span class=\"label bg-purple-gradient pull-right\">$interest_total_p%</span></td><td>".number_format($other_charges_total)."</td><td class='text-purple'>".number_format($total_repayable_total)." </td><td>".number_format($principal_paid_total)."<span class=\"label pull-right bg-purple-gradient\">$principal_paid_total_p%</span></td><td>".number_format($interest_total_paid)."<span class=\"label pull-right bg-purple-gradient pull-right\">$interest_total_paid_p%</span></td><td>".number_format($pi_total_paid)."<span class=\"label pull-right bg-purple-gradient pull-right\">$pi_total_paid_p%</span></td><td>".number_format($other_charges_total_paid)."<span class=\"label bg-purple-gradient pull-right \">$other_charges_total_paid_p%</span></td><td class='text-purple text-bold'>".number_format($total_total_repaid)."<span class=\"label bg-purple-gradient\">$total_total_repaid_p%</span></td><td class='font-italic'>".number_format($total_balance_total)."</td><td class='text-red'>".number_format($overdue_balance_total)."</td></tr>";

    ?>





</table>
<hr/>
<div class="font-bold font-16"><?php echo $cust_disb_analysis; ?></div>