<?php
session_start();
include_once ("php_functions/functions.php");
include_once ("configs/conn.inc");
include_once("php_functions/authenticator.php");

$company = company_settings();

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $company['name']; ?> | Monthly Performance</title>
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


    $userbranch = $userd['branch'];

    $view_summary = permission($userd['uid'],'o_summaries',"0","read_");
    if($view_summary == 1) {
        $andbranch_payments = "";
        $andbranch_customers = "";
        $andbranch_loans = "";
        $andbranch = "";
    }
    else{
        $andbranch_payments = "AND branch_id='$userbranch'";
        $andbranch_customers = "AND branch='$userbranch'";
        $andbranch_loans = "AND current_branch='$userbranch'";
        $andbranch = "AND uid='$userbranch'";
    }

    ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->


        <!-- Main content -->


            <section class="content-header">
                <h1>
                    Monthly Performance
                    <small>List</small>
                </h1>
                <ol class="breadcrumb">
                    <li><a href="monthly-performance">Monthly Performance </a></li>
                    <li><a href="bdo-performance"> BDO Performance </a></li>
                    <li><a href="accounting?general-ledger"> General Ledger</a></li>
                    <li><a href="accounting?income-statement"> Income Statement</a></li>
                    <li><a href="accounting?accounts-receivable"> Accounts Receivables</a></li>
                    <li><a href="accounting?balance-sheet"> Balance Sheet</a></li>
                    <li><a href="accounting?cash-flow"> Cash Flow</a></li>
                    <li><a href="accounting?defaulter-ageing"> Defaulter Ageing</a></li>
                    <li><a href="accounting?trial-balance"> Trial Balance</a></li>

                </ol>
            </section>
            <section class="content">
                <div class="row">
                    <div class="col-xs-12">

                        <!-- /.box -->

                        <div class="box">
                            <div class="box-header bg-info">
                                <div class="row">
                                    <div class="col-md-10">
                                      <div class="row">
                                          <div class="col-sm-2">
                                              <select class="form-control" id="year_">
                                                  <?php
                                                  $begin = new DateTime(datesub($thisyear, 6,0,0));
                                                  $end = new DateTime($thisyear);
                                                  $year_n = $thisyear * 1;

                                                  $interval = DateInterval::createFromDateString('1 year');
                                                  $period = new DatePeriod($begin, $interval, $end);

                                                  foreach ($period as $dt) {
                                                      $y = $dt->format("Y");
                                                      if($y == $year_n){
                                                          $selected = "SELECTED";
                                                      }
                                                      else{
                                                          $selected = "";
                                                      }
                                                      echo "<option $selected>$y</option>";
                                                  }
                                                  ?>

                                              </select>
                                          </div>
                                          <div class="col-sm-2">
                                              <select class="form-control" id="month_">
                                                  <?php
                                                  for($i=1; $i<=12; ++$i){
                                                      if($i == $thismonth){
                                                          $selected = "SELECTED";
                                                      }
                                                      else{
                                                          $selected = "";
                                                      }
                                                      echo "<option $selected value='$i'>".month_name($i)."</option>";
                                                  }
                                                  ?>

                                              </select>
                                          </div>
                                          <div class="col-sm-2">
                                              <select class="form-control" id="branches_">
                                                  <option value="0"> --All Branches</option>
                                              <?php
                                              $branches = fetchtable('o_branches',"status=1 $andbranch","name","asc","100","uid, name");
                                              while($b = mysqli_fetch_array($branches)){
                                                  $bid = $b['uid'];
                                                  $bname = $b['name'];
                                                  echo "<option value='$bid'>$bname</option>";
                                              }
                                              ?>


                                              </select>
                                          </div>
                                          <div class="col-sm-2">
                                              <select class="form-control" id="products_">
                                                  <option>--All Products</option>
                                                  <?php
                                                  $products = fetchtable('o_loan_products',"status=1","name","asc","100","uid, name");
                                                  while($p = mysqli_fetch_array($products)){
                                                      $pid = $p['uid'];
                                                      $pname = $p['name'];
                                                      echo "<option value='$pid'>$pname</option>";
                                                  }
                                                  ?>

                                              </select>
                                          </div>
                                          <div class="col-sm-2">
                                              <button onclick="monthly_performance();" class="btn btn-primary bg-blue-gradient"><i class="fa fa-refresh"></i> Generate</button>
                                          </div>

                                      </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.box-header -->
                            <div class="box-body">
                               <div id="monthly_">
                                   Loading...
                               </div>
                            </div>
                            <!-- /.box-body -->
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
<!-- ./wrapper -->

<?php
include_once("footer_includes.php");
?>
<script>
    $(document).ready(function () {
        monthly_performance();
    });

</script>
</body>
</html>
