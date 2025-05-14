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






$loan_q = "status!=0 AND final_due_date >= '$start_date' AND final_due_date <= '$end_date' AND disbursed=1 $loan_branches";
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
//$addons = loan_addons_array($loan_array);
//$loan_interest_array = $addons[0];
//$loan_other_charges_array = $addons[1];
$loans = fetchtable('o_loans',"uid in ($loan_list)","uid","asc","1000000000","uid, given_date, customer_id,loan_amount, final_due_date, product_id ,status, total_repaid, current_branch ,loan_balance, paid, total_repayable_amount, current_lo, current_co,group_id, group_id, loan_balance, status, other_info, total_addons, total_deductions");
while($l = mysqli_fetch_array($loans)) {
    $luid = $l['uid'];
    $given_date = $l['final_due_date']; ////// swapped for collections

    $sec = $l['other_info'];
    $sec_obj = (json_decode($sec, true));
    $interest = $sec_obj['INTEREST_AMOUNT'];

    $total_deductions = $l['total_deductions'];
    $status = $l['status'];
    $principal = $l['loan_amount'];
    $total_repaid = $l['total_repaid'] + $total_deductions;
    $loan_balance = $l['loan_balance'];
    $current_branch = $l['current_branch'];
    $product_id = $l['product_id'];
    $current_lo = $l['current_lo'];
    $current_co = $l['current_co'];
    $total_addons = $l['total_addons'];

    $group_id = $l['group_id'];

    if($status == 7){
        $overdue_balance = $l['loan_balance'];
    }
    else{
        $overdue_balance = 0;
    }

    $gd = explode('-', $given_date);
    $ym = $gd[0].'-'.$gd[1];
    $week_of_month = getWeekOfMonth($given_date);
    $week = $gd[0].'-'.$gd[1].'-W'.$week_of_month;
    //$interest = $loan_interest_array[$luid];    //// Found another way since it's stored now
    // $other_charges = $loan_other_charges_array[$luid];  ////Found another way ''


    $total_repayable = $l['total_repayable_amount'];
    $total_repaid_ = $l['total_repaid'];

    $other_charges = $total_addons - $interest;
    $total_repayable = $principal + $interest;  ////---Making this the total repayable amount
    $total_repaid = false_zero($total_repaid_ - $other_charges); ////----Total repaid, we remove other charges paid

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

<div class="row">
    <div class="col-sm-9">
        <table class="tablex">
            <thead class="bg-gray font-bold">
            <tr><th><?php echo $obj; ?></th><th>PRINCIPLE</th><th>INTEREST</th><th>OTHER CHARGES</th><th>REPAYABLE</th><th>PAID</th><th>BALANCE</th><th>RATE%</th><th>OVERDUE</th></tr>
            </thead>
            <tbody>
            <?php
            $obj_ = array();
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

                $obj_[$obj_name] = $principal;

                $rate = roundDown((($total_repaid/$total_repayable)*100), 2);
                $total_repaid_t+=$total_repaid;
                $total_repayable_t+=$total_repayable;

                echo " <tr><td class='text-black' style='background: white;'>$obj_name</td><td class='text-bold'>".number_format($principal)."</td><td class='text-blue'>".number_format($interest)."  </td><td>".number_format($other_charges)."</td><td>".number_format($total_repayable)." </td><td>".number_format($total_repaid)." </td><td>".number_format($total_balance)."</td><td>$rate%</td><td class='text-red'>".number_format($overdue_balance)."</td></tr>";


            }
            $interest_total_p = false_zero(round((($interest_total/$principal_total)*100), 2));
            $principal_paid_total_p = false_zero(round((($principal_paid_total/$principal_total)*100), 2));
            $interest_total_paid_p  = false_zero(round((($interest_total_paid/$interest_total)*100), 2));
            $pi_total_paid_p =  false_zero(round((($pi_total_paid/($interest_total+$principal_total))*100), 2));
            $other_charges_total_paid_p = false_zero(round((($other_charges_total_paid/$other_charges_total)*100), 2));
            $total_total_repaid_p = false_zero(round((($total_total_repaid/$total_repayable_total)*100), 2));

            $rate_av = roundDown((($total_repaid_t/$total_repayable_t)*100), 2);

            ?>
            </tbody>
            <tfoot>
            <?php
            echo " <tr class='bg-gray text-bold'><td>TOTAL</td><td>".number_format($principal_total)."</td><td class='text-blue'>".number_format($interest_total)."</td><td>".number_format($other_charges_total)."</td><td>".number_format($total_repayable_total)." </td><td>".number_format($total_total_repaid)."</td><td>".number_format($total_balance_total)."</td><td class='font-italic'>$rate_av%</td><td class='text-red'>".number_format($overdue_balance_total)."</td></tr>";

            ?>





        </table>

    </div>
    <div class="col-sm-3">


        <?php

        // $obj_ = array("a" => 100, "b" => 35, "c" => 36, "D"=>56, "E"=>58, "F"=>77);
        $total = array_sum($obj_); // Total sum of values
        $gap_size = 2; // Gap size in degrees
        $num_segments = count($obj_);

        $total_gap_size = $num_segments * $gap_size;
        $total_degrees_available = 360 - $total_gap_size; // Degrees available for segments

        // Define a color palette
        $color_palette = array("#4e79a7", "#f28e2b", "#e15759", "#76b7b2", "#59a14f", "#edc949", "#af7aa1", "#ff9da7", "#9c755f", "#bab0ab");

        $data = [];
        $accumulated_angle = 0;
        $color_index = 0;

        foreach ($obj_ as $key => $value) {

            $percentage = ($value / $total) * 100;
            $adjusted_degree = ($percentage / 100) * $total_degrees_available;
            $start_angle = $accumulated_angle;
            $end_angle = $start_angle + $adjusted_degree;
            $middle_angle = ($start_angle + $end_angle) / 2;

            // Use colors from the palette
            $color = $color_palette[$color_index % count($color_palette)];
            $color_index++;

            $data[] = [
                'label' => $key,
                'value' => round($percentage, 2) . '%', // Include percentage value
                'percentage' => $percentage,
                'color' => $color,
                'start' => $start_angle,
                'end' => $end_angle,
                'middle' => $middle_angle
            ];
            // Update accumulated_angle by adding adjusted_degree and gap_size
            $accumulated_angle = $end_angle + $gap_size;
        }
        ?>






















        <?php
        // Build the conic-gradient string with gaps
        $gradient_parts = [];
        foreach ($data as $index => $segment) {
            // Segment color
            $gradient_parts[] = "{$segment['color']} {$segment['start']}deg {$segment['end']}deg";
            // Gap color (transparent)
            if ($index < $num_segments - 1) {
                $gap_start = $segment['end'];
                $gap_end = $gap_start + $gap_size;
                $gradient_parts[] = "transparent {$gap_start}deg {$gap_end}deg";
            } else {
                // For the last segment, ensure we end at 360 degrees
                $gap_start = $segment['end'];
                $gap_end = 360;
                if ($gap_end > $gap_start) {
                    $gradient_parts[] = "transparent {$gap_start}deg {$gap_end}deg";
                }
            }
        }
        $gradient_string = implode(", ", $gradient_parts);
        ?>

        <div class="pie-chart-container">
            <div class="pie-chart" style="background: conic-gradient(<?= $gradient_string ?>);">
                <?php
                // Add labels inside the pie chart
                foreach ($data as $segment) {
                    // Adjust middle angle for label placement considering the gap
                    $angle_rad = deg2rad($segment['middle'] - 90);
                    $radius_percentage = 30; // Percentage of the pie chart's radius for label placement
                    $x = 50 + cos($angle_rad) * $radius_percentage;
                    $y = 50 + sin($angle_rad) * $radius_percentage;

                    // Prepare label content
                    $label_content = "{$segment['label']}\n{$segment['value']}";

                    echo "<div class='label' style='
                            left: {$x}%;
                            top: {$y}%;
                            '>" . nl2br($label_content) . "</div>";
                }
                ?>
            </div>
        </div>
    </div>
</div>