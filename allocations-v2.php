<?php
session_start();
include_once("php_functions/functions.php");
include_once("configs/conn.inc");
include_once("php_functions/authenticator.php");

$userd = session_details();

$company = company_settings();

$branch_regions = table_to_obj('o_branches', "uid > 0", "10000", "uid", "region_id");
$branches = table_to_obj('o_branches', "uid > 0", "10000", "uid", "name");
$regions = table_to_obj('o_regions', "uid > 0", "10000", "uid", "name");
$flags = table_to_obj2('o_flags', "uid > 0", "1000", "uid", ['name', 'color_code']);

if (isset($_GET['from'])) {
    $start_date = $_GET['from'];
} else {
    $start_date = $date;
}
if (isset($_GET['to'])) {
    $end_date = $_GET['to'];
} else {
    $end_date = $date;
}

$team_leaders = table_to_obj('o_team_leaders', "status=1", "1000", "agent_id", "leader_id");
$leader_colors = array();
$colors = array('#FF0000', '#316e41', '#237ba7', '#522028', '#786e65', '#2e8b57', '#008080', '#110e12', '#7e273f', '#bb373f', '#e84e79', '#e84e79');

foreach ($team_leaders as $aid => $lid) {
    $pos = rand(0, 12);
    $color = $colors[$pos];
    $leader_colors[$lid] = $color;
}
///----Read permissions for branches
$branchCondition = getBranchCondition($userd, 'o_loans', 'current_branch');
$branchLoanCondition = $branchCondition['branchLoanCondition'] ?? "";


/// -----End of read permissions for branches



?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $company['name']; ?> | Loans</title>
    <!-- Tell the browser to be responsive to screen width -->
    <?php
    include_once('header_includes.php');
    ?>

</head>

<body class="hold-transition skin-purple sidebar-mini">
    <div class="wrapper">

        <?php
        include_once('header.php');
        ?>
        <!-- Left side column. contains the logo and sidebar -->
        <?php
        include_once('menu.php');
        ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <?php
            if (isset($_GET['BRANCH'])) {
                $g = 7;
                $group = 'BRANCH';
            } elseif (isset($_GET['CC'])) {
                $g = 12;
                $group = 'CC';
            } elseif (isset($_GET['FA'])) {
                $g = 13;
                $group = 'FA';
            } elseif (isset($_GET['IDC'])) {
                $g = 21;
                $group = 'IDC';
            } elseif (isset($_GET['EDC'])) {
                $g = 14;
                $group = 'EDC';
            } else {
                $g = 7;
                $group = 'BRANCH';
            }

            if (isset($_GET['SHOW-CLOSED'])) {
                $closed = " AND paid=1";
            } else {
                $closed = " ";
            }
            // var_dump($team_leaders);
            ?>

            <!-- Main content -->


            <section class="content-header">
                <h1>
                    Allocations
                    <small>List</small>

                </h1>
                <ol class="breadcrumb">
                    <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Allocations</li>

                </ol>
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-xs-6">
                        <a href="?BRANCH" class="btn btn-default btn-md" id="BRANCH">Branch</a>
                        <a href="?CC" class="btn btn-default btn-md" id="CC">CC</a>
                        <a href="?FA" class="btn btn-default btn-md" id="FA">FA</a>
                        <a href="?IDC" class="btn btn-default btn-md" id="IDC">IDC</a>
                        <a href="?EDC" class="btn btn-default btn-md" id="EDC">EDC</a>

                        <?php
                        $show_closed = session_variables('READ', 'show_closed');
                        $show_past = session_variables('READ', 'show_past');
                        // echo $branchLoanCondition.',,,,,';

                       /////////------ Show/hide closed accounts
                        if ($show_closed == 1) {
                            $and_paid = "";
                            echo "<button onclick=\"session_variable('ADD','show_closed',0, 1)\" class='btn btn-default text-blue font-bold btn-sm font-14 pull-right'><i class='fa fa-eye'></i> Hide Closed Accounts </button>";
                        } else {
                            $and_paid = " AND paid=0";
                            echo "<button onclick=\"session_variable('ADD','show_closed',1, 1)\" class='btn btn-default text-red font-bold btn-sm font-14 pull-right'><i class='fa fa-eye-slash'></i> Show Closed Accounts </button>";
                        }

                        //////-----Show/hide older accounts
                        if($show_past == 1){
                            $and_staff_status = " AND status > 0"; ////--Show all
                            echo "<button onclick=\"session_variable('ADD','show_past',0, 1)\" class='btn btn-default text-blue font-bold btn-sm font-14 pull-right'><i class='fa fa-eye'></i> Hide Past Agents </button>";
                        }
                        else
                        {
                            $and_staff_status = " AND status = 1";
                            echo "<button onclick=\"session_variable('ADD','show_past',1, 1)\" class='btn btn-default text-red font-bold btn-sm font-14 pull-right'><i class='fa fa-eye-slash'></i> Show Past Accounts </button>";
                        }


                        ?>

                        <?php
                        if (isset($_GET['agent'])) {
                            $ag = decurl($_GET['agent']);
                            $agent = fetchrow('o_users', "uid='$ag'", "name");
                            echo " <span class='font-18 font-bold text-green'> <i class='fa fa-user-circle-o'></i> $agent </span> <a class='font-italic' href=\"reports?hreport=sl-agent-closed-accounts.php&from=$date&to=$date&branch=0&agent=$ag\" target='_blank'>My Collections <i class='fa fa-external-link-square'></i></a>";
                        }
                        ?>
                    </div>

                    <div class="col-xs-6">

                        <input type="date" title="From" class="btn btn-default bg-gray" value="<?php echo $start_date; ?>" id="from_date"> <i class="fa fa-arrow-right"></i>
                        <input type="date" class="btn btn-default bg-gray" title="To" id="to_date" value="<?php echo $end_date; ?>"> <button class="btn btn-success" onclick="alloc_dates('<?php echo $group; ?>','<?php echo encurl($ag); ?>');"> RUN <i class="fa fa-arrow-right"></i></button>

                        <button style="display: nonse;" class="btn btn-github pull-right" title="Upload new allocations" data-toggle="modal" data-target="#upload_allocations"> <i class="fa fa-upload"></i> Upload</button>


                    </div>


                </div>

                <div class="xxx">

                    <div class="nav-tabs-custom">

                        <div class="tab-content scroll-hor">
                            <div class="tab-pane active" id="tab_2">
                                <?php

                                if (isset($_GET['agent'])) {
                                    $agent_eid = $_GET['agent'];
                                ?>

                                    <table id="tbl1" class="table table-condensed table-striped table-bordered table-hover">
                                        <thead class="bg-black-gradient">
                                            <tr>
                                                <th>UID</th>
                                                <th>Client Name</th>
                                                <th>Flag</th>
                                                <th>Client Phone</th>
                                                <th>Branch</th>
                                                <th>Given Date</th>
                                                <th>_Due_Date_</th>
                                                <th>Loan Total</th>
                                                <th>Paid</th>
                                                <th>Port Start Bal</th>
                                                <th>Collected by Agent</th>
                                                <th>Agent Coll. Rate</th>
                                                <th>Balance</th>
                                                <th>Total Coll. Rate</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php


                                            $agent = decurl($_GET['agent']);
                                            $allLoansInPayments = array(0);
                                            $loan_collections_array = array();
                                            $collected_by_agent_total = 0;
                                            $branches = table_to_obj('o_branches', "uid >0", "100000", "uid", "name");

                                            $payments = fetchtable('o_incoming_payments', "collected_by =$agent AND payment_date BETWEEN '$start_date' AND '$end_date' AND status = 1", "uid", "asc", "10000000", "uid, amount, loan_id");
                                            while ($p = mysqli_fetch_array($payments)) {
                                                $pid = $p['uid'];
                                                $amount = $p['amount'];
                                                $loan_id = $p['loan_id'];

                                                $loan_collections_array = obj_add($loan_collections_array, $loan_id, $amount);

                                                array_push($allLoansInPayments, $loan_id);
                                            }
                                            $also_loans_in_payments = implode(",", $allLoansInPayments);

                                            $customer_ids = table_to_array('o_loans', "current_agent = $agent AND disbursed=1 $and_paid AND status!=0 OR uid in ($also_loans_in_payments)", "1000000", "customer_id");

                                            $customer_ids_string = implode(',', $customer_ids);

                                            $customer_names = table_to_obj2('o_customers', "uid in ($customer_ids_string)", "1000000", "uid", ["full_name", "flag"]);



                                            $loans = fetchtable('o_loans', "current_agent = $agent AND disbursed=1 $and_paid AND status!=0 OR uid in ($also_loans_in_payments)", "uid", "desc", "10000000", "uid, total_repayable_amount, total_repaid, loan_balance, account_number, paid, customer_id, given_date, final_due_date, current_branch, loan_flag");
                                            while ($l = mysqli_fetch_array($loans)) {
                                                $luid = $l['uid'];
                                                $total_repayable_amount = $l['total_repayable_amount'];
                                                $total_paid = $l['total_repaid'];
                                                $loan_balance = $l['loan_balance'];
                                                $paid = $l['paid'];
                                                $client_id = $l['customer_id'];
                                                $given_date = $l['given_date'];
                                                $final_due_date = $l['final_due_date'];
                                                $current_branch = $l['current_branch'];
                                                $account_number = $l['account_number'];

                                                $branch_name = $branches[$current_branch];

                                                $clients_arr = $customer_names[$client_id];
                                                $client_name = $clients_arr['full_name'];
                                                $flag = $clients_arr['flag'];
                                                $flag_arr = $flags[$flag];
                                                $flag_name = $flag_arr['name'];
                                                $flag_color = $flag_arr['color_code'];


                                                $int = "<a onclick=\"interactions_popup('" . encurl($client_id) . "')\"><i class=\"fa fa-comments-o\"></i></a>";
                                                $goto = "<a target=\"_BLANK\" href=\"customers?customer=" . encurl($client_id) . "#tab_3\"><i class=\"fa  fa-external-link\"></i></a>";

                                                $total_collection_rate = round(($total_paid / $total_repayable_amount) * 100, 2);
                                                $collected_by_agent = $loan_collections_array[$luid];
                                                $portfolio_start_balance = $total_repayable_amount - ($total_paid - $collected_by_agent);

                                                $agent_collection_rate = round(($collected_by_agent / $portfolio_start_balance) * 100, 2);


                                                $loan_total = $loan_total + $total_repayable_amount;
                                                $total_paid_total = $total_paid_total + $total_paid;
                                                $port_start_balance_total = $port_start_balance_total + $portfolio_start_balance;
                                                $collected_by_agent_total = $collected_by_agent_total + $collected_by_agent;
                                                $balance_total = $balance_total + $loan_balance;

                                                $ddplus = datediff($final_due_date, $date);

                                                if ($group == 'BRANCH' && $ddplus == 15) {
                                                    $info = "<a title='Account will move to CC tonight'><i class='fa bell fa-bell text-red'></i></a>";
                                                } elseif ($group == 'CC' && $ddplus == 45) {
                                                    $info = "<a title='Account will move to FA tonight'><i class='fa bell fa-bell text-red'></i></a>";
                                                } elseif ($group == 'FA' && $ddplus == 105) {
                                                    $info = "<a title='Account will move to IDC tonight'><i class='fa bell fa-bell text-red'></i></a>";
                                                } elseif ($group == 'IDC' && $ddplus == 135) {
                                                    $info = "<a title='Account will move to EDC tonight'><i class='fa bell fa-bell text-red'></i></a>";
                                                } else {
                                                    $info = "";
                                                }

                                                if ($loan_balance < 1) {
                                                    // continue;
                                                }

                                                echo "<tr><td>$luid</td><td><b>$client_name</b>
                                    </td><td><span class='font-13 font-bold' style='color: $flag_color;'><i class='fa fa-flag'></i> $flag_name</span></td><td> $account_number</td><td><i>$branch_name</i></td><td>$given_date</td><td>$info $final_due_date <br/><span class='font-italic text-blue font-bold'>DD+$ddplus</span></td><td>" . money($total_repayable_amount) . "</td><td>" . money($total_paid) . "</td><td class='font-bold font-bold bg-gray'>" . money($portfolio_start_balance) . "</td><td class='font-bold font-bold bg-gray'>" . money(false_zero($collected_by_agent)) . "</td>
                                    <td class='font-bold font-bold bg-gray'>$agent_collection_rate%</td><td>" . money($loan_balance) . "</td><td>$total_collection_rate%</td><th>$int | $goto</th></tr>";
                                            }

                                            $total_agent_collection_rate = round(($collected_by_agent_total / $port_start_balance_total) * 100, 2);
                                            $total_collection_rate_total = round(($total_paid_total / $loan_total) * 100, 2);
                                            ?>
                                        </tbody>
                                        <tfoot class="bg-black-gradient">
                                            <tr>
                                                <th>--</th>
                                                <th>--</th>
                                                <th>--</th>
                                                <th>--</th>
                                                <th>--</th>
                                                <th>--</th>
                                                <th>--</th>
                                                <th><?php echo money($loan_total); ?></th>
                                                <th><?php echo money($total_paid_total); ?></th>
                                                <th><?php echo money($port_start_balance_total); ?></th>
                                                <th><?php echo money($collected_by_agent_total); ?></th>
                                                <th><?php echo ($total_agent_collection_rate); ?>%</th>
                                                <th><?php echo money($balance_total); ?></th>
                                                <th><?php echo ($total_collection_rate_total); ?>%</th>
                                                <th>--</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <div class="pull-right well well-sm">

                                        <?php
                                        $unallocate = permission($userd['uid'], 'o_loans', "0", "update_");
                                        if ($unallocate == 1) {
                                        ?>
                                            <button class="btn bg-blue" onclick="modal_view('/forms/allocation_move.php','agent=<?php echo $agent_eid; ?>','Reallocate accounts to New Agent'); modal_show();  "><i class="fa fa-exchange"></i> Reallocate Accounts</button>


                                            <button class="btn bg-red" onclick="remove_allocations('<?php echo $agent_eid; ?>')"><i class="fa fa-times-circle"></i> Remove Allocations</button>

                                        <?php
                                        }
                                        ?>
                                    </div>



                            </div>
                        <?php
                                } else {
                        ?>
                            <div class="tab-pane active" id="tab_1">
                                <table id="tbl2" class="table table-condensed table-striped table-bordered table-hover">
                                    <thead class="bg-black-gradient">
                                        <tr>
                                            <th>AgentID</th>
                                            <th>Agent Name</th>
                                            <th>portfolio Amt</th>
                                            <th>Paid</th>
                                            <th>Port Start Bal</th>
                                            <th>Collected by Agent</th>
                                            <th>Balance</th>
                                            <th>Agent Coll. Rate</th>
                                            <th>Total Coll. Rate</th>
                                            <th>Total Loans</th>
                                            <th>Closed Loans</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $abranches = array();
                                        $agent_array = array();
                                        $agent_branches = fetchtable('o_staff_branches', "status=1", "uid", "asc", "1000000", "agent, branch");
                                        while ($ab = mysqli_fetch_array($agent_branches)) {
                                            $agent_uid = $ab['agent'];
                                            $branch_uid = $ab['branch'];
                                            $branch_name = $branches[$branch_uid];
                                            if (isset($agent_array[$agent_uid])) {
                                                array_push($agent_array[$agent_uid], $branch_name);
                                            } else {
                                                $nestedArray = array($branch_name);
                                                $agent_array[$agent_uid] = $nestedArray;
                                            }
                                        }




                                        $agents = table_to_obj('o_users', "user_group='$g' $and_staff_status", "10000", "uid", "name");
                                        $agent_branches = table_to_obj('o_users', "user_group='$g' $and_staff_status", "10000", "uid", "branch");
                                        $agent_tags = table_to_obj('o_users', "user_group='$g' $and_staff_status", "10000", "uid", "tag");

                                        $all_agent_names = array();

                                        $agen = fetchtable('o_users', "uid>0 $and_staff_status", "uid", "asc", "10000", "uid, branch, tag, name");
                                        while ($aa = mysqli_fetch_array($agen)) {
                                            $agid = $aa['uid'];
                                            $aname_ = $aa['name'];
                                            $abranch = $aa['branch'];
                                            $atags = $aa['tag'];
                                            $all_agent_names[$agid] = $aname_;
                                        }
                                        $all_leaders_names = table_to_obj('o_users', "uid>0 $and_staff_status", "10000", "uid", "name");

                                        $agentsString = implode(',', array_keys($agents));
                                        $agentsAllPaymentsArray = array();
                                        $allLoansInPayments = array(0);
                                        $agentCollectionsArray = array();

                                        $payments = fetchtable('o_incoming_payments', "collected_by in ($agentsString) AND payment_date BETWEEN '$start_date' AND '$end_date' AND status = 1", "uid", "desc", "10000000", "uid, amount, collected_by, loan_id");
                                        while ($p = mysqli_fetch_array($payments)) {
                                            $pid = $p['uid'];
                                            $amount = $p['amount'];
                                            $coll = $p['collected_by'];
                                            $loan_id = $p['loan_id'];


                                            $agentCollectionsArray = obj_add($agentCollectionsArray, $coll, $amount);

                                            array_push($agentsAllPaymentsArray[$coll], $pid);
                                            array_push($allLoansInPayments, $loan_id);
                                        }

                                        $total_repayable_amount_per_agent_array = array();
                                        $repayments_per_agent_loan_array = array();
                                        $agents_loan_balance = array();
                                        $total_loans_array = array();
                                        $total_loans_cleared_array = array();
                                        $also_loans_in_payments = implode(",", $allLoansInPayments);
                                        $loans = fetchtable('o_loans', "current_agent in ($agentsString) AND disbursed=1 $and_paid AND status!=0 OR uid in ($also_loans_in_payments)", "uid", "asc", "10000000", "uid, current_agent, total_repayable_amount ,total_repaid, loan_balance, paid");
                                        while ($l = mysqli_fetch_array($loans)) {
                                            $luid = $l['uid'];
                                            $lagent = $l['current_agent'];
                                            $total_repayable_amount = $l['total_repayable_amount'];
                                            $total_paid = $l['total_repaid'];
                                            $loan_balance = $l['loan_balance'];
                                            $paid = $l['paid'];



                                            $total_repayable_amount_per_agent_array = obj_add($total_repayable_amount_per_agent_array, $lagent, $total_repayable_amount);
                                            $repayments_per_agent_loan_array = obj_add($repayments_per_agent_loan_array, $lagent, $total_paid);
                                            $agents_loan_balance = obj_add($agents_loan_balance, $lagent, $loan_balance);
                                            $total_loans_array = obj_add($total_loans_array, $lagent, 1);
                                            if ($paid == 1) {
                                                $total_loans_cleared_array = obj_add($total_loans_cleared_array, $lagent, 1);
                                            }
                                        }

                                        $pos = 0;
                                        //echo $colors[0];
                                        foreach ($agents as $aid => $aname) {



                                            $atag = $agent_tags[$aid];
                                            $team_leader = $team_leaders[$aid];
                                            if ($team_leader > 0) {

                                                $color = $leader_colors[$team_leader];
                                                $team_leader_name = $all_leaders_names[$team_leader];
                                                $tl = "<br/><span class='font-13 bg-orange-active label' style='background-color:$color !important;'>TL: $team_leader_name</span>";
                                            } else {
                                                $tl = "";
                                            }
                                            if ($atag == 'A') {
                                                $atagx = "<span class=\"label label-success\">$atag</span>";
                                            } elseif ($atag == 'B') {
                                                $atagx = "<span class=\"label label-primary\">$atag</span>";
                                            } elseif ($atag == 'C') {
                                                $atagx = "<span class=\"label label-warning text-black\">$atag</span>";
                                            }
                                            $pos += 1;
                                            $portfolio_amount = $total_repayable_amount_per_agent_array[$aid];
                                            $portfolio_paid = $repayments_per_agent_loan_array[$aid];
                                            $collected_by_agent = $agentCollectionsArray[$aid];
                                            $balance = $agents_loan_balance[$aid];
                                            $total_loans = $total_loans_array[$aid];
                                            $total_loans_cleared = $total_loans_cleared_array[$aid];
                                            $portfolio_start_balance = $portfolio_amount - ($portfolio_paid - $collected_by_agent);

                                            $agent_branches_ = $agent_array[$aid];
                                            $agent_branch_string = implode(',', $agent_branches_);

                                            $port_amount_total = $port_amount_total + $portfolio_amount;
                                            $paid_total = $paid_total + $portfolio_paid;
                                            $port_start_balance_total = $port_start_balance_total + $portfolio_start_balance;
                                            $collected_by_agent_total = $collected_by_agent_total + $collected_by_agent;
                                            $balance_total = $balance_total + $balance;

                                            $total_loans_total = $total_loans_total + $total_loans;
                                            $closed_loans_total = $closed_loans_total + $total_loans_cleared;

                                            $branch = $branches[$agent_branches[$aid]];
                                            $region = $regions[$branch_regions[$agent_branches[$aid]]];



                                            $total_collection_rate = round(($portfolio_paid / $portfolio_amount) * 100, 2);
                                            $agent_collection_rate = round(($collected_by_agent / $portfolio_start_balance) * 100, 2);

                                            $view = "<a href=\"?agent=" . encurl($aid) . "&$group\"><i class=\"fa fa-eye\"></i></a>";

                                            echo " <tr><td>$aid</td><td>$atagx$aname<br/><span class='text-bold text-muted'>$agent_branch_string</span> $tl</td><td>" . money($portfolio_amount) . "</td><td>" . money($portfolio_paid) . "</td><td class='font-bold font-bold bg-gray'>" . money($portfolio_start_balance) . "</td><td class='font-bold font-bold bg-gray'>" . money(false_zero($collected_by_agent)) . "</td><td class='font-bold bg-gray'>" . money($balance) . "</td>
                                    <td class='font-bold font-italic text-blue bg-gray'>$agent_collection_rate%</td><td>$total_collection_rate%</td><td>$total_loans</td><td>" . false_zero($total_loans_cleared) . "</td><td>$view</td></tr>";
                                        }
                                        $agent_collection_rate_total = round(($collected_by_agent_total / $port_start_balance_total) * 100, 2);
                                        $total_collection_rate_total = round(($paid_total / $port_amount_total) * 100, 2);


                                        ?>

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>--</th>
                                            <th>--</th>
                                            <th><?php echo money($port_amount_total); ?></th>
                                            <th><?php echo money($paid_total); ?></th>
                                            <th><?php echo money($port_start_balance_total); ?></th>
                                            <th><?php echo money($collected_by_agent_total); ?></th>
                                            <th><?php echo money($balance_total); ?></th>
                                            <th><?php echo ($agent_collection_rate_total); ?>%</th>
                                            <th><?php echo ($total_collection_rate_total); ?></th>
                                            <th><?php echo $total_loans_total; ?></th>
                                            <th><?php echo $closed_loans_total; ?></th>
                                            <th>--</th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <hr />
                                <div class="pull-right well well-sm">
                                    <?php
                                    $unallocate = permission($userd['uid'], 'o_loans', "0", "update_");
                                    if ($unallocate == 1) {
                                    ?>
                                        <button class="btn bg-purple" onclick="allocation_soft_reshuffle('<?php echo $group; ?>');"><i class="fa fa-refresh"></i> Soft Reshuffle</button>
                                        <button class="btn bg-red" onclick="allocation_hard_reshuffle('<?php echo $group; ?>');"><i class="fa fa-recycle"></i> Hard Reshuffle</button>

                                    <?php
                                    }
                                    ?>
                                </div>




                            </div>

                        <?php
                                }
                        ?>

                        </div>

                    </div>

                </div>

            </section>



            <!-- /.content -->
        </div>

        <!-- /.content-wrapper -->
        <?php
        include_once("footer.php");
        ?>


        <!-- Control Sidebar -->

        <!-- /.control-sidebar -->
        <!-- Add the sidebar's background. This div must be placed
         immediately after the control sidebar -->
        <div class="control-sidebar-bg"></div>
    </div>


    <div class="modal fade" id="upload_allocations">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Upload New Allocations for <b><?php echo $group; ?></b></h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal" id="alloc-upload" method="POST" action="action/system/allocations" enctype="multipart/form-data">
                        <div class="box-body">



                            <div class="form-group">
                                <label for="loans_" class="col-sm-3 control-label">New Allocations (CSV)</label>

                                <div class="col-sm-9">
                                    <input type="hidden" name="type_" value="<?php echo $group ?>">
                                    <input type="file" id="loans_" name="file_" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="agree" class="col-sm-3 control-label">Lock this allocation? <a title="If locked, this accounts won't move automatically to other vintages unless you upload them again with this option unchecked"><i class="fa fa-info-circle"></i> </a></label>
                                <div class="col-sm-9">
                                    <input type="checkbox" id="agree" name="agree" value="yes">
                                </div>

                            </div>


                            <div class="col-sm-3"></div>
                            <div class="col-sm-9">
                                <div class="box-footer">
                                    <div class="prgress">
                                        <div class="messagealloc-upload" id="message"></div>
                                        <div class="progressalloc-upload" id="progress">
                                            <div class="baralloc-upload" id="bar"></div>
                                            <br>
                                            <div class="percentalloc-upload" id="percent"></div>
                                        </div>
                                    </div>
                                    <br />

                                </div>
                            </div>
                            <div class="col-sm-6">
                                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                            </div>
                            <div class="col-sm-6">
                                <input type="submit" onclick="formready('alloc-upload');" class="btn btn-success" value="Submit" />
                            </div>


                        </div>
                        <!-- /.box-body -->

                        <!-- /.box-footer -->
                    </form>




                </div>
                <div class="modal-footer">

                </div>
            </div>

        </div>

    </div>
    <!-- ./wrapper -->

    <?php
    include_once("footer_includes.php");
    ?>

    <script>
        $(function() {
            $('#<?php echo $group; ?>').addClass('btn-primary').removeClass('btn-default');
            document.title = "<?php echo $company['name'] . '-' . $group; ?> Allocations";
            $('#tbl1').DataTable({
                dom: 'Bfrtip',
                "pageLength": 10,
                order: [
                    [4, 'asc']
                ],
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ],
            });

            $('#tbl2').DataTable({
                dom: 'Bfrtip',
                "pageLength": 25,
                order: [
                    [0, 'asc']
                ],
                buttons: [
                    'copy', 'csv', 'excel', 'pdf', 'print'
                ]
            });

            $('.bell').fadeOut('slow');
            $('.bell').fadeIn('slow');

        });
    </script>

</body>

</html>