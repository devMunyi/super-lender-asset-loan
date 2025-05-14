<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$company = $_GET['c'];
$product_id = $_GET['p'];
include_once("../configs/20200902.php");
include_once("../configs/auth.inc");
if($company > 0) {
echo $company;
    include_once("../php_functions/functions.php");

    $company_d = company_details($company);
    if ($company_d['uid'] > 0) {
        $db = $company_d['db_name'];
        $_SESSION['db_name'] = $db;
        include_once("../configs/conn.inc");

        $prod = fetchonerow('o_loan_products',"uid='$product_id'","period, period_units, pay_frequency");
        $pay_frequency = $prod['pay_frequency'];
        $period = $prod['period'];
        $period_units = $prod['period_units'];
        $total_period = $period * $period_units;
        $instalments  = ceil(($total_period/$pay_frequency));



     $registrations = table_to_obj('o_loan_addons',"addon_id='2' AND status=1","100000","loan_id","addon_amount");
///////-------------------------End of get company details
        ///-------Lets start here
       $loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND status !=0 AND product_id='$product_id' AND final_due_date >= '$date'","uid","asc","10000","uid, given_date, final_due_date, total_repayable_amount,  total_repaid,total_instalments, loan_amount");
       while($l = mysqli_fetch_array($loans)){
           $uid = $l['uid'];
           $given_date = $l['given_date'];
           $final_due_date = $l['final_due_date'];
           $total_repayable_amount = $l['total_repayable_amount'];
          // $total_instalments = $instalments;
           $total_repaid = $l['total_repaid'];
           $loan_amount = $l['loan_amount'];
           $reg = $registrations[$uid];
           if($reg > 0){
               $total_repayable_amount = $total_repayable_amount - $reg;
               $total_repaid = $total_repaid - $reg;
             //  echo "<br/>---$total_repayable_amount, $total_repaid---<br/>";
           }




           if($pay_frequency > 0) {
               $inst = next_instalment($given_date, $pay_frequency, $final_due_date);
               $total_instalments = $inst['total_instalments'];
               $next_instalment = $inst['next_instalment'];
               $next_due_date = $inst['next_date'];
               $current_inst_amount = ($total_repayable_amount/$total_instalments)*$next_instalment;
               $current_inst_balance = round(false_zero($current_inst_amount - $total_repaid),0);
               echo "[$uid, Loan Amount: $loan_amount, Next Inst: $next_instalment,Given: $given_date- Next: $next_due_date, Final: $final_due_date,Inst Amount: $current_inst_amount, Total_rep: $total_repayable_amount , Total Inst: $total_instalments, Instalamount: $current_inst_amount] inst balance [$current_inst_balance], Repaid: $total_repaid, Pay Freq: $pay_frequency <br/>";
               $update_ = updatedb('o_loans', "current_instalment=$next_instalment, next_due_date='$next_due_date', current_instalment_amount='$current_inst_balance' ","uid='$uid'");
               if($update_ == 1){
                  ////------Check and impose penalties

               }
               else{

               }
               echo "Update $update_ <br/>";
               }
           else{
               echo "$uid has 0 instalments frequency <br/>";
           }



       }
    }

}
else{
    ///------Probably a suspense payment
    echo "Add a company parameter";
}


function next_instalment($given_date, $inst_period, $due_date){
    global $date;
    $result =array();
    if($inst_period > 0){
        $days_ago = datediff3($date, $given_date);
        $next = ceil ($days_ago/$inst_period);
    }
    else{
        $next = 1;
    }
    $days_passed = $next * $inst_period;
    $next_date = dateadd($given_date, 0,0, $days_passed);
    $passed_due_days = datediff3($due_date, $next_date);  ///---Next instalment should not be greater than due date
    if($passed_due_days < 1){
        $next_date = $due_date;
    }
    $loan_days = datediff3($given_date, $due_date);
    $ti = ceil($loan_days/$inst_period);

    $result['next_instalment'] = $next;
    $result['next_date'] = $next_date;
    $result['total_instalments'] = $ti;

    return $result;


}

function current_instalment($given_date, $inst_period){
    global $date;
    $result =array();
    if($inst_period > 0){
        $days_ago = datediff3($date, $given_date);
        $next = ceil ($days_ago/$inst_period);
    }
    else{
        $next = 1;
    }
    $days_passed = $next * $inst_period;
    $next_date = dateadd($given_date, 0,0, $days_passed);
    $passed_due_days = datediff3($due_date, $next_date);  ///---Next instalment should not be greater than due date
    if($passed_due_days < 1){
        $next_date = $due_date;
    }
   return $result;


}


