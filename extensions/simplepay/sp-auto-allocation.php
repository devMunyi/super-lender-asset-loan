<?php
session_start();
include_once("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once("../configs/conn.inc");
include_once("../php_functions/functions.php");

//$agent_allocations = table_to_obj('o_loans',"disbursed=1 AND paid=0 AND status!=0 AND current_agent (12, 13, 14, 21)","100000","current_agent","loan_balance");
///---agent allocations now looks like $agent_allocations[1]=1000, $agent_allocations[2] = 3000;

//=== special case branches
$special_case_branches = [3, 4, 28, 40, 41, 42, 43, 44, 45, 52, 54, 58, 98, 99, 100, 101];
//=== branch_id => agent_id, a branch is assigned to a specific agent
$special_case_branches_allocation = [
    3 => 2915,
    4 => 2587,
    28 => 2591,
    40 => 2589,
    41 => 2589,
    42 => 2915,
    43 => 3538,
    44 => 3538,
    45 => 2591,
    52 => 2915,
    54 => 2590,
    58 => 2590,
    98 => 2587,
    99 => 2840,
    100 => 2590,
    101 => 2840
];
$branch_regions = table_to_obj('o_branches', "uid > 0", "10000", "uid", "region_id");
$branches_array = table_to_array('o_branches', "uid > 0", "10000", "uid");
$CC_agents = array();
$CC_agents_A = array();
$CC_agents_B = array();
$CC_agents_C = array();
$FA_agents = array();
//$FA_agents_central = array();
//$FA_agents_eastern = array();
//$FA_agents_western = array();
$IDC_agents = array();
$EDC_agents = array();
$all_agents = array();

$agent_names_array = array();

$agents = fetchtable('o_users', "status IN (1, 3)", "uid", "asc", "10000", "uid, user_group, name, branch, tag");
while ($a = mysqli_fetch_array($agents)) {
    $user = $a['uid'];
    $ugroup = $a['user_group'];
    $aname = $a['name'];
    $abranch = $a['branch'];
    $tag = $a['tag'];


    $agent_names_array[$user] = $aname;
    if ($ugroup ==  12) {
        array_push($CC_agents, $user);
        if ($tag == 'A') {
            array_push($CC_agents_A, $user);
        } elseif ($tag == 'B') {
            array_push($CC_agents_B, $user);
        } elseif ($tag == 'C') {
            array_push($CC_agents_C, $user);
        }
        array_push($all_agents, $user);
    } elseif ($ugroup == 13) {

        array_push($FA_agents, $user);
        /*
            $region = $branch_regions[$abranch];
            if($region == 1){
                array_push($FA_agents_central, $user);
            }
            elseif ($region == 2){
                array_push($FA_agents_eastern, $user);
            }
            elseif ($region == 3){
                array_push($FA_agents_western, $user);
            }
            */
        array_push($all_agents, $user);
    } elseif ($ugroup == 14) {
        array_push($EDC_agents, $user);
        array_push($all_agents, $user);
    } elseif ($ugroup == 21) {
        array_push($IDC_agents, $user);
        array_push($all_agents, $user);
    }
}

$CC_agents_list = implode(',', $CC_agents);
$CC_agents_list_A = implode(',', $CC_agents_A);
$CC_agents_list_B = implode(',', $CC_agents_B);
$CC_agents_list_C = implode(',', $CC_agents_C);

$FA_agents_list = implode(',', $FA_agents);
$EDC_agents_list = implode(',', $EDC_agents);
$IDC_agents_list = implode(',', $IDC_agents);
$all_agents_list = implode(',', $all_agents);


$CC_allocations = array_fill_keys($CC_agents, 0);
$CC_allocations_A = array_fill_keys($CC_agents_A, 0);
$CC_allocations_B = array_fill_keys($CC_agents_B, 0);
$CC_allocations_C = array_fill_keys($CC_agents_C, 0);
//$FA_allocations = array_fill_keys($FA_agents, 0);
//$FA_allocations_central = array_fill_keys($FA_agents_central, 0);
//$FA_allocations_eastern = array_fill_keys($FA_agents_eastern, 0);
//$FA_allocations_western = array_fill_keys($FA_agents_western, 0);
$EDC_allocations = array_fill_keys($EDC_agents, 0);
$IDC_allocations = array_fill_keys($IDC_agents, 0);

//echo(json_encode($FA_agents_central));
//echo(json_encode($FA_agents_eastern));
//die(json_encode($FA_agents_western));


$agent_totals = array();
/////----Lets calculate total per agent
$agent_allocations = fetchtable('o_loans', "disbursed=1 AND paid=0 AND loan_balance > 5 AND status!=0 AND current_agent in ($all_agents_list)", "uid", "asc", "100000000", "current_agent,loan_balance, current_branch");
while ($al = mysqli_fetch_array($agent_allocations)) {
    $agent = $al['current_agent'];
    $loan_balance = $al['loan_balance'];
    $current_branch = $al['current_branch'];

    if (in_array($agent, $CC_agents)) {
        $CC_allocations = obj_add($CC_allocations, $agent, $loan_balance);
    }

    if (in_array($agent, $CC_agents_A)) {
        $CC_allocations_A = obj_add($CC_allocations_A, $agent, $loan_balance);
    }
    if (in_array($agent, $CC_agents_B)) {
        $CC_allocations_B = obj_add($CC_allocations_B, $agent, $loan_balance);
    }
    if (in_array($agent, $CC_agents_C)) {
        $CC_allocations_C = obj_add($CC_allocations_C, $agent, $loan_balance);
    } elseif (in_array($agent, $FA_agents)) {
        $FA_allocations = obj_add($FA_allocations, $agent, $loan_balance);
        /* if(in_array($agent, $FA_agents_central)){
             $FA_allocations_central = obj_add($FA_allocations_central, $agent, $loan_balance);
         }
         elseif (in_array($agent, $FA_agents_eastern)){
             $FA_allocations_eastern = obj_add($FA_allocations_eastern, $agent, $loan_balance);
         }
         elseif (in_array($agent, $FA_agents_western)){
             $FA_allocations_western = obj_add($FA_allocations_western, $agent, $loan_balance);
         } */
    } elseif (in_array($agent, $EDC_agents)) {
        $EDC_allocations = obj_add($EDC_allocations, $agent, $loan_balance);
    } elseif (in_array($agent, $IDC_agents) && in_array($current_branch, $special_case_branches)) {
        $IDC_allocations = obj_add($IDC_allocations, $agent, $loan_balance);
    }
}

////-----Check if users are allocated and default to 0

//echo json_encode($CC_allocations, true);

//die();
$CC_loans = array();
$CC_loans_A = array();
$CC_loans_B = array();
$CC_loans_C = array();
$FA_loans = array();
//$FA_loans_central = array();
//$FA_loans_eastern = array();
//$FA_loans_western = array();
$IDC_loans = array();
$IDC_loan_branches = array(); // this will be associative array of loan_id => branch_id
$EDC_loans = array();


$loan_balances_array = array();
$loan_branches =  array();
/////////////////////---------------Query all loans 'except those locked
$loans = fetchtable('o_loans', "disbursed=1 AND paid=0 AND loan_balance > 5 AND  final_due_date < '$date' AND (
        JSON_UNQUOTE(JSON_EXTRACT(other_info, '$.LOCK_ALLOCATION')) != '1' OR JSON_EXTRACT(other_info, '$.LOCK_ALLOCATION') IS NULL)", "uid", "asc", "100000000000", "uid, final_due_date, current_agent, loan_balance, current_branch");
while ($l = mysqli_fetch_array($loans)) {
    $uid = $l['uid'];
    $final_due_date = $l['final_due_date'];
    $current_agent = $l['current_agent'];
    $loan_balance = $l['loan_balance'];
    $current_branch = $l['current_branch'];
    $current_region = $branch_regions[$current_branch];

    $ago = datediff($final_due_date, $date);
    $loan_balances_array[$uid] = $loan_balance;
    $loan_branches[$uid] = $current_branch;

    if ($ago > 15 and $ago <= 60) {
        ////-----Call centre
        /// Check if loan is already assigned to a CC agent
        if (!in_array($current_agent, $CC_agents)) {
            // array_push($CC_loans, $uid);
        }
    }
    //-------------------CC extended Vintages


    if ($ago >= 16 and $ago <= 30) {  ////---AAAA (Green Vintage)

        if (!in_array($current_agent, $CC_agents_A)) {
            array_push($CC_loans_A, $uid);
        }
    }
    if ($ago > 30 and $ago <= 45) {  ////---BBBB (Blue Vintage)
        if (!in_array($current_agent, $CC_agents_B)) {
            array_push($CC_loans_B, $uid);
        }
    }
    if ($ago > 45 and $ago <= 60) {  ////---CCCC (Yellow Vitange)
        if (!in_array($current_agent, $CC_agents_C)) {
            array_push($CC_loans_C, $uid);
        }
    }
    //-------------------End CC extended Vintages
    elseif ($ago > 60 and $ago <= 135) {

        ///-----FA   
        // if (!in_array($current_agent, $IDC_agents) && in_array($current_branch, $special_case_branches)) {
        if (in_array($current_branch, $special_case_branches)) {
            array_push($IDC_loans, $uid);
            $IDC_loan_branches[$uid] = $current_branch;
        } else {

            /// Check if loan is already assigned to a FA agent
            if (!in_array($current_agent, $FA_agents)) {
                array_push($FA_loans, $uid);
            }
        }


        /*  if($current_region == 1){
              if(!in_array($current_agent, $FA_agents_central)){
                  array_push($FA_loans_central, $uid);
              }
          }
          if($current_region == 2){
              if(!in_array($current_agent, $FA_agents_eastern)){
                  array_push($FA_loans_eastern, $uid);
              }
          }
          if($current_region == 3){
              if(!in_array($current_agent, $FA_agents_western)){
                  array_push($FA_loans_western, $uid);
              }
          }  */
    } elseif ($ago > 145 and $ago <= 160) {
        ///------IDC
        /// Check if loan is already assigned to a IDC agent
        // if (!in_array($current_agent, $IDC_agents) && in_array($current_branch, $special_case_branches)) {
         if (in_array($current_branch, $special_case_branches)) {
            array_push($IDC_loans, $uid);
            $IDC_loan_branches[$uid] = $current_branch;
        }
    } elseif ($ago > 160) {
        ////-----EDC
        // if (!in_array($current_agent, $IDC_agents) && in_array($current_branch, $special_case_branches)) {
        if (in_array($current_branch, $special_case_branches)) {
            array_push($IDC_loans, $uid);
            $IDC_loan_branches[$uid] = $current_branch;
        } else {
            /// Check if loan is already assigned to a IDC agent
            if (!in_array($current_agent, $EDC_agents)) {
                array_push($EDC_loans, $uid);
            }
        }
    }
}


if (isset($_GET['CC'])) {
    $mass_logging = "";
    $mass_logging_A = "";
    $mass_logging_B = "";
    $mass_logging_C = "";
    $all_allocated = 0;
    $all_allocated_A = 0;
    $all_allocated_B = 0;
    $all_allocated_C = 0;
    /////////------------------------CC allocations
    for ($i = 0; $i < sizeof($CC_loans); ++$i) {
        $loan_id = $CC_loans[$i];
        $loan_balance = $loan_balances_array[$loan_id];

        $minValue = min($CC_allocations); // Get the minimum value from the array
        $minAgent = array_search($minValue, $CC_allocations); // Find the agent with least value
        /////---------------Save allocation to database, add logs
        /////---------------Update allocation total
        $CC_allocations = obj_add($CC_allocations, $minAgent, $loan_balance);
        $agent_name = $agent_names_array[$minAgent];


        $allocate = updatedb('o_loans', "current_agent='$minAgent', allocation='CC'", "uid='$loan_id'");
        if ($allocate == 1) {
            $all_allocated += 1;
            $mess = "Loan allocated to CC Agent $agent_name($minAgent) by automated system";
            //---Too expensive to save one by one
            $mass_logging = $mass_logging . ',("o_loans","' . $loan_id . '","' . $mess . '","' . $fulldate . '","0","1")';
        } else {
            echo "Error allocating Loan $loan_id to $minAgent <br/>";
        }
        // echo "Loan $loan_id, CC $minAgent <br/>";


    }
    echo " $all_allocated/" . sizeof($CC_loans) . " Accounts allocated to CC <br/>";

    $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
    $log = addtodbmulti('o_events', $fds, ltrim($mass_logging, ","));


    /////------CC A allocations
    ///
    ///
    for ($i = 0; $i < sizeof($CC_loans_A); ++$i) {
        $loan_id = $CC_loans_A[$i];
        $loan_balance = $loan_balances_array[$loan_id];

        $minValue = min($CC_allocations_A); // Get the minimum value from the array
        $minAgent = array_search($minValue, $CC_allocations_A); // Find the agent with least value
        /////---------------Save allocation to database, add logs
        /////---------------Update allocation total
        $CC_allocations_A = obj_add($CC_allocations_A, $minAgent, $loan_balance);
        $agent_name = $agent_names_array[$minAgent];


        $allocate = updatedb('o_loans', "current_agent='$minAgent', allocation='CC'", "uid='$loan_id'");
        if ($allocate == 1) {
            $all_allocated_A += 1;
            $mess = "Loan allocated to CC Agent $agent_name($minAgent) by automated system";
            //---Too expensive to save one by one
            $mass_logging_A = $mass_logging_A . ',("o_loans","' . $loan_id . '","' . $mess . '","' . $fulldate . '","0","1")';

             /// === store allocation history
             try{
                $alloc_fds = ['loan_id', 'allocation_type', 'allocation_date', 'allocation_datetime', 'allocation_amount', 'allocated_agent', 'status'];
                $alloc_vals = [$loan_id, "CC", "$date", "$fulldate", $loan_balance, $minAgent, 1];

                addtodb("o_allocation_history", $alloc_fds, $alloc_vals);

            }catch(Exception $e){
                echo "Error: " . $e->getMessage();
            }
        } else {
            echo "Error allocating Loan $loan_id to $minAgent <br/>";
        }
        // echo "Loan $loan_id, CC $minAgent <br/>";


    }
    echo " $all_allocated_A/" . sizeof($CC_loans_A) . " Accounts allocated to CC <br/>";

    $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
    $log = addtodbmulti('o_events', $fds, ltrim($mass_logging_A, ","));

    ///
    ///
    /////------CC A allocations END






    /// -------CC B Allocations
    ///
    for ($i = 0; $i < sizeof($CC_loans_B); ++$i) {
        $loan_id = $CC_loans_B[$i];
        $loan_balance = $loan_balances_array[$loan_id];

        $minValue = min($CC_allocations_B); // Get the minimum value from the array
        $minAgent = array_search($minValue, $CC_allocations_B); // Find the agent with least value
        /////---------------Save allocation to database, add logs
        /////---------------Update allocation total
        $CC_allocations_B = obj_add($CC_allocations_B, $minAgent, $loan_balance);
        $agent_name = $agent_names_array[$minAgent];


        $allocate = updatedb('o_loans', "current_agent='$minAgent', allocation='CC'", "uid='$loan_id'");
        if ($allocate == 1) {
            $all_allocated_B += 1;
            $mess = "Loan allocated to CC Agent $agent_name($minAgent) by automated system";
            //---Too expensive to save one by one
            $mass_logging_B = $mass_logging_B . ',("o_loans","' . $loan_id . '","' . $mess . '","' . $fulldate . '","0","1")';

             /// === store allocation history
             try{
                $alloc_fds = ['loan_id', 'allocation_type', 'allocation_date', 'allocation_datetime', 'allocation_amount', 'allocated_agent', 'status'];
                $alloc_vals = [$loan_id, "CC", "$date", "$fulldate", $loan_balance, $minAgent, 1];

                addtodb("o_allocation_history", $alloc_fds, $alloc_vals);

            }catch(Exception $e){
                echo "Error: " . $e->getMessage();
            }
        } else {
            echo "Error allocating Loan $loan_id to $minAgent <br/>";
        }
        // echo "Loan $loan_id, CC $minAgent <br/>";


    }
    echo " $all_allocated_B/" . sizeof($CC_loans_B) . " Accounts allocated to CC <br/>";

    $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
    $log = addtodbmulti('o_events', $fds, ltrim($mass_logging_B, ","));
    ///
    ///
    /// -------CC B Allocations END

    ///--------CC C Allocations
    ///
    ///
    ///
    for ($i = 0; $i < sizeof($CC_loans_C); ++$i) {
        $loan_id = $CC_loans_C[$i];
        $loan_balance = $loan_balances_array[$loan_id];

        $minValue = min($CC_allocations_C); // Get the minimum value from the array
        $minAgent = array_search($minValue, $CC_allocations_C); // Find the agent with least value
        /////---------------Save allocation to database, add logs
        /////---------------Update allocation total
        $CC_allocations_C = obj_add($CC_allocations_C, $minAgent, $loan_balance);
        $agent_name = $agent_names_array[$minAgent];


        $allocate = updatedb('o_loans', "current_agent='$minAgent', allocation='CC'", "uid='$loan_id'");
        if ($allocate == 1) {
            $all_allocated_C += 1;
            $mess = "Loan allocated to CC Agent $agent_name($minAgent) by automated system";
            //---Too expensive to save one by one
            $mass_logging_C = $mass_logging_C . ',("o_loans","' . $loan_id . '","' . $mess . '","' . $fulldate . '","0","1")';

             /// === store allocation history
             try{
                $alloc_fds = ['loan_id', 'allocation_type', 'allocation_date', 'allocation_datetime', 'allocation_amount', 'allocated_agent', 'status'];
                $alloc_vals = [$loan_id, "CC", "$date", "$fulldate", $loan_balance, $minAgent, 1];

                addtodb("o_allocation_history", $alloc_fds, $alloc_vals);

            }catch(Exception $e){
                echo "Error: " . $e->getMessage();
            }
        } else {
            echo "Error allocating Loan $loan_id to $minAgent <br/>";
        }
        // echo "Loan $loan_id, CC $minAgent <br/>";


    }
    echo " $all_allocated_C/" . sizeof($CC_loans_C) . " Accounts allocated to CC <br/>";

    $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
    $log = addtodbmulti('o_events', $fds, ltrim($mass_logging_C, ","));
    ///
    /// -------CC C Allocations END

}

if (isset($_GET['FA'])) {

    $branch_agents = array();

    $agent_branches = fetchtable('o_staff_branches', "status=1 AND agent in ($FA_agents_list)", "uid", "asc", "1000000", "agent, branch");
    while ($ab = mysqli_fetch_array($agent_branches)) {
        $agent_uid = $ab['agent'];
        $branch_uid = $ab['branch'];

        $alloc_amount = false_zero($FA_allocations[$agent_uid]); // gotten from somewhere

        if (isset($branch_agents[$branch_uid])) {
            $branch_agents[$branch_uid][$agent_uid] = $alloc_amount;
        } else {
            $branch_agents[$branch_uid] = array($agent_uid => $alloc_amount);
        }
    }

    //echo json_encode($branch_agents);

    //////------------------FA Allocation

    $mass_logging = "";
    $all_allocated = 0;

    for ($i = 0; $i < sizeof($FA_loans); ++$i) {
        $loan_id = $FA_loans[$i];
        $loan_balance = $loan_balances_array[$loan_id];
        $loan_branch = $loan_branches[$loan_id];

        $FA_branch_allocations = $branch_agents[$loan_branch];

        $minValue = min($FA_branch_allocations); // Get the minimum value from the array
        $minAgent = array_search($minValue, $FA_branch_allocations); // Find the agent with least value
        /////---------------Save allocation to database, add logs
        /////---------------Update allocation total
        $branch_agents[$loan_branch][$minAgent] = $branch_agents[$loan_branch][$minAgent] + $loan_balance;
        $agent_name = $agent_names_array[$minAgent];

        // echo "$agent_name UID: $minAgent, LID: $loan_id, Amt: $minValue, Bal: $loan_balance <br/>";


        $allocate = updatedb('o_loans', "current_agent='$minAgent', allocation='FA'", "uid='$loan_id'");
        if ($allocate == 1) {
            $all_allocated += 1;
            $mess = "Loan allocated to FA Agent $agent_name($minAgent) by automated system";
            //---Too expensive to save one by one
            $mass_logging = $mass_logging . ',("o_loans","' . $loan_id . '","' . $mess . '","' . $fulldate . '","0","1")';

             /// === store allocation history
             try{
                $alloc_fds = ['loan_id', 'allocation_type', 'allocation_date', 'allocation_datetime', 'allocation_amount', 'allocated_agent', 'status'];
                $alloc_vals = [$loan_id, "FA", "$date", "$fulldate", $loan_balance, $minAgent, 1];

                addtodb("o_allocation_history", $alloc_fds, $alloc_vals);

            }catch(Exception $e){
                echo "Error: " . $e->getMessage();
            }

        } else {
            echo "Error allocating Loan $loan_id to $minAgent <br/>";
        }
        // echo "Loan $loan_id, CC $minAgent <br/>";

    }
    echo " $all_allocated/" . sizeof($FA_loans) . " Accounts allocated to FA <br/>";

    $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
    $log = addtodbmulti('o_events', $fds, ltrim($mass_logging, ","));

    /*
        ///////////-------------------CENTRAL CENTRAL CENTRAL CENTRAL CENTRAL CENTRAL CENTRAL CENTRAL
        $mass_logging = "";
        $all_allocated = 0;

        for ($i = 0; $i < sizeof($FA_loans_central); ++$i) {
            $loan_id = $FA_loans_central[$i];
            $loan_balance = $loan_balances_array[$loan_id];

            $minValue = min($FA_allocations_central); // Get the minimum value from the array
            $minAgent = array_search($minValue, $FA_allocations_central); // Find the agent with least value
            /////---------------Save allocation to database, add logs
            /////---------------Update allocation total
            $FA_allocations_central = obj_add($FA_allocations_central, $minAgent, $loan_balance);
            $agent_name = $agent_names_array[$minAgent];

            // echo "$agent_name $minAgent, $loan_id <br/>";


            $allocate = updatedb('o_loans', "current_agent='$minAgent', allocation='FA'", "uid='$loan_id'");
            if ($allocate == 1) {
                $all_allocated += 1;
                $mess = "Loan allocated to FA Agent $agent_name($minAgent) by automated system";
                //---Too expensive to save one by one
                $mass_logging = $mass_logging . ',("o_loans","' . $loan_id . '","' . $mess . '","' . $fulldate . '","0","1")';
            } else {
                echo "Error allocating Loan $loan_id to $minAgent <br/>";
            }
            // echo "Loan $loan_id, CC $minAgent <br/>";

        }
        echo " $all_allocated/" . sizeof($FA_loans) . " Accounts allocated to FA Central<br/>";

        $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
        $log = addtodbmulti('o_events', $fds, ltrim($mass_logging, ","));



        ///////EASTERN EASTERN EASTERN EASTERN EASTERN EASTERN EASTERN EASTERN EASTERN EASTERN EASTERN
        $mass_logging = "";
        $all_allocated = 0;

        for ($i = 0; $i < sizeof($FA_loans_eastern); ++$i) {
            $loan_id = $FA_loans_eastern[$i];
            $loan_balance = $loan_balances_array[$loan_id];

            $minValue = min($FA_allocations_eastern); // Get the minimum value from the array
            $minAgent = array_search($minValue, $FA_allocations_eastern); // Find the agent with least value
            /////---------------Save allocation to database, add logs
            /////---------------Update allocation total
            $FA_allocations_eastern = obj_add($FA_allocations_eastern, $minAgent, $loan_balance);
            $agent_name = $agent_names_array[$minAgent];

            // echo "$agent_name $minAgent, $loan_id <br/>";


            $allocate = updatedb('o_loans', "current_agent='$minAgent', allocation='FA'", "uid='$loan_id'");
            if ($allocate == 1) {
                $all_allocated += 1;
                $mess = "Loan allocated to FA Agent $agent_name($minAgent) by automated system";
                //---Too expensive to save one by one
                $mass_logging = $mass_logging . ',("o_loans","' . $loan_id . '","' . $mess . '","' . $fulldate . '","0","1")';
            } else {
                echo "Error allocating Loan $loan_id to $minAgent <br/>";
            }
            // echo "Loan $loan_id, CC $minAgent <br/>";

        }
        echo " $all_allocated/" . sizeof($FA_loans) . " Accounts allocated to FA Eastern<br/>";

        $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
        $log = addtodbmulti('o_events', $fds, ltrim($mass_logging, ","));




        ///////WESTERN WESTERN WESTERN WESTERN WESTERN WESTERN WESTERN WESTERN WESTERN WESTERN WESTERN
        $mass_logging = "";
        $all_allocated = 0;

        for ($i = 0; $i < sizeof($FA_loans_western); ++$i) {
            $loan_id = $FA_loans_western[$i];
            $loan_balance = $loan_balances_array[$loan_id];

            $minValue = min($FA_allocations_western); // Get the minimum value from the array
            $minAgent = array_search($minValue, $FA_allocations_western); // Find the agent with least value
            /////---------------Save allocation to database, add logs
            /////---------------Update allocation total
            $FA_allocations_western = obj_add($FA_allocations_western, $minAgent, $loan_balance);
            $agent_name = $agent_names_array[$minAgent];

            // echo "$agent_name $minAgent, $loan_id <br/>";


            $allocate = updatedb('o_loans', "current_agent='$minAgent', allocation='FA'", "uid='$loan_id'");
            if ($allocate == 1) {
                $all_allocated += 1;
                $mess = "Loan allocated to FA Agent $agent_name($minAgent) by automated system";
                //---Too expensive to save one by one
                $mass_logging = $mass_logging . ',("o_loans","' . $loan_id . '","' . $mess . '","' . $fulldate . '","0","1")';
            } else {
                echo "Error allocating Loan $loan_id to $minAgent <br/>";
            }
            // echo "Loan $loan_id, CC $minAgent <br/>";

        }
        echo " $all_allocated/" . sizeof($FA_loans) . " Accounts allocated to FA Western<br/>";

        $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
        $log = addtodbmulti('o_events', $fds, ltrim($mass_logging, ","));
        */
}



if (isset($_GET['IDC'])) {

    //////------------------IDC Allocation
    $mass_logging = "";
    $all_allocated = 0;

    for ($i = 0; $i < sizeof($IDC_loans); ++$i) {
        $loan_id = $IDC_loans[$i];
        $loan_balance = $loan_balances_array[$loan_id];
        $loan_branch = intval($IDC_loan_branches[$loan_id] ?? 0);

        // update to account for special case branches
        if ($loan_branch > 0 && in_array($loan_branch, $special_case_branches)) {
            $minAgent = $special_case_branches_allocation[$loan_branch];
            $agent_name = $agent_names_array[$minAgent];
        } else {

            continue; // skip this loan if it doesn't belong to a special case branch

            // $minValue = min($IDC_allocations); // Get the minimum value from the array
            // $minAgent = array_search($minValue, $IDC_allocations); // Find the agent with least value
            // /////---------------Save allocation to database, add logs
            // /////---------------Update allocation total
            // $IDC_allocations = obj_add($IDC_allocations, $minAgent, $loan_balance);
            // $agent_name = $agent_names_array[$minAgent];
        }


        $allocate = updatedb('o_loans', "current_agent='$minAgent', allocation='IDC'", "uid='$loan_id'");
        if ($allocate == 1) {
            $all_allocated += 1;
            $mess = "Loan allocated to IDC Agent $agent_name($minAgent) by automated system";
            //---Too expensive to save one by one
            $mass_logging = $mass_logging . ',("o_loans","' . $loan_id . '","' . $mess . '","' . $fulldate . '","0","1")';

             /// === store allocation history
             try{
                $alloc_fds = ['loan_id', 'allocation_type', 'allocation_date', 'allocation_datetime', 'allocation_amount', 'allocated_agent', 'status'];
                $alloc_vals = [$loan_id, "IDC", "$date", "$fulldate", $loan_balance, $minAgent, 1];

                addtodb("o_allocation_history", $alloc_fds, $alloc_vals);

            }catch(Exception $e){
                echo "Error: " . $e->getMessage();
            }

        } else {
            echo "Error allocating Loan $loan_id to $minAgent <br/>";
        }
        // echo "Loan $loan_id, CC $minAgent <br/>";


    }
    echo " $all_allocated/" . sizeof($IDC_loans) . " Accounts allocated to IDC <br/>";

    $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
    $log = addtodbmulti('o_events', $fds, ltrim($mass_logging, ","));
}


if (isset($_GET['EDC'])) {
    //////------------------EDC Allocation
    $mass_logging = "";
    $all_allocated = 0;

    for ($i = 0; $i < sizeof($EDC_loans); ++$i) {
        $loan_id = $EDC_loans[$i];
        $loan_balance = $loan_balances_array[$loan_id];

        $minValue = min($EDC_allocations); // Get the minimum value from the array
        $minAgent = array_search($minValue, $EDC_allocations); // Find the agent with least value
        /////---------------Save allocation to database, add logs
        /////---------------Update allocation total
        $EDC_allocations = obj_add($EDC_allocations, $minAgent, $loan_balance);
        $agent_name = $agent_names_array[$minAgent];


        $allocate = updatedb('o_loans', "current_agent='$minAgent', allocation='EDC'", "uid='$loan_id'");
        if ($allocate == 1) {
            $all_allocated += 1;
            $mess = "Loan allocated to EDC Agent $agent_name($minAgent) by automated system";
            //---Too expensive to save one by one
            $mass_logging = $mass_logging . ',("o_loans","' . $loan_id . '","' . $mess . '","' . $fulldate . '","0","1")';

             /// === store allocation history
             try{
                $alloc_fds = ['loan_id', 'allocation_type', 'allocation_date', 'allocation_datetime', 'allocation_amount', 'allocated_agent', 'status'];
                $alloc_vals = [$loan_id, "EDC", "$date", "$fulldate", $loan_balance, $minAgent, 1];

                addtodb("o_allocation_history", $alloc_fds, $alloc_vals);

            }catch(Exception $e){
                echo "Error: " . $e->getMessage();
            }
        } else {
            echo "Error allocating Loan $loan_id to $minAgent <br/>";
        }
        // echo "Loan $loan_id, CC $minAgent <br/>";


    }
    echo " $all_allocated/" . sizeof($EDC_loans) . " Accounts allocated to EDC <br/>";

    $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
    $log = addtodbmulti('o_events', $fds, ltrim($mass_logging, ","));
}

include_once("../configs/close_connection.inc");
