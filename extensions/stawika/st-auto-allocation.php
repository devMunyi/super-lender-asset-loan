<?php
session_start();
include_once ("../configs/20200902.php");
$_SESSION['db_name'] = $db_;
include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

//$agent_allocations = table_to_obj('o_loans',"disbursed=1 AND paid=0 AND status!=0 AND current_agent (12, 13, 14, 21)","100000","current_agent","loan_balance");
///---agent allocations now looks like $agent_allocations[1]=1000, $agent_allocations[2] = 3000;
$branch_regions = table_to_obj('o_branches',"uid > 0","10000","uid","region_id");
$branches_array = table_to_array('o_branches',"uid > 0","10000","uid");
$CC_agents = array();
$FA_agents = array();
//$FA_agents_central = array();
//$FA_agents_eastern = array();
//$FA_agents_western = array();
$IDC_agents = array();
$EDC_agents = array();
$all_agents = array();

$agent_names_array = array();

$agents = fetchtable('o_users',"status=1","uid","asc","10000","uid, user_group, name, branch");
while($a = mysqli_fetch_array($agents)){
 $user = $a['uid'];
 $ugroup = $a['user_group'];
 $aname = $a['name'];
 $abranch = $a['branch'];


    $agent_names_array[$user] = $aname;
 if($ugroup ==  12) {
     array_push($CC_agents, $user);
     array_push($all_agents, $user);
 }
 elseif ($ugroup == 13){

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

 }
 elseif ($ugroup == 14){
     array_push($EDC_agents, $user);
     array_push($all_agents, $user);
 }
 elseif ($ugroup == 21){
     array_push($IDC_agents, $user);
     array_push($all_agents, $user);
 }

}

$CC_agents_list = implode(',', $CC_agents);
$FA_agents_list = implode(',', $FA_agents);
$EDC_agents_list = implode(',', $EDC_agents);
$IDC_agents_list = implode(',', $IDC_agents);
$all_agents_list = implode(',', $all_agents);


$CC_allocations = array_fill_keys($CC_agents, 0);
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
$agent_allocations = fetchtable('o_loans',"disbursed=1 AND paid=0 AND loan_balance > 5 AND status!=0 AND current_agent in ($all_agents_list)","uid","asc","100000000","current_agent,loan_balance, current_branch");
while ($al = mysqli_fetch_array($agent_allocations)){
    $agent = $al['current_agent'];
    $loan_balance = $al['loan_balance'];
    $current_branch = $al['current_branch'];

    if(in_array($agent, $CC_agents)) {
        $CC_allocations = obj_add($CC_allocations, $agent, $loan_balance);
    }
    elseif (in_array($agent, $FA_agents)) {
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

    }
    elseif (in_array($agent, $EDC_agents)) {
        $EDC_allocations = obj_add($EDC_allocations, $agent, $loan_balance);
    }
    elseif (in_array($agent, $IDC_agents)) {
          $IDC_allocations = obj_add($IDC_allocations, $agent, $loan_balance);
    }
}

////-----Check if users are allocated and default to 0

//echo json_encode($CC_allocations, true);

//die();
$CC_loans = array();
$FA_loans = array();
//$FA_loans_central = array();
//$FA_loans_eastern = array();
//$FA_loans_western = array();
$IDC_loans = array();
$EDC_loans = array();


$loan_balances_array = array();
$loan_branches =  array();
$loan_due_dates = array();
$loans = fetchtable('o_loans',"disbursed=1 AND paid=0 AND loan_balance > 5 AND  given_date <= '$date' AND status!= 0 AND given_date >=  '2022-12-01'","uid","asc","100000000000","uid, final_due_date, current_agent, loan_balance, current_branch, given_date");
while($l = mysqli_fetch_array($loans)){
    $uid = $l['uid'];
    $final_due_date = $l['final_due_date'];
    $current_agent = $l['current_agent'];
    $loan_balance = $l['loan_balance'];
    $current_branch = $l['current_branch'];
    $given_date = $l['given_date'];
    $loan_due_dates[$uid] = $final_due_date;
    $current_region = $branch_regions[$current_branch];

    $ago = datediff($given_date, $date);
    $loan_balances_array[$uid] = $loan_balance;
    $loan_branches[$uid] = $current_branch;

    if($ago > 0 AND $ago <= 200){
        ////-----Call centre
        /// Check if loan is already assigned to a CC agent
        if(!in_array($current_agent, $CC_agents)){
            array_push($CC_loans, $uid);
        }


    }

    elseif ($ago > 200){
        ////-----EDC
        /// Check if loan is already assigned to a IDC agent
        if(!in_array($current_agent, $EDC_agents)){
            array_push($EDC_loans, $uid);
        }
    }

}


if(isset($_GET['CC'])) {
    $mass_logging = "";
    $all_allocated = 0;
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
        $ldd = $loan_due_dates[$loan_id];


        $allocate = updatedb('o_loans', "current_agent='$minAgent', allocation='CC'", "uid='$loan_id'");
        if ($allocate == 1) {
            $all_allocated += 1;
            $mess = "Loan allocated to CC Agent $agent_name($minAgent) by automated system";
            //---Too expensive to save one by one
            $mass_logging = $mass_logging . ',("o_loans","' . $loan_id . '","' . $mess . '","' . $fulldate . '","0","1")';
        } else {
            echo "Error allocating Loan $allocate $loan_id ($ldd) to $minAgent <br/>";
        }
         echo "Loan $loan_id, CC $minAgent <br/>";


    }
    echo " $all_allocated/" . sizeof($CC_loans) . " Accounts allocated to CC <br/>";

    $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
    $log = addtodbmulti('o_events', $fds, ltrim($mass_logging, ","));


}

if(isset($_GET['FA'])) {

$branch_agents = array();

$agent_branches = fetchtable('o_staff_branches',"status=1 AND agent in ($FA_agents_list)","uid","asc","1000000","agent, branch");
while($ab = mysqli_fetch_array($agent_branches)){
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


if(isset($_GET['IDC'])) {

//////------------------IDC Allocation
    $mass_logging = "";
    $all_allocated = 0;

    for ($i = 0; $i < sizeof($IDC_loans); ++$i) {
        $loan_id = $IDC_loans[$i];
        $loan_balance = $loan_balances_array[$loan_id];

        $minValue = min($IDC_allocations); // Get the minimum value from the array
        $minAgent = array_search($minValue, $IDC_allocations); // Find the agent with least value
        /////---------------Save allocation to database, add logs
        /////---------------Update allocation total
        $IDC_allocations = obj_add($IDC_allocations, $minAgent, $loan_balance);
        $agent_name = $agent_names_array[$minAgent];


        $allocate = updatedb('o_loans', "current_agent='$minAgent', allocation='IDC'", "uid='$loan_id'");
        if ($allocate == 1) {
            $all_allocated += 1;
            $mess = "Loan allocated to IDC Agent $agent_name($minAgent) by automated system";
            //---Too expensive to save one by one
            $mass_logging = $mass_logging . ',("o_loans","' . $loan_id . '","' . $mess . '","' . $fulldate . '","0","1")';
        } else {
            echo "Error allocating Loan $loan_id to $minAgent <br/>";
        }
        // echo "Loan $loan_id, CC $minAgent <br/>";


    }
    echo " $all_allocated/" . sizeof($IDC_loans) . " Accounts allocated to IDC <br/>";

    $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
    $log = addtodbmulti('o_events', $fds, ltrim($mass_logging, ","));

}


if(isset($_GET['EDC'])) {
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
        } else {
            echo "Error allocating Loan $loan_id to $minAgent <br/>";
        }
        // echo "Loan $loan_id, CC $minAgent <br/>";


    }
    echo " $all_allocated/" . sizeof($EDC_loans) . " Accounts allocated to EDC <br/>";

    $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
    $log = addtodbmulti('o_events', $fds, ltrim($mass_logging, ","));


}

