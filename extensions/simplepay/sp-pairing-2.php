<?php
session_start();
// error_reporting(E_ALL);
// ini_set('display_errors', 'On');
include_once ("../php_functions/functions.php");
include_once ("../configs/conn.inc");

$company = company_settings();

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
$b = $_GET['b'];
if($b > 0) {
    $br = $_GET['b'];
    $b = decurl($_GET['b']);
    $andbranch = " AND uid = '$b'";
    $andbranch2 = " AND branch = '$b'";
}
else{
    $b = 0;
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

$k = 0;
$branch_colors = array();
foreach ($branch_names as $key => $value)
{
    $branch_colors[$key] = $colors[$k];
    $k = $k + 1;
    if($k > 9){
        $k = 0;
    }
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
                            echo "<li class=\"list-group-item\"><a onclick=\"load_std('/extensions/sp-pairing-2.php','#dynamic_load','b=$ebid')\" href=\"#\">$bname</a></li>";

                        }
                        $users_array = table_to_obj('o_users',"uid > 0","100000","uid","name");
                        $loan_statuses = table_to_obj('o_loan_statuses',"uid > 0","100","uid","name");
                        if(isset($_GET['b'])){

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



                                <?php
                            }
                            else{
                                echo "<div class=\"text-bold font-18\"><i class=\"fa fa-hand-o-left\"></i> Select Branch</div>";
                            }
                            ?>
                        </div>
                        <div class="box-body">
                            <h4 class="text-bold">All Unpaired</h4>
                            <table class="table table-bordered">
                                <thead>
                                <tr><th>UID</th> <th>Name</th> <th>Group</th> <th>Branch</th><th>Action</th></tr>
                                </thead>
                                <tbody>
                                <?php
                                $all_pair_users = array();
                                $pairs = fetchtable('o_pairing',"status=1  $andbranch2","branch","asc","1000000","uid , lo, co, branch");
                                while($p = mysqli_fetch_array($pairs)){
                                    $action = "";
                                    $puid = $p['uid'];
                                    $lo = $p['lo'];           $lo_name = $user_names_array[$lo];      $lo_branch = $user_branches_array[$lo];
                                    $co = $p['co'];           $co_name = $user_names_array[$co];      $co_branch = $user_branches_array[$co];
                                    $pair_branch = $p['branch'];
                                    $pair_branch_name = $branch_names[$pair_branch];

                                    array_push($all_pair_users, $lo);
                                    array_push($all_pair_users, $co);


                                }

                                $usersx = fetchtable('o_users',"status = 1 AND user_group in (7,8) $andbranch2","branch","asc","10000","uid, name, user_group, branch");
                               $total_found = mysqli_num_rows($usersx);
                               if($total_found > 0) {
                                   while ($u = mysqli_fetch_array($usersx)) {
                                       $user_uid = $u['uid'];
                                       $uname = $u['name'];
                                       $ugroup = $u['user_group'];
                                       $ubranch = $u['branch'];

                                       $group_name = $group_names[$ugroup];
                                       $branch_name = $branch_names[$ubranch];

                                       $branch_color = $branch_colors[$ubranch] . '54';

                                       if (in_array($user_uid, $all_pair_users) == false) {
                                           $act = "<button class='btn-sm btn-success' onclick=\"modal_view('/jresources/customers/new_pair.php','user=$user_uid','Find a new pair')\"  title='Find a new Pair'><i class='fa fa-chain'></i> Pair</button>";
                                           echo "<tr style='background:$branch_color ;'><th>$user_uid</th> <th>$uname</th> <th>$group_name</th> <th>$branch_name</th><th>$act</th></tr>";
                                       }


                                   }
                               }
                               else{
                                   echo "<tr><td colspan='5'> No records found</td></tr>";
                               }
                                ?>
                                </tbody>
                            </table>







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

