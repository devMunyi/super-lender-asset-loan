<?php
session_start();
include_once ("php_functions/functions.php");
include_once ("configs/conn.inc");
include_once("php_functions/authenticator.php");

$company = company_settings();

$userd = session_details();
$user_id = $userd['uid'];
$group_tag = $userd['tag'];
$user_group = $userd['user_group'];
$user_branch = $userd['branch'];

$view_branches = permission($userd['uid'],'o_branches',"0","read_");
if($view_branches == 1){
    $andbranch = "";
}
else{
    $andbranch = " AND uid = '$user_branch'";
}

if($group_loans == 1){
    $th = "<th>Group</th>";
}
else{
    $th = "";
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
                    Pairing
                    <small>List</small>
                    <a class="btn btn-success" href="pairing-v2.php">Go to V2</a>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
                    <li class="active">Pairing</li>
                </ol>
            </section>
           
            
            <section class="content">
                    <div class="row well">
                        <?php
                      $upload_pairs = permission($userd['uid'],'o_pairing',"0","read_");
                  if($upload_pairs != 1){
      die(errormes("You don't have permission to view this page"));
      exit();
             }

                        ?>
                        <div class="col-md-2">
                            <ol class="list-group">
                            <?php 
                                 if($show_mtd_reports == 'TRUE'){
                                    $start_date = "2021-01-01";
                                   }
                                   else{
                                    $start_date = $date;
                                   }
                                   $end_date = $date;
                                   if(isset($_GET['from'])){
                                       $start_date = $_GET['from'];
                                   }
                                   if(isset($_GET['to'])){
                                       $end_date = $_GET['to'];
                                   }

                                 



                            $branches_array = array();
                            $bran = fetchtable('o_branches',"status=1 $andbranch","name","asc","1000","uid, name");
                            while($b = mysqli_fetch_array($bran)){
                                $bid = $b['uid'];   $ebid = encurl($bid);
                                $bname = $b['name'];
                                $branches_array[$bid] = $bname;
                                echo "<li class=\"list-group-item\"><a href=\"?b=$ebid\">$bname</a></li>";

                            }
                            $users_array = table_to_obj('o_users',"uid > 0","100000","uid","name");
                            $loan_statuses = table_to_obj('o_loan_statuses',"uid > 0","100","uid","name");
                            if(isset($_GET['b'])){
                                echo "<a class=\"btn bg-blue-gradient btn-block\" href=\"#pairs_\">Pairs <i class=\"fa fa-hand-o-down\"></i></a>";
                            }
                            

                            ?>
                            </ol>
                        </div>
                        <div class="col-md-10">
                           <div class="box">
                            <div class="box-header">
                                <?php
                                if(isset($_GET['b'])){
                                    $br = $_GET['b'];
                                    $b = decurl($_GET['b']);
                                    $title = $branches_array[$b];
                                    ?>
                                    
                         
                         <div class="row well"> <div class="col-xs-2"> <h4 class="font-bold"><?php echo $branches_array[$b]; ?></h4></div>  <div class="col-xs-3">
                                     <table><tr><td>From:</td><td> <input type="date" value="<?php echo $start_date; ?>" id="from_date" class="form-control"> </td></tr> </table></div> <div class="col-xs-3">
                                     <table><tr><td>To:</td><td> <input type="date" id="to_date" value="<?php echo $end_date; ?>" class="form-control"> </td></tr></table></div><div class="col-xs-3"><button class="btn btn-primary" onclick="run_pairing('<?php echo $br; ?>','<?php echo $report_type; ?>');"> RUN <i class="fa fa-arrow-right"></i></button> <button class="btn btn-success" data-toggle="modal" data-target="#upload_pairs"> <i class="fa fa-arrow-up"></i> Upload</button> </div> </div>
                                    <?php
                                }
                                else{
                                    echo "<div class=\"text-bold font-18\"><i class=\"fa fa-hand-o-left\"></i> Select Branch</div>";
                                }
                                ?>
                            </div>
                            <div class="box-body">
                                <?php
                                if($user_group != 'kdkdd'){
                                ?>
                                <h4 class="text-bold">All Allocations</h4>
                                <table class="table table-condensed table-sm" id="tbl1">
                                    <thead>
                                        <tr><th>LoanId</th><th>Name</th><th>Phone</th><th>Branch</th><th>LoanAmount</th><th>GivenDate</th><th>DueDate</th><th>LO</th><th>LO_Code</th><th>CO</th><th>CO_Code</th><th>Status</th><?php echo $th; ?></tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        if($b > 0){
                                        $branch_customers = table_to_obj('o_customers',"branch='$b'","1000000","uid","full_name");

                                        if($group_loans == 1){
                                            $customer_groups = table_to_obj('o_group_members',"status='1'","1000000","customer_id","group_id");
                                            $group_names = table_to_obj('o_customer_groups',"uid > 0","10000","uid","group_name");
                                        }

                                         $loans = fetchtable('o_loans',"current_branch='$b' AND disbursed=1 AND status!=0 AND given_date BETWEEN '$start_date' AND '$end_date' AND allocation='BRANCH' ","uid","asc","100000000","uid, customer_id, account_number, loan_amount,  given_date, final_due_date,status, current_lo, current_co, current_branch");

                                         while($l = mysqli_fetch_array($loans)){
                                            $lid = $l['uid'];
                                            $cust_id = $l['customer_id'];
                                            $acc = $l['account_number'];
                                            $loan_amount = $l['loan_amount'];
                                            $customer_name = $branch_customers[$cust_id];

                                            if($group_loans == 1){
                                                $group_name = "<td>".$group_names[$customer_groups[$cust_id]]."</td>";
                                            }
                                            else{
                                                $group_name = "";
                                            }
                                          
                                            $given_date = $l['given_date'];
                                            $final_due_date = $l['final_due_date'];
                                            $current_lo = $l['current_lo'];           $lo_name = $users_array[$current_lo];  
                                            $current_co = $l['current_co'];           $co_name = $users_array[$current_co];
                                            $current_branch = $l['current_branch'];   $branch_name = $branches_array[$current_branch];
                                            $status = $l['status'];                   $status_name = $loan_statuses[$status];
                                           

                                            echo "<tr><td>$lid</td><td>$customer_name</td><td>$acc</td><td>$branch_name</td><td>$loan_amount</td><td>$given_date</td><td>$final_due_date</td><td>$lo_name</td><td>$current_lo</td><td>$co_name</td><td>$current_co</td><td>$status_name</td>$group_name</tr>";
                                         }
                                        }

                                        ?>
                                   

                                    </tbody>
                                    <tfoot>
                                    <tr><th>LoanId</th><th>Name</th><th>Phone</th><th>Branch</th><th>LoanAmount</th><th>GivenDate</th><th>DueDate</th><th>LO</th><th>LO_Code</th><th>CO</th><th>CO_Code</th><th>Status</th><?php echo $th; ?></tr>
                                    </tfoot>

                                </table>
                                <hr/>
                                <?php
                                }
                                ?>
                                <h4 class="text-bold">Wrongly Allocated Loans</h4>
                                <table class="table bg-light-blue table-condensed table-sm" id="tbl2">
                                    <thead>
                                    <tr><th>LoanId</th><th>Name</th><th>Phone</th><th>Branch</th><th>LoanAmount</th><th>GivenDate</th><th>DueDate</th><th>LO</th><th>LO_Code</th><th>CO</th><th>CO_Code</th><th>Status</th><?php echo $th ?></tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if($b > 0){
                                        if($group_loans == 1){
                                            $customer_groups = table_to_obj('o_group_members',"status='1'","1000000","customer_id","group_id");
                                            $group_names = table_to_obj('o_customer_groups',"uid > 0","10000","uid","group_name");
                                        }

                                        $branch_agents_array = table_to_array('o_users',"branch='$b' AND status=1","100000","uid");
                                        $lo_list_array = table_to_array('o_users',"tag='LO' AND status=1 AND branch='$b'","100000","uid");
                                        $co_list_array = table_to_array('o_users',"tag='CO' AND status=1 AND branch='$b'","100000","uid");

                                        $pair = table_to_obj('o_pairing',"branch='$b' AND status=1","1000","lo",'co');

                                        $branch_customers = table_to_obj('o_customers',"branch='$b'","1000000","uid","full_name");

                                        $loans = fetchtable('o_loans',"current_branch='$b' AND disbursed=1 AND status!=0 AND given_date BETWEEN '$start_date' AND '$end_date'","uid","asc","100000000","uid, customer_id, account_number, loan_amount,  given_date, final_due_date,status, current_lo, current_co, current_branch");
                                        while($l = mysqli_fetch_array($loans)){
                                            $lid = $l['uid'];
                                            $cust_id = $l['customer_id'];
                                            $acc = $l['account_number'];
                                            $loan_amount = $l['loan_amount'];
                                            $customer_name = $branch_customers[$cust_id];

                                            if($group_loans == 1){
                                                $group_name = "<td>".$group_names[$customer_groups[$cust_id]]."</td>";
                                            }
                                            else{
                                                $group_name = "";
                                            }

                                            $given_date = $l['given_date'];
                                            $final_due_date = $l['final_due_date'];
                                            $current_lo = $l['current_lo'];           $lo_name = $users_array[$current_lo];
                                            $current_co = $l['current_co'];           $co_name = $users_array[$current_co];
                                            $current_branch = $l['current_branch'];   $branch_name = $branches_array[$current_branch];
                                            $status = $l['status'];                   $status_name = $loan_statuses[$status];

                                          /*  if((in_array($current_lo, $lo_list_array) == 1)  && (in_array($current_lo, $branch_agents_array) == 1)){
                                               continue;

                                            } */
                                            if($pair[$current_lo] == $current_co && $current_lo > 0 && $current_co > 0){
                                                ////-----Paired correctly
                                               // echo "$current_lo-$current_co,";
                                                                                           }
                                            else {

                                                echo " <tr><td>$lid</td><td>$customer_name</td><td>$acc</td><td>$branch_name</td><td>$loan_amount</td><td>$given_date</td><td>$final_due_date</td><td>$lo_name</td><td>$current_lo</td><td>$co_name</td><td>$current_co</td><td>$status_name</td>$group_name</tr>";
                                            }
                                        }
                                    }

                                    ?>


                                    </tbody>
                                    <tfoot>
                                    <tr><th>LoanId</th><th>Name</th><th>Phone</th><th>Branch</th><th>LoanAmount</th><th>GivenDate</th><th>DueDate</th><th>LO</th><th>LO_Code</th><th>CO</th><th>CO_Code</th><th>Status</th><?php echo $th ?></tr>
                                    </tfoot>

                                </table>
                                <div class="well bg-gray">
                                    <?php
                                    if($b > 0){
                                        include_once ("widgets/settings/pairing-branch.php");
                                    }

                                    ?>
                                </div>
                            </div>
                           </div>
                        </div>
                        
                    
                     
                    </div>
                    <!-- /.col -->
                </div>
            </section>

        <!-- /.content -->
    </div>

    <!-- /.content-wrapper -->
   

    <!-- Control Sidebar -->

    <!-- /.control-sidebar -->
    <!-- Add the sidebar's background. This div must be placed
         immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
<div class="modal fade" id="upload_pairs">
    <div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title">Upload New Pairs for <b><?php echo $branches_array[$b]; ?></b></h4>
    </div>
    <div class="modal-body">
    <div class="alert  bg-purple">Download the report as is and modify the existing LO-CO. The LO-CO Codes are the most important. Please pair them correctly for it to work</div>

    <form class="form-horizontal" id="pair-upload" method="POST" action="action/loan/new_pairs" enctype="multipart/form-data">
                            <div class="box-body">
                               
                                

                                <div class="form-group">
                                    <input type="hidden" value="<?php echo $b; ?>" name="branch">
                                    <label for="target_customers" class="col-sm-3 control-label">New Loan Pairs (CSV)</label>

                                    <div class="col-sm-9">
                                        <input type="file" id="target_customers" name="target_customers" class="form-control">
                                    </div>
                                </div>
                               

                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <div class="box-footer">
                                        <div class="prgress">
                                            <div class="messagepair-upload" id="message"></div>
                                            <div class="progresspair-upload" id="progress">
                                                <div class="barpair-upload" id="bar"></div>
                                                <br>
                                                <div class="percentpair-upload" id="percent"></div>
                                            </div>
                                        </div>
                                        <br/>
                                      
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                                </div>
                                <div class="col-sm-6">
    <input type="submit" onclick="formready('pair-upload');" class="btn btn-success" value="Submit"/>
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
<?php
    include_once("footer.php");
    ?>


<?php
include_once("footer_includes.php");
?>
<script>
    $(document).ready( function () {
        document.title = "<?php echo $company['name'].'-'.$title; ?>";
        $('#tbl1').DataTable({
            dom: 'Bfrtip',
            buttons: [
                'csv'
            ]
        } );
        $('#tbl2').DataTable({
            dom: 'Bfrtipji',
            buttons: [
                'csv'
            ]
        } );
      
    } );
</script>

</body>
</html>
