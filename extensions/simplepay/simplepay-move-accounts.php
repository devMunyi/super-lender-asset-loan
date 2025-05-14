<?php
session_start();


include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

$ago_15 = datesub($date, 0, 0, 15);


//////----------------------Adds
?>

<?php



    $loan_statuses = table_to_obj('o_loan_statuses',"uid>0","100","uid","name");

    $cc_agents = table_to_array('o_users',"tag='CC' AND status=1","100000","uid");
    $fa_agents = table_to_array('o_users',"tag='FA' AND status=1","100000","uid");
    $EDC_agents = table_to_array('o_users',"tag='EDC' AND status=1","100000","uid");
    $username = table_to_obj('o_users',"uid > 0","100000","uid","name");
    $regions = table_to_array('o_regions',"status=1","1000","uid");
    $branch_regions = table_to_obj('o_branches',"region_id > 0","1000","uid","region_id");
    $agent_branches = table_to_obj('o_users',"status=1","100000","uid","branch");
    $region_agents = array();

   // echo "<br/> CC".implode(',', $cc_agents); 
   // echo "<br/> FA".implode(',', $fa_agents); 

    $current_cc = 0;
    $current_fa = 0;
    $loans_to_fa = array();
    $loan_balances_array = array();
    $loan_branches = array();
    $loan_regions = array();
    $agent_regions = array();
   
    $region1Agents = array();  $region1AgentAmount = array();
    $region2Agents = array();  $region2AgentAmount = array();  
    $region3Agents = array();  $region2AgentAmount = array();  

    $r1 = 0;
    $r2 = 0;
    $r3 = 0;

    ///--------------create an array with Agent uid and region
    $agent_region_allocation = array();
    for($f = 0; $f <= sizeof($fa_agents); ++$f){
       $fa_ag = $fa_agents[$f];             // echo $fa_ag.'<br/>';
       $fa_br = $agent_branches[$fa_ag];     //echo $fa_br.'!<br/>';
       $agent_region = $branch_regions[$fa_br]; //echo $agent_region.'?<br/>';
       if($agent_region == 1){
           array_push($region1Agents, $fa_ag );
        }
       if($agent_region == 2)
        {
            array_push($region2Agents, $fa_ag );
        }
      if($agent_region == 3)
        {
            array_push($region3Agents, $fa_ag );

        }

    }
    

   //echo implode(',', $region1Agents).'<br/>';
   //echo implode(',',$region2Agents).'<br/>';
   //echo implode(',', $region3Agents).'<br/>';


    $loans = fetchtable('o_loans',"disbursed=1 AND final_due_date <= '$ago_15'  AND status !=0 AND paid=0","loan_balance","asc","100000","uid, final_due_date, status, loan_balance, allocation, current_branch");
    while($l = mysqli_fetch_array($loans)){

        $uid = $l['uid'];
        $status = $l['status'];
        $due_date = $l['final_due_date'];
        $loan_balance = $l['loan_balance'];
        $current_allocation = $l['allocation'];
        $current_branch = $l['current_branch'];
        $ago = datediff($due_date, $date);
        

        if($ago >= 15 && $ago < 90 && sizeof($cc_agents) > 0){
          // echo "CC Bal:$loan_balance, DD:[$due_date] $uid [CC ".$cc_agents[$current_cc]."]<br/>";
           $cc = $cc_agents[$current_cc];

           echo "CC Update Loan $uid : $cc <br/>";

           $updatebdo = updatedb('o_loans',"current_co='$cc', allocation='CC'","uid='$uid'");
            if($updatebdo == 1) {
                echo "$uid : 1 <br/>";
                store_event('o_loans_', $uid, "LOAN Collector Updated to CC:<b>".$username[$cc]."($cc)</b> on $fulldate by automatic service");
            }
            else{
                echo "$uid : 0 <br/>";
            }


           $current_cc = $current_cc + 1;
           if($current_cc >= sizeof($cc_agents)){
            $current_cc = 0;
           } 
        }
        elseif($ago > 90 ){
          // array_push($loans_to_fa, $uid); 
           // $fa = $fa_agents[$current_fa];
           $loan_branches[$uid] = $current_branch;
           $loan_region = $branch_regions[$current_branch];
          
         //  var_dump($region_agents);

                //----Agent with minimum
                if($loan_region == 1){
                    $current_ag1 = $region1Agents[$r1];
                    $r1 = $r1 + 1;
                   
                        $updatebdo = updatedb('o_loans',"current_co='$current_ag1', allocation='FA'","uid='$uid'");
                        if($updatebdo == 1) {
                            echo "FA Update $updatebdo: <br/>";
                                ///--------Update Array

                            store_event('o_loans', $uid, "LOAN Collector Updated to FA:<b>".$username[$current_ag1]."($current_ag1)</b> on $fulldate by automatic service");
                        }
                        else{
                            echo "FA Update Fail $uid : 0 <br/>";
                        }
              

                   
                    if($r1 >= sizeof($region1Agents)){
                        $r1 = 0;
                    }
                    
                    //echo $uid.',<br/>';
                  
                }
                if($loan_region == 2){
                    $current_ag2 = $region2Agents[$r2];

                    $r2 = $r2 + 1;

                    $updatebdo = updatedb('o_loans',"current_co='$current_ag2', allocation='FA'","uid='$uid'");
                    if($updatebdo == 1) {
                        echo "FA Update $updatebdo: <br/>";
                            ///--------Update Array

                        store_event('o_loans', $uid, "LOAN Collector Updated to FA:<b>".$username[$current_ag2]."($current_ag2)</b> on $fulldate by automatic service");
                    }
                    else{
                        echo "FA Update Fail $uid : 0 <br/>";
                    }

                    if($r2 >= sizeof($region2Agents)){
                        $r2 = 0;
                    }
                 

                }
                if($loan_region == 3){
                  
                    $current_ag3 = $region3Agents[$r3];
                   // echo "Loan $uid - $current_ag3 ,,,<br/>";

                    $r3 = $r3 + 1;

                    $updatebdo = updatedb('o_loans',"current_co='$current_ag3', allocation='FA'","uid='$uid'");
                    if($updatebdo == 1) {
                        echo "FA Update $updatebdo: <br/>";
                            ///--------Update Array

                        store_event('o_loans', $uid, "LOAN Collector Updated to FA:<b>".$username[$current_ag3]."($current_ag3)</b> on $fulldate by automatic service");
                    }
                    else{
                        echo "FA Update Fail $uid : 0 <br/>";
                    }

                    if($r3 >= sizeof($region3Agents)){
                        $r3 = 0;
                    }
                 
                  
                }
             
            

               
             //  $agents[$loan_region][$agent_id] = $agents[$loan_region][$agent_id] + $loan_balance;
             //  echo "Current Branch: $current_branch, Region: $loan_region, Minimum $minimum_val : Agent $agent_id <br/>";



               ///--------Update BDO 
              /* $updatebdo = updatedb('o_loans',"current_co='$agent_id', allocation='FA'","uid='$uid'");
               if($updatebdo == 1) {
                echo "FA Update $updatebdo: <br/>";
                    ///--------Update Array
               $agents[$loan_region][$agent_id] = $agents[$loan_region][$agent_id] + $loan_balance;

                   store_event('o_loans', $uid, "LOAN Collector Updated to FA:<b>".$username[$agent_id]."($agent_id)</b> on $fulldate by automatic service");
               }
               else{
                echo "FA Update Fail $uid : 0 <br/>";
               }
              */

              
          // $loan_balances_array[$uid] = $loan_balance;
           // echo "FA $ago <br/>";
          /* $fa = $fa_agents[$current_fa];

           echo "FA Update Loan $uid : $fa <br/>";


           $updatebdo = updatedb('o_loans',"current_co='$fa', allocation='FA'","uid='$uid'");
           if($updatebdo == 1) {
            echo "$uid : 1 <br/>";
               store_event('o_loans', $uid, "LOAN Collector Updated to FA:<b>".$username[$fa]."($fa)</b> on $fulldate by automatic service");
           }
           else{
            echo "$uid : 0 <br/>";
           }




           $current_fa = $current_fa + 1;
           if($current_fa >= sizeof($fa_agents)){
            $current_fa = 0;
           }  */

        }
       
       
    }

   

    ////////////------Loop through regions
   for($r=0; $r<=sizeof($regions); ++$r){
        $region_id = $regions[$r];
        echo $region_id.'<br/>';
        ///-----Loop through loans in the region
        for($l=0; $l <= sizeof($loans_to_fa); ++$l){
            $loan_id = $loans_to_fa[$l];
            $loan_region = $loan_regions[$loan_id];

            if($region_id == $loan_region){
                ///-----This is a loan in the region
            }

        } 
    
    }


include_once("../configs/close_connection.inc");
    ?>
