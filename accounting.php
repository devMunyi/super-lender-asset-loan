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
    <title><?php echo $company['name']; ?> | Accounting</title>
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

    $start_date = getFirstDayOfMonth($date);
    $end_date = "$date";


    ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->


        <!-- Main content -->


            <section class="content-header">
                <h1>
                    Accounting
                    <small>Standard</small>
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
                <div class="box bg-black-gradient box-solid">
                    <?php

                    $view_accounting = permission($userd['uid'], 'o_accounting', "0", "read_");
                    if ($view_accounting != 1) {
                        die(errormes("You don't have permission to view this page."));
                    }
                    ?>
                <div class="box-header box-title">
                            <?php

                            if(isset($_GET['general-ledger'])){
                               echo "<span class=\"font-18 font-bold\">General Ledger (GL)</span> &nbsp;";
                               $page = 'general_ledger.php';
                            }
                            elseif (isset($_GET['income-statement'])) {
                                echo "<span class=\"font-18 font-bold\">Income Statement</span> &nbsp;";
                                $page = 'income_statement.php';

                            }
                            elseif (isset($_GET['accounts-receivable'])) {
                                echo "<span class=\"font-18 font-bold\">Accounts Receivable</span> &nbsp;";
                                $page = 'accounts_receivable.php';

                            }
                            elseif (isset($_GET['balance-sheet'])) {
                                echo "<span class=\"font-18 font-bold\">Balance Sheet</span> &nbsp;";
                                $page = 'balance_sheet.php';

                            }
                            elseif (isset($_GET['cash-flow'])) {
                                echo "<span class=\"font-18 font-bold\">Cash Flow</span> &nbsp;";
                                $page = 'cash_flow.php';

                            }
                            elseif (isset($_GET['defaulter-ageing'])) {
                                echo "<span class=\"font-18 font-bold\">Cash Flow</span> &nbsp;";
                                $page = 'defaulter_ageing.php';

                            }
                            elseif (isset($_GET['trial-balance'])) {
                                echo "<span class=\"font-18 font-bold\">Trial Balance</span> &nbsp;";
                                $page = 'trial_balance.php';

                            }
                            elseif (isset($_GET['audit-report'])) {
                                echo "<span class=\"font-18 font-bold\">Audit Report</span> &nbsp;";
                                $page = 'audit_report.php';

                            }
                            ?>

                        &nbsp;&nbsp; &nbsp;&nbsp;
                        <button onclick="input_add('#dobj', 'BRANCH'); accounting_load('<?php echo $page; ?>');" class="btn btn-default bg-black-gradient">Per Branch</button>
                        <button onclick="input_add('#dobj', 'PRODUCT'); accounting_load('<?php echo $page; ?>');" class="btn btn-default bg-black-gradient">Per Product</button>
                        <button onclick="input_add('#dobj', 'MONTH'); accounting_load('<?php echo $page; ?>');" class="btn btn-default bg-black-gradient">Per Month</button>
                        <input type="hidden" id="dobj" value="BRANCH">


                    <div class="pull-right">
                        <input type="date" value="<?php echo $start_date; ?>" id="start_date_acc" class="btn btn-default" style="color: white;">
                        <input type="date" value="<?php echo $end_date; ?>" id="end_date_acc" style="color: white;" class="btn btn-default">
                        <button onclick="accounting_load('<?php echo $page; ?>');" class="btn btn-success bg-green-gradient">GO</button>
                    </div>


                </div>
                    <div class="box-body scroll-hor text-black" style="background: white;">
                        <div class="acc" id="account_load">
                         <i class="fa fa-refresh"></i>   Loading ....
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
<!-- ./wrapper -->

<?php
include_once("footer_includes.php");
?>
<script>
    $(document).ready(function () {
       accounting_load('<?php echo $page; ?>');
    });

</script>
</body>
</html>
