<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//set_time_limit(1200);
/*
 * This service is called after the daily checker products to add or update addons
 */
$company = $_GET['c'];
$addonId = $_GET['a'];
$updateAddons = $_GET['update_addon'];  /////If addon is already added, update it with new value
$ignore_date = $_GET['ignore_date'];
include_once("../configs/auth.inc");
include_once '../configs/20200902.php';
if($company > 0 && $addonId > 0) {
    echo $company;
    include_once("../php_functions/functions.php");

    $company_d = company_details($company);
    if ($company_d['uid'] > 0) {
        $db = $db_;
        $_SESSION['db_name'] = $db;
        include_once("../configs/conn.inc");

        $last_ = fetchrow('o_last_service',"uid=1","last_date");
        $dt = new DateTime($last_);

        $last_date = $dt->format('Y-m-d');


        if($last_date == $date)
        {
         //   die("Scheduled messages already sent for $fulldate $db");
          //  exit();
        }
        else
        {
          //  echo "Ready to send messages<br/>";
        }
        /////-------------------------------------Start by marking all loans due yesterday as overdue
        $yesterday = datesub($date, 0, 0, 1);
        $mark = updatedb('o_loans',"status='7'","paid=0 AND disbursed=1 AND final_due_date <= '$yesterday' AND status=3");

        ///
        /// -----------------------------End

        $prod_addons = table_to_array('o_product_addons',"addon_id='$addonId' AND status=1","100","product_id");
        $prod_addons_string = implode(',', $prod_addons);


      ///////-------------------------End of get company details
        ///-------Lets start here
        ///------Loop through addons
            $ad = fetchonerow('o_addons',"status=1 AND uid = $addonId","*");
            $uid = $ad['uid'];
            $name = $ad['name'];
            $from_day = false_zero($ad['from_day']);
            $to_day = false_zero($ad['to_day']);
            $amount = false_zero($ad['amount']);
            $amount_type = $ad['amount_type'];
            $loan_stage = $ad['loan_stage'];

            $addon_on = $ad['addon_on'];
            if($amount_type == 'PERCENTAGE'){
                $factor = $amount/100;
            }
            else{
                $factor = 1;
            }
            echo "Addon ($name) \n";

            $anddate = "";
        if($to_day > $from_day){
           $loan_day = datesub($date, 0, 0, $from_day);  /////For loans given this day
            $to_loan_day = datesub($date, 0, 0, $to_day);  /////to this day
            // echo "$uid, Daily ($total_days days) ($loan_day:-$to_loan_day)\n";
            $anddate = " AND given_date BETWEEN '$to_loan_day' AND '$loan_day'";
            }
         else
            {
                ////---One charge
                $loan_day = datesub($date, 0, 0, $from_day);  /////For loans given this day
               // echo "$uid, Once ($loan_day)\n";
                $anddate = " AND given_date = '$loan_day'";
            }
         $amountx = $amount;
            //echo $prod_addons_string;

            $all_loans = array();
            $addon_on_array = array();

            if($ignore_date == 1){
                $anddate = "";
            }
            ////-----loans to recalculate for daily interest
            $loans_to_recalc_array = array();
            
            $loan_total_days_array = array();
            $loans = fetchtable('o_loans',"status!=0 AND disbursed=1 AND product_id in ($prod_addons_string) $anddate AND disbursed=1 AND paid = 0","uid","asc","100000000","uid, given_date, $addon_on");
            while($l = mysqli_fetch_array($loans)){
                $uid = $l['uid'];
                $given_date = $l['given_date'];
                $days_ago = datediff3($given_date, $date);        ///////---------5
                ////-----Calculate Interests
                if($to_day > $from_day){              ////////-------- 8 > 5

                    if($days_ago >= $from_day && $days_ago <= $to_day){
                        $total_days = $days_ago - $from_day + 1;
                        array_push($loans_to_recalc_array, $uid);
                    }
                    else if($days_ago > $to_day){
                        $total_days = $to_day;
                        array_push($loans_to_recalc_array, $uid);
                    }
                    else{
                        $total_days = 0;
                    }
                    //---Daily charge or something

                   $loan_total_days_array[$uid] = $total_days;
                }



                $action_amount = $l[$addon_on];
                $addon_on_array[$uid] = $action_amount;

                array_push($all_loans, $uid);

                //echo "$uid, $given_date \n";
            }
            $all_loans_string = implode(',', $all_loans);

            $addon_exists = table_to_array('o_loan_addons',"loan_id in ($all_loans_string) AND status=1 AND addon_id='$addonId'","1000000","loan_id");

           for($i=0; $i<sizeof($all_loans); ++$i)
           {
               if($loan_total_days_array[$all_loans[$i]] > 0){
                   $mult_factor = $loan_total_days_array[$all_loans[$i]];
               }
               else{
                   $mult_factor = 1;
               }

               if($amount_type == 'PERCENTAGE'){
                   $addon_amount = $mult_factor * ($amountx)/100 * $addon_on_array[$all_loans[$i]];
               }
               else{
                   $addon_amount = $amountx * $mult_factor;
               }
               if( in_array( $all_loans[$i] ,$addon_exists ) )
               {
                 //  echo $all_loans[$i]."Exists $factor, update $addon_amount \n";
                   if($updateAddons == 1){
                       ///----Update Addons
                       $updated = updatedb('o_loan_addons',"addon_amount='$addon_amount', added_date='$fulldate'","loan_id='".$all_loans[$i]."' AND addon_id='$addonId'");
                       $total_updated = $total_updated + $updated;
                       $total_to_update = $total_to_update + 1;
                       if($updated == 1){
                          // recalculate_loan($all_loans[$i], true);
                          ///too expensive, recalculate better
                       }
                   }
               }
               else{
                  // echo $all_loans[$i]."No exist ".$addon_on_array[$all_loans[$i]].", create $addon_amount \n";
                   $fds = array('loan_id','addon_id','addon_amount','added_date','status');
                   $vals = array($all_loans[$i],"$addonId","$addon_amount","$fulldate","1");
                   $save = addtodb('o_loan_addons', $fds, $vals);
                   $total_saved = $total_saved + $save;
                   $total_to_save = $total_to_save + 1;
                   if($save == 1){
                     //  recalculate_loan($all_loans[$i], true);
                     /// Too expensive recalculate better
                   }

               }
           }

           echo "$total_saved/$total_to_save Addons Created \n";
           echo "$total_updated/$total_to_update Addons Updated \n";

           

          ///----All addons
          $loan_addons_array = array();
          $adds = fetchtable('o_Loan_addons',"status=1 AND loan_id in ($all_loans_string)","uid","asc","100000000","uid, loan_id, addon_amount");
          while($aa = mysqli_fetch_array($adds)){
            $llid = $aa['loan_id'];
            $aaamount = $aa['addon_amount'];
            $loan_addons_array = obj_add($loan_addons_array, $llid, $aaamount);

          }

          ////----Loop through loans
          $loans_l = fetchtable('o_loans',"uid in ($all_loans_string)","uid","asc","10","uid, loan_amount, total_repaid");
          while($ll = mysqli_fetch_array($loans_l)){
            $loan_id = $ll['uid'];
            $loan_amount = $ll['loan_amount'];
            $total_repaid = $ll['total_repaid'];

            $total_addons_calc = $loan_addons_array[$loan_id];
            if($total_addons_calc > 0){
                ///----Do the rest of the calculations
                $total_repayable = $loan_amount + $total_addons_calc;
                $loan_balance = false_zero($total_repaid - $total_repayable);
                ///----Update the loan
                $update_l = updatedb('o_Loans',"total_repayable_amount='$total_repayable', loan_balance='$loan_balance', total_addons='$total_addons_calc'","uid='$loan_id'");
                echo "Update loan: $loan_id <br/>";
            }

          }








    }
  //  $update=updatedb('o_last_service',"last_date='$fulldate'","uid=1");
}
else{
    ///------Probably a suspense payment
    echo "Add a company parameter and addon";
}


include_once("../configs/close_connection.inc");