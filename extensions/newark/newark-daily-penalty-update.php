<?php
session_start();
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$ago_30 = datesub($date, 0, 0, 20);
$ago_15 = datesub($date, 0, 0, 1);


$all_loans = table_to_array('o_loans',"disbursed=1 AND paid=0 AND status!=0 AND final_due_date BETWEEN '$ago_30' AND '$ago_15'","1000000","uid","uid","asc");
$all_loans_list = implode(',', $all_loans);

$daily_penalty_addons = array();

$all_addons = fetchtable('o_loan_addons',"status=1 AND loan_id in ($all_loans_list) AND addon_id = 9","uid","asc","1000000000","loan_id, addon_id, addon_amount");
while($aa = mysqli_fetch_array($all_addons)){
    $lid = $aa['loan_id'];
    $addon_id = $aa['addon_id'];
    $addon_amount = $aa['addon_amount'];

        $daily_penalty_addons[$lid] = $addon_amount;


}
$updated = 0;
$created = 0;
$miss_created = 0;
$miss_updated = 0;
$skipped = 0;

$loans = fetchtable('o_loans',"uid in ($all_loans_list)","uid","asc","10000","uid, given_date, loan_amount, final_due_date");
while($l = mysqli_fetch_array($loans)){

    $lid = $l['uid'];
    $given_date = $l['given_date'];
    $final_due_date = $l['final_due_date'];
    $loan_amount = $l['loan_amount'];
    $due_ago = intval(datediff3($final_due_date, $date));

   // echo "$given_ago: $lid,";

    if($due_ago >= 1) {

        $days = $due_ago;
        if($days > 15){
            $days = 15;
        }
        $daily_interest = ((0.8 * $days)/100) * $loan_amount;
        $total_interest = $daily_interest;

        $current_interest = $daily_penalty_addons[$lid];

       // echo "Loan $lid, Final_due_date:$final_due_date, Days: $days, Loan Amount: $loan_amount, Interest: $total_interest, Current: $current_interest <br/>";

        if($current_interest > 0){
            if($current_interest < $total_interest){
                $update_db = updatedb('o_loan_addons', "addon_amount='$total_interest', added_date='$fulldate'","loan_id='$lid' AND addon_id=9 AND status=1 AND addon_amount!='$total_interest'");
                if($update_db == 1){
                   // echo "Updated $lid<br/>";
                    $updated+=1;
                    recalculate_loan($lid);
                }
                else{
                   // echo "Error updating $lid";
                    $miss_updated+=1;
                }
            }
            else{
               // echo "Skipped $lid <br/>";
                $skipped+=1;
            }
        }
        else{
            ///----Missing create
            $fds = array('loan_id', 'addon_id', 'addon_amount', 'added_date', 'status');
            $vals = array("$lid", "9", "$total_interest", "$fulldate", "1");
            $save = addtodb('o_loan_addons', $fds, $vals);
            if($save == 1){
               // echo "Created $lid <br/>";
                $created+=1;
                recalculate_loan($lid);
            }
            else{
               // echo "Error creating $lid <br/>";
                $miss_created+=1;
            }
        }

        //  echo "$lid, $given_date, Ago: $given_ago, Days: $days, Daily_interest: $daily_interest, Fixed: $fixed_interest, Total_interest: $total_interest <br/>";

        ///----Update Interest
      /*  $update_db = updatedb('o_loan_addons', "addon_amount='$total_interest', added_date='$fulldate'","loan_id='$lid' AND addon_id=9 AND status=1 AND addon_amount!='$total_interest'");
        if($update_db == 1){
            recalculate_loan($lid);
        }
      */

    }
   // $upd = updatedb('o_loans',"loan_flag=0","uid='$lid'");

}

echo "Updated: $updated, Created: $created, Miss Created: $miss_created, Miss Updated: $miss_updated, Skipped: $skipped";