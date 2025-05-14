<?php
session_start();
// error_reporting(E_ALL);
// ini_set('display_errors', 'On');
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");


$userd = session_details();
$user_id = $userd['uid'];
$group_tag = $userd['tag'];
$user_group = $userd['user_group'];
$user_branch = $userd['branch'];

$view_branches = permission($userd['uid'],'o_branches',"0","read_");
if($view_branches == 1){
    $andbranch = "";
    $andbranch2 = "";
}
else{
    $andbranch = " AND uid = '$user_branch'";
    $andbranch2 = " AND branch = '$user_branch'";
}


$colors = array("#E6E4E4","#EBADAD","#FFBBBB","#FFFFC3","#CBFF98","#B2EED0","#D4EFE9","#EBADD6","#FFFFFF","#D8C4FF");

if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
    exit();
}

$user_names_array = array();
$user_groups_array = array();
$user_branches_array = array();
$user_status_array = array();

$users = fetchtable('o_users',"uid > 0","uid","asc","10000","uid, name, user_group, branch, status");
while($u = mysqli_fetch_array($users)){
    $user_names_array[$u['uid']] = $u['name'];
    $user_groups_array[$u['uid']] = $u['user_group'];
    $user_branches_array[$u['uid']] = $u['branch'];
    $user_status_array[$u['uid']] = $u['status'];
}


$branch_names = table_to_obj('o_branches',"uid > 0","100000","uid","name");
$group_names = table_to_obj('o_user_groups',"uid > 0","100000","uid","name");

$b = $_GET['b'];
if($b > 0) {
    $br = $_GET['b'];
    $b = decurl($_GET['b']);
    $title = $branches_array[$b];
}
else{
    $b = 0;
}


?>



    <?php
    include_once('header.php');
    ?>
    <!-- Left side column. contains the logo and sidebar -->
    <?php
    include_once('menu.php');
    ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="x">
        <!-- Content Header (Page header) -->


        <!-- Main content -->




        <section class="x">
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





                        $branches_array = array();
                        $bran = fetchtable('o_branches',"status=1 $andbranch","name","asc","1000","uid, name");
                        while($bra = mysqli_fetch_array($bran)){
                            $bid = $bra['uid'];   $ebid = encurl($bid);
                            $bname = $bra['name'];
                            $branches_array[$bid] = $bname;
                            echo "<li class=\"list-group-item\"><a onclick=\"load_std('/extensions/sp-pairing-3.php','#dynamic_load','b=$ebid')\" href=\"#\">$bname</a></li>";

                        }
                        $users_array = table_to_obj('o_users',"uid > 0","100000","uid","name");
                        $loan_statuses = table_to_obj('o_loan_statuses',"uid > 0","100","uid","name");
                        if(isset($_GET['b'])){
                           //echo "<a class=\"btn bg-blue-gradient btn-block\" href=\"#pairs_\">Pairs <i class=\"fa fa-hand-o-down\"></i></a>";
                        }


                        ?>
                    </ol>
                </div>

                <div class="col-md-10">
                    <div class="box">
                           <div class="box-body">
                               <h4 class="text-bold">Wrongly Paired Customers/Loans for <b><?php echo $branches_array[$b]; ?></b> <button onclick="auto_pair('<?php echo $b;?>')" class="btn btn-danger pull-right"><i class="fa fa-flash"></i> Autofix</button></h4>
                            <table class="table table-striped table-bordered">


                                <?php
                                if($b > 0) {

                                    $all_pairs = table_to_obj('o_pairing',"branch = $b AND status=1","1000","lo","co");
                                    $all_los = table_to_array('o_pairing',"branch = $b AND status=1","1000","lo");
                                    $all_cos = table_to_array('o_pairing',"branch = $b AND status=1","1000","co");
                                    $all_wrong_loans = array();
                                    $all_wrong_los = array();
                                    $all_wrong_cos = array();

                                    $loans_per_lo = array();
                                    $loans_per_co = array();
                                    $wrong_loans = fetchtable('o_loans',"current_branch = $b AND disbursed = 1 AND status!=0","uid","asc","10000000","uid, current_lo, current_co");
                                    $total_wrong = mysqli_num_rows($wrong_loans);
                                    if($total_wrong > 0) {
                                        while ($wl = mysqli_fetch_array($wrong_loans)) {
                                            $loan = $wl['uid'];
                                            $current_lo = $wl['current_lo'];
                                            $current_co = $wl['current_co'];

                                            if ($all_pairs[$current_lo] == $current_co) {
                                                ///----Good pair, continue
                                            } else {
                                                // array_push($all_wrong_loans, $loan);
//                                            array_push($all_wrong_los, $current_lo);
//                                            array_push($all_wrong_cos, $current_co);
//                                            $loans_per_lo = obj_add($loans_per_lo, $current_lo, 1);
//                                            $loans_per_co = obj_add($loans_per_co, $current_co, 1);

                                                array_push($all_wrong_loans, $loan);

                                                if ((in_array($current_lo, $all_los)) == false) {
                                                    array_push($all_wrong_los, $current_lo);
                                                    $loans_per_lo = obj_add($loans_per_lo, $current_lo, 1);
                                                }

                                                if ((in_array($current_co, $all_cos)) == false) {
                                                    array_push($all_wrong_cos, $current_co);
                                                    $loans_per_co = obj_add($loans_per_co, $current_co, 1);
                                                }


                                            }

                                        }
                                        $all_wrong_los_ = array_values(array_unique($all_wrong_los));
                                        $all_wrong_cos_ = array_values(array_unique($all_wrong_cos));


                                        for ($i = 0; $i < sizeof($all_wrong_los_); ++$i) {
                                            $loan_lo = $all_wrong_los_[$i];
                                            $lo_name = $user_names_array[$loan_lo];

                                            if ($loan_lo == 0) {
                                                $lo_name = "(No LO)";
                                            }

                                            if ((in_array($loan_lo, $all_los)) == false) {
                                                $tot = $loans_per_lo[$loan_lo];
                                                $fix = "<button onclick=\"fix_pairing_d($b, $loan_lo, 'LO')\" class='btn btn-primary'><i class='fa fa-gears'></i>Fix</button>";
                                                echo "<tr><td><b>$tot</b> Loans have wrong LO  $lo_name [$loan_lo]</td><td>$fix</td></tr>";
                                            }

                                        }

                                        for ($i = 0; $i < sizeof($all_wrong_cos_); ++$i) {
                                            $loan_co = $all_wrong_cos_[$i];
                                            $co_name = $user_names_array[$loan_co];

                                            if ($loan_co == 0) {
                                                $co_name = "(No CO)";
                                            }
                                            if ((in_array($loan_co, $all_cos)) == false) {
                                                $tot = $loans_per_co[$loan_co];
                                                $fix = "<button onclick=\"fix_pairing_d($b, $loan_co, 'CO')\" class='btn btn-primary'><i class='fa fa-gears'></i>Fix</button>";
                                                echo "<tr><td><b>$tot</b>Loans have wrong COs $co_name [$loan_co]</td><td>$fix</td></tr>";
                                            }

                                        }
                                    }
                                    else{
                                        echo "<tr><td colspan='2'> All accounts are correctly tagged</td></tr>";
                                    }



                                }
                                else{
                                    echo "<tr><td>Select a Branch</td></tr>";

                                }

                                ?>
                            </table>


                            </div>
                </div>
                </div>

                        </div>

        </section>

        <!-- /.content -->
    </div>

    <!-- /.content-wrapper -->


    <!-- Control Sidebar -->

    <!-- /.control-sidebar -->
    <!-- Add the sidebar's background. This div must be placed
         immediately after the control sidebar -->

<!-- ./wrapper -->

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

