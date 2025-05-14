<?php
session_start();
include_once ("php_functions/functions.php");
include_once ("configs/conn.inc");
include_once("php_functions/authenticator.php");

//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

$company = company_settings();

$userd = session_details();
$user_id = $userd['uid'];
$group_tag = $userd['tag'];

if(isset($_GET['from'])){
    $start_date = $_GET['from'];
}
if(isset($_GET['to'])){
    $end_date = $_GET['to'];
}

if(isset($_GET['from']) && isset($_GET['to'])){
    $andpdate = " AND payment_date BETWEEN '$start_date' AND '$end_date' ";
}
else{
    $andpdate = "AND payment_date BETWEEN '$date' AND '$date' ";
}


if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $company['name']; ?> | Allocations</title>
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
            <?php
        $branch_s = 'ACTIVE';
        $alloc = 'BRANCH';
        $tag = 'CO';
        $get_started = "";
        if($_GET['group']) {
            $gr = $_GET['group'];
            if ($gr == 'BRANCH') {
                $branch_s = 'btn btn-primary';
                $tag = 'CO';
            }
            elseif ($gr == 'CC') {
                $CC_s = 'btn btn-primary';
                $tag = 'CC';
            }
            elseif ($gr == 'FA') {
                $FA_s = 'btn btn-primary';
                $tag = 'FA';
            }
            elseif ($gr == 'EDC') {
                $EDC_s = 'btn btn-primary';
                $tag = 'EDC';
            }
            else{
                $branch_s = 'btn btn-primary';
            }
            $alloc = $gr;
            $andalloc = " AND allocation='$alloc' ";
        }
        else{
           $andalloc = "";
           
        }
        
        $andagent = $andco = "";
        $br_display  = $cc_display = $fa_display = $edc_display = 'NONE';
        if($group_tag == 'CO'){  
            $br_display = "ALL"; ////-Show just Branch Tag
            $andagent = " AND uid='$user_id'";
            $andco = " AND current_co='$user_id'";
        }
        elseif($group_tag == 'CC'){ 
            $cc_display = "ALL";
            $andagent = " AND uid='$user_id'";
            $andco = " AND current_agent='$user_id'";
        }
        elseif($group_tag == 'FA'){  $fa_display = "ALL";
            $andagent = " AND uid='$user_id'";
            $andco = " AND current_agent='$user_id'";
        }
        elseif($group_tag == 'EDC'){ $edc_display = "ALL";
        }
        elseif($group_tag == 'BRANCH-MANAGER'){ $br_display = "ALL";
        }
        elseif($group_tag == 'CC-MANAGER'){ $cc_display = "ALL";
        }
       elseif($group_tag == 'FA-MANAGER'){$fa_display = "ALL";
        }
       elseif($group_tag == 'EDC-MANAGER'){$edc_display = "ALL";
        }
      else{$br_display  = $cc_display = $fa_display = $edc_display = 'ALL';}

        if(isset($_GET['bdo']) || isset($_GET['group'])){

        }
        else{
        $get_started = "<h4 class='container'><i class='fa fa-hand-pointer-o'></i> Click one to get started </h4>";
        }

        $agents_array = array();
        $agent_name_array = array();
        $agent_branch_array = array();

        $branch_names_array = table_to_obj('o_branches',"uid > 0","1000","uid","name");
        $customer_names_array = table_to_obj('o_customers',"status in (1,2)","10000000","uid","full_name");

        
        $agent_tag_array = array();


        $agents = fetchtable('o_users',"status=1 $andagent  AND tag in ('$tag') ","uid","asc","100000","uid, name, tag,branch");
        while($a = mysqli_fetch_array($agents)){
            $aid = $a['uid'];
            $aname = $a['name'];
            $abranch = $a['branch'];
            $atag = $a['tag'];


            array_push($agents_array, $aid);
          
        }
        $agentsa = fetchtable('o_users',"status=1","uid","asc","100000","uid, name, tag,branch");
        while($aa = mysqli_fetch_array($agentsa)){
            $aid = $aa['uid'];
            $aname = $aa['name'];
            $abranch = $aa['branch'];
            $atag = $aa['tag'];


            $agent_name_array[$aid] = $aname;
            $agent_branch_array[$aid] = $abranch;
            $agent_tag_array[$aid] = $atag;

        }


        $total_allocated = $total_collected = $total_balance = $total_loans = 0;
        $bdo_branches = table_to_obj('o_users',"status!=0","100000","uid","branch");
        $branch_name_array = table_to_obj('o_branches',"status=1","1000","uid","name");
        $agent_loan_amount_array = array();
        $agent_loan_repayable_array = array();
        $agent_loan_repaid_array = array();
        $agent_loan_balance_array = array();
        $agent_loans = array();

        $agent_customers_array = array();
        $agent_customers_array = array();

        $loan_agents = array();
        $loan_customer_array = array();
        $loan_amounts_array = array();
        $loan_total_array = array();
        $loan_paid_array = array();
        $loan_balance_array = array();
        $loan_given_dates = array();
        $loan_due_dates = array();


        $agent_payments_array = array();
        $all_loans_array = table_to_array('o_loans',"status!=0 AND disbursed=1 $andalloc $andco","100000000","uid");
        $all_loans_list = implode(',', $all_loans_array);
        $all_payments = fetchtable('o_incoming_payments',"loan_id in ($all_loans_list) AND status=1 $andpdate","uid","asc","100000000","uid, loan_id, added_by, collected_by, amount");
        while($all_p = mysqli_fetch_array($all_payments)){
            $pid = $all_p['uid'];
            $lid = $all_p['loan_id'];
            $added_by = $all_p['collected_by'];
            $pamount = $all_p['amount'];
            $agent_payments_array = obj_add($agent_payments_array, $added_by, $pamount);
            // echo "$added_by, $pamount <br/>";
        }


        $loan_branches = array();
        $all_loans = fetchtable('o_loans',"status!=0 AND disbursed=1 $andalloc $andco","uid","asc","10000000","uid, loan_amount, total_repayable_amount, total_repaid, loan_balance, customer_id, current_co, current_agent, given_date, final_due_date, current_branch");
        while($l = mysqli_fetch_array($all_loans)){
            $lid = $l['uid'];
            $loan_amount = $l['loan_amount'];
            $total_repayable_amount = $l['total_repayable_amount'];
            $total_repaid = $l['total_repaid'];
            $loan_balance = $l['loan_balance'];
            $current_co = $l['current_agent'];
            $current_agent = $l['current_agent'];
            $current_branch = $l['current_branch'];
            $loan_branches[$lid] = $current_branch;
            if($current_agent > 0){
                $current_co = $current_agent;
               // echo $current_co;
            }
            $customer_id = $l['customer_id'];
            $given_date = $l['given_date'];
            $due_date = $l['final_due_date'];

            //////-----------Load to agents arrays
            $loan_agents[$lid] = $current_co;
            $loan_customer_array[$lid] = $customer_id;
            $loan_amounts_array[$lid] = $loan_amount;
            $loan_total_array[$lid] = $total_repayable_amount;
            $loan_paid_array[$lid] = $total_repaid;
            $loan_balance_array[$lid] = $loan_balance;
            $loan_given_dates[$lid] = $given_date;
            $loan_due_dates[$lid] = $due_date;
            

            /////-----------End of load to egents array


            $agent_loan_amount_array = obj_add($agent_loan_amount_array, $current_co, $loan_amount);
            $agent_loan_repayable_array = obj_add($agent_loan_repayable_array, $current_co, $total_repayable_amount);
            $agent_loan_repaid_array = obj_add($agent_loan_repaid_array, $current_co, $total_repaid);
            $agent_loan_balance_array = obj_add($agent_loan_balance_array, $current_co, $loan_balance);
            $agent_loans = obj_add($agent_loans, $current_co, 1);

            /////-----------Push all loans belonging to an agent to his account
            array_push($agent_loans[$current_co], $lid );


            $total_allocated = $total_allocated + $total_repayable_amount;
            $total_collected = $total_collected + $total_repaid;
            $total_balance = $total_balance + $loan_balance;
            $total_loans = $total_loans + 1;

        }
        $rate = round( ($total_collected/$total_allocated)*100,2);



            ?>
            <section class="content">
                <div class="row well card">
                    <div class="col-xs-12">

                        <!-- /.box -->

                        <div class="box">
                            <div class="box-header">
                                <div class="row">
                                    <div class="col-md-10">
                                      
                                        <ul class="nav nav-tabs">
                                            <li>
                                                <a style="display:<?php echo $br_display; ?>;" class="<?php echo $branch_s; ?>"  href="?group=BRANCH">Branch</a>
                                            </li>
                                            <li>
                                                <a style="display:<?php echo $cc_display; ?>;" class="<?php echo $CC_s; ?>" href="?group=CC">Call Centre</a>
                                            </li>
                                            <li>
                                                <a style="display:<?php echo $fa_display; ?>;" class="<?php echo $FA_s; ?>"  href="?group=FA">Field Agent</a>
                                            </li>
                                            <li>
                                                <a style="display:<?php echo $edc_display; ?>;" class="<?php echo $EDC_s; ?>" href="?group=EDC">External Debt Collectors</a>
                                            </li>
                                        </ul>

                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-success pull-right" title="Upload new allocations" data-toggle="modal" data-target="#upload_allocations"> <i class="fa fa-upload"></i> Upload</button>
                                    </div>


                                </div>
                               
                            </div>
                            <!-- /.box-header -->
                            <?php
                            echo $get_started;
                             if(isset($_GET['group'])){
                            ?>

                            <div class="box-body">
                                <div class="container p-3 my-3 bg-gray-light">
                                <table class="table table-bordered bg-black-gradient font-14" style="display: none;">
                                            <tr><td>Total Allocated</td><td class="text-bold"><?php echo money($total_allocated); ?></td> <td>Total Collected</td><td>Collected By Agents</td><td>0.00</td><td class="text-bold"><?php echo money($total_collected); ?></td></tr>
                                             <tr><td>Total Balance</td><td class="text-bold"><?php echo money($total_balance); ?> </td><td>Total Loans</td><td class="text-bold"><?php echo $total_loans; ?> </td><td>Rate</td><td><span class="label  font-24 text-orange"><?php echo false_zero($rate); ?>%</span></td></tr>
                                        </table>
                                  <div class="row well well-sm">
                                     <div class="col-md-3"> <h4>All <b><?php echo $alloc; ?></b> Agents </h4></div>
                                      <div class="col-xs-2">

                                         <input type="date" title="From" class="btn btn-default bg-gray" value="<?php echo $start_date; ?>" id="from_date"> <i class="fa fa-arrow-right"></i>
                                      </div>
                                      <div class="col-xs-2">
                                          <input type="date" class="btn btn-default bg-gray" title="To" id="to_date" value="<?php echo $end_date; ?>"> </div><div class="col-xs-2"><button class="btn btn-primary" onclick="alloc_dates('<?php echo $gr; ?>');"> RUN <i class="fa fa-arrow-right"></i></button>
                                      </div>


                                  </div>
                                   <table class="table table-bordered table-striped" id="tbl1">
                                       <thead>
                                       <tr><th>uid</th><th>Collector</th><th>Branch</th><th>Loan Amount</th><th>Total Repayable</th><th>Total Collected</th><th class="text-blue font-14">Portfolio Start Balance</th><th class="text-blue font-14">Collected By Agent</th><th class="text-blue font-14">Agent Balance</th><th class="text-blue font-14">Agent Coll. Rate</th><th>Total Balance</th><th class="">Total Coll. Rate</th><th>Total Loans</th><th>Action</th></tr>
                                       </thead>
                                       <tbody>
                                       <?php
                                            $collected_by_agent_total = 0;
                                           for($i=0; $i<sizeof($agents_array); ++$i){
                                               $agent = $agents_array[$i];
                                               $agent_name = $agent_name_array[$agents_array[$i]];
                                               $agent_branch = $branch_names_array[$agent_branch_array[$agents_array[$i]]];
                                               $agent_amount = $agent_loan_amount_array[$agents_array[$i]];
                                               $agent_repayable = $agent_loan_repayable_array[$agents_array[$i]];
                                               $agent_collected = $agent_loan_repaid_array[$agents_array[$i]]; ///---Wrong value
                                               $collected_by_agent = $agent_payments_array[$agents_array[$i]];
                                               $agent_balance = $agent_loan_balance_array[$agents_array[$i]];
                                               $agent_total = $agent_loans[$agents_array[$i]];

                                               $agent_rate = round((($collected_by_agent/ ($agent_balance-$collected_by_agent))*100), 2);
                                               //$agent_rate = 0.00;
                                               $portfolio_start_balance = $agent_balance+$collected_by_agent;
                                               $portfolio_agent_balance = $portfolio_start_balance - $collected_by_agent;

                                                /////Totals
                                                $agent_amount_t = $agent_amount_t + $agent_amount;
                                                $agent_repayable_t = $agent_repayable_t + $agent_repayable;
                                                $agent_collected_t = $agent_collected_t + $agent_collected;
                                                $agent_balance_t = $agent_balance_t + $agent_balance;
                                                $agent_total_t = $agent_total_t + $agent_total;
                                                $portfolio_start_balance_total = $portfolio_start_balance_total + $portfolio_start_balance;
                                                $collected_by_agent_total = $collected_by_agent_total + $collected_by_agent;
                                                $portfolio_agent_balance_total = $portfolio_agent_balance_total  + $portfolio_agent_balance;

                                               $rate = false_zero(round(($agent_collected/$agent_repayable)*100,2));
                                               $act = "<a href=\"?bdo=".encurl($agent)."\"><i class=\"fa fa-eye\"></i></a>";
                                              // $act = "";


                                               echo "<tr><td>".$agents_array[$i]."</td><td>$agent_name</td><td>$agent_branch</td><td>".money($agent_amount)."</td><td>".money($agent_repayable)."</td><td>".money($agent_collected)."</td><td class=\"text-blue font-bold font-14\">".money($portfolio_start_balance)."</td><td class=\"text-blue font-bold\">".money($collected_by_agent)."</td><td class=\"text-blue font-14 text-bold\">".money($portfolio_agent_balance)."</td><td class=\"font-14 text-blue font-bold\">".$agent_rate."%</td><td>".money($agent_balance)."</td><td class=\"\">$rate%
                                           </td><td>$agent_total</td><td>$act</td></tr>";
                                           }
                                           $rate_av = false_zero(round(($agent_collected_t/$agent_repayable_t)*100,2));
                                           $arate_av =  round((($collected_by_agent_total/ ($agent_balance_t-$collected_by_agent_total))*100), 2);
                                       ?>


                                       </tbody>
                                       <tfoot class="bg-black-gradient font-18">
                                       <tr><th>uid</th><th>Collector</th><th>Branch</th><th><?php echo money($agent_amount_t); ?></th><th><?php echo money($agent_repayable_t); ?></th><th><?php echo money($agent_collected_t); ?></th><th class="text-blue font-14"><?php echo money($portfolio_start_balance_total)?></th><th class="font-bold font-14 text-blue"><?php echo money($collected_by_agent_total); ?></th><th class="font-bold font-14 text-blue"><?php echo money($portfolio_agent_balance_total); ?></th><th class="text-blue font-bold font-14"><?php echo money($arate_av); ?>%</th><th><?php echo money($agent_balance_t); ?></th><th class="font-bold font-14"><?php echo ($rate_av); ?>%</th><th class='font-18'><?php echo ($agent_total_t); ?></th><th>Action</th></tr>
                                       </tfoot>

                                   </table>
                                </div>
                            </div>


                           <?php
                             }
                            ?>




                            <?php
                            if(isset($_GET['bdo'])){
                                $bdo = decurl($_GET['bdo']);
                                
                                $bdo_name = $agent_name_array[$bdo];
                                $group = $agent_tag_array[$bdo];
                                $agent_branch = $branch_names_array[$agent_branch_array[$bdo]];
                               

                                
                                $bdo_rate = round( ( $agent_loan_repaid_array[$bdo]/$agent_loan_repayable_array[$bdo])*100,2);
                            ?>
                            <div class="box-body">
                                <div class="font-14">
                                    <table class="table"><tr><td>BDO Performance: <b><?php echo $bdo_name; ?></b> <i>All Accounts</i> <a class="btn btn-success pull-right" href="?group"><i class="fa  fa-reply"></i> Back to All BDOs</a></tr> </table> 
                                    
                                    <table class="table table-bordered bg-black-gradient font-14" style="display: none;" >
                                    
                                        <tr><td>Group</td><td class="font-bold"><?php echo $group; ?></td><td>Branch</td><td class="font-bold"><?php echo "$agent_branch"; ?></td><td>Loan Amount</td><td class="font-bold"><?php echo false_zero(money($agent_loan_amount_array[$bdo])); ?></td></tr>
                                        <tr><td>Loan Total</td><td class="font-bold"><?php echo false_zero(money($agent_loan_repayable_array[$bdo])); ?></td><td>Repaid</td><td class="font-bold"><?php echo false_zero(money($agent_loan_repaid_array[$bdo]));  ?></td> <td class="font-22">Collected by Agent</td><td class="font-bold font-blue font-22"><?php echo false_zero(money($agent_collected_loan_repaid_array[$bdo]));  ?></td><td>Balance</td><td class="font-bold"><?php echo false_zero(money($agent_loan_balance_array[$bdo])); ?></td><td>Total Loans</td><td class="font-bold"><?php echo false_zero(money($agent_loans[$bdo])); ?></td><td>Rate</td><td class="font-24 font-bold"><?php echo $bdo_rate; ?>%</td></tr>
                                    </table>
                                    <table class="table table-bordered" id="tbl2">
                                        <thead>
                                        <tr><th>uid</th><th>Client</th><th>Branch</th><th>Loan Amount</th><th>Loan Total</th><th>Total Paid</th><th>Collected By Agent</th><th>Balance</th><th>Given Date</th><th>Due Date</th><th>Chat</th><th>Goto</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                           
                                                 foreach ($loan_agents as $lid => $agent) {
                                                    if($bdo == $agent){

                                                        $customer_id_ = $loan_customer_array[$lid];
                                                        $customer_name = $customer_names_array[$customer_id_];
                                                        $loan_amount = $loan_amounts_array[$lid];
                                                        $total_repayable_amount = $loan_total_array[$lid];
                                                        $total_repaid = $loan_paid_array[$lid];
                                                        $total_collected_agent = 0;
                                                        $loan_balance = $loan_balance_array[$lid];
                                                        $given_date = $loan_given_dates[$lid];
                                                        $due_date = $loan_due_dates[$lid];

                                                        $customer_branch = $branch_names_array[$loan_branches[$lid]];

                                                        $int = "<a onclick=\"interactions_popup('".encurl($customer_id_)."')\"><i class=\"fa fa-comments-o\"></i></a>";
                                                        $goto = "<a target=\"_BLANK\" href=\"customers?customer=".encurl($customer_id_)."#tab_3\"><i class=\"fa  fa-external-link\"></i></a>";

                                                echo "<tr><td>$lid</td><td>$customer_name</td><td>$customer_branch</td><td>".money($loan_amount)."</td><td>".money($total_repayable_amount)."</td><td>".money($total_repaid)."</td><td>".money($total_collected_agent)."</td><td>".money($loan_balance)."</td><td>".$given_date."</td><td>".$due_date."</td><td>$int</td><td>$goto</td></tr>";
                                                    }
                                                 }
                                            ?>
                                       
                                      
                                        </tbody>
                                        <tfoot>
                                        <tr><th>uid</th><th>Client</th><th>Branch</th><th>Loan Amount</th><th>Loan Total</th><th>Total Paid</th><th>Balance</th><th>Given Date</th><th>Due Date</th><th>Chat</th><th>Goto</th></tr>
                                        </tfoot>

                                    </table>
                                </div>
                            </div>
                            <!-- /.box-body -->
                            <?php
                            }
                            ?>
                        </div>
                        <!-- /.box -->
                    </div>
                    <!-- /.col -->
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
                <h4 class="modal-title">Upload New Allocations for <b><?php echo $alloc; ?></b></h4>
            </div>
            <div class="modal-body">
                <div class="alert  bg-danger">Please note: When you upload new allocations, the previous allocations will be reset. Make sure you have downloaded a copy of the performance as of now before you upload</div>

                <form class="form-horizontal" id="alloc-upload" method="POST" action="action/system/allocations" enctype="multipart/form-data">
                    <div class="box-body">



                        <div class="form-group">
                            <label for="loans_" class="col-sm-3 control-label">New Allocations (CSV)</label>

                            <div class="col-sm-9">
                                <input type="hidden" name="type_" value="<?php echo $alloc ?>">
                                <input type="file" id="loans_" name="file_" class="form-control">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="agree" class="col-sm-3 control-label">Lock this allocation?</label>
                            <span class="alert alert-info">If locked, this accounts won't move automatically to other vintages unless you upload them again with this option unchecked</span>
                            <div class="col-sm-9">
                            <input type="checkbox" id="agree" name="agree" value="yes">
                            </div>

                        </div>
                        <input type="submit" value="Submit">

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
                                <br/>

                            </div>
                        </div>
                        <div class="col-sm-6">
                            <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                        </div>
                        <div class="col-sm-6">
                            <input type="submit" onclick="formready('alloc-upload');" class="btn btn-success" value="Submit"/>
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
    $(document).ready( function () {
        document.title = "<?php echo $company['name'].'-'.$title; ?>";
        $('#tbl1').DataTable({
            dom: 'Bfrtip',
            "pageLength": 25,
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        } );
        $('#tbl2').DataTable({
            dom: 'Bfrtip',
            "pageLength": 25,
            buttons: [
                'copy', 'csv', 'excel', 'pdf', 'print'
            ]
        } );
    } );
</script>

</body>
</html>
