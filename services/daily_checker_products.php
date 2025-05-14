<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/*
 * This service is called per product to mark them as overdue, written off e.t.c
 */
$company = $_GET['c'];

include_once("../configs/auth.inc");
if($company > 0) {
    echo $company;
    include_once("../php_functions/functions.php");

    $company_d = company_details($company);
    if ($company_d['uid'] > 0) {
        $db = $company_d['db_name'];
        $_SESSION['db_name'] = $db;
        include_once("../configs/conn.inc");

        $last_ = fetchrow('o_last_service',"uid=1","last_date");
        $dt = new DateTime($last_);

        $last_date = $dt->format('Y-m-d');


        if($last_date == $date)
        {
            die("Scheduled messages already sent for $fulldate $db");
            exit();
        }
        else
        {
          //  echo "Ready to send messages<br/>";
        }

        $loan_products = table_to_array('o_loan_products',"uid='$productId' AND status=1","100","uid");
        $loan_products_string = implode(',', $loan_products);


      ///////-------------------------End of get company details
        ///-------Lets start here
        ///------Loop through addons
            $ad = fetchonerow('o_loan_products',"status=1 AND uid = $productId","period, period_units");
            $period = $ad['period'];
            $period_units = $ad['period_units'];
            $total_period_days = $period*$period_units;
            $expected_due_date = datesub($date, 0, 0, $total_period_days);
           $anddate = " AND given_date = '$loan_day'";


            //echo $prod_addons_string;

            $all_loans = array();
            $addon_on_array = array();

            if($ignore_date == 1){
                $anddate = "";
            }

            $loans = fetchtable('o_loans',"status!=0 AND disbursed=1 AND product_id in ($prod_addons_string) $anddate","uid","asc","10000000","uid, given_date, $addon_on");
            while($l = mysqli_fetch_array($loans)){
                $uid = $l['uid'];
                $given_date = $l['given_date'];
                $action_amount = $l[$addon_on];
                $addon_on_array[$uid] = $action_amount;

                array_push($all_loans, $uid);

                //echo "$uid, $given_date \n";
            }
            $all_loans_string = implode(',', $all_loans);

            $addon_exists = table_to_array('o_loan_addons',"loan_id in ($all_loans_string) AND status=1 AND addon_id='$addonId'","1000000","loan_id");

           for($i=0; $i<sizeof($all_loans); ++$i)
           {

               if($amount_type == 'PERCENTAGE'){
                   $addon_amount = ($amountx)/100 * $addon_on_array[$all_loans[$i]];
               }
               else{
                   $addon_amount = $amountx;
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
                           recalculate_loan($all_loans[$i], true);
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
                       recalculate_loan($all_loans[$i], true);
                   }

               }
           }

           echo "$total_saved/$total_to_save Addons Created \n";
           echo "$total_updated/$total_to_update Addons Updated \n";











    }
  //  $update=updatedb('o_last_service',"last_date='$fulldate'","uid=1");
}
else{
    ///------Probably a suspense payment
    echo "Add a company parameter and Product";
}

include_once("../configs/close_connection.inc");
