<?php

$result = fetchtable2("o_loans", "status > 0 AND loan_type = 4", "uid", "DESC", "uid, loan_amount, total_repayable_amount, total_repaid, final_due_date, disbursed, paid");
$active_loans_count = 0;
$active_loans_sum = 0;

$overdue_loans_count = 0;
$overdue_loans_sum = 0;

$duetoday_loans_count = 0;
$duetoday_loans_sum = 0;

$duetomorrow_loans_count = 0;
$duetomorrow_loans_sum = 0;

$repaid_amount = 0;
$repayable_amount = 0;

while($l = mysqli_fetch_array($result)) {

    // setting variables to use
    $disbursed = $l["disbursed"];
    $paid = $l["paid"];
    $principal = $l["loan_amount"];
    $final_due_date = $l["final_due_date"];
    $today = $date; // retrieved from conn.inc
    $repaid = $l["total_repaid"];
    $repayable = $l["total_repayable_amount"];

    // handle active loans
    if($disbursed == 1 && $paid == 0){
        $active_loans_count += 1;
        $active_loans_sum += $principal;
    }

    // handle overdue loans
    if($final_due_date < $today) {
        $overdue_loans_count += 1;
        $overdue_loans_sum += $repayable;
    }

    // handle due today loans
    $daysDifference = datediff3($today, $final_due_date);
    if($daysDifference == 0) {
        $duetoday_loans_count += 1;
        $duetoday_loans_sum += $repayable;
    }

    // handle loans due tomorrow
    $today = new DateTime($date);
    $today->modify('+1 day');
    $today = $today->format('Y-m-d');
    $daysDifference = datediff3($today, $final_due_date);

   if($daysDifference == 1){
    $duetomorrow_loans_count += 1;
    $duetomorrow_loans_sum += $repayable;
   }

   // handle collection rate variables
   $repaid_amount += $repaid;
   $repayable_amount += $repayable;

}


// convert to money format
$active_loans_sum = money($active_loans_sum);
$overdue_loans_sum = money($overdue_loans_sum);
$duetoday_loans_sum = money($duetoday_loans_sum);
$duetomorrow_loans_sum = money($duetoday_loans_sum);
$collection_rate_ = (( $repaid_amount / $repayable_amount ) * 100 );
$collection_rate = floor($collection_rate_ * 100) / 100;
 
?>

<section class="content-header">
    <h1>
      Asset Finance

    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Summary</li>
    </ol>
</section>
<section class="content">
    <div class="box">
        <div class="box-body">

            <!-- /.box -->



                <!-- /.box-header -->
                <div class="row">



                    <div class="col-md-2">
                        <div class="box box-default box-solid box-success">
                            <div class="box-header with-border">
                                <span class="box-title">Active Loans</span>
                            </div>
                            <div class="box-body">
                                <div class="box-title font-bold font-18"><?php echo $active_loans_sum; ?> <small class="text-muted font-light">(<?php echo $active_loans_count; ?>)</small></div>
                                Number of active asset Loans <a href="loans?loan-type=4"><i class="fa fa-external-link"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="box box-default box-solid box-danger">
                            <div class="box-header with-border">
                                <span class="box-title">Overdue Loans</span>
                            </div>
                            <div class="box-body">
                                <div class="box-title font-bold font-18"><?php echo $overdue_loans_sum; ?> <small class="text-muted font-light">(<?php echo $overdue_loans_count; ?>)</small></div>
                                Number of overdue asset Loans <a href="loans?loan-type=3"><i class="fa fa-external-link"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="box box-default box-solid box-warning">
                            <div class="box-header with-border">
                                <span class="box-title">Due Today</span>
                            </div>
                            <div class="box-body">
                                <div class="box-title font-bold font-18"><?php echo $duetoday_loans_sum; ?> <small class="text-muted font-light">(<?php echo $duetoday_loans_count; ?>)</small></div>
                                Number of overdue asset Loans <a href="loans?loan-type=3"><i class="fa fa-external-link"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="box box-default box-solid box-warning">
                            <div class="box-header with-border">
                                <span class="box-title">Due Tomorrow</span>
                            </div>
                            <div class="box-body">
                                <div class="box-title font-bold font-18"><?php echo $duetomorrow_loans_sum; ?> <small class="text-muted font-light">(<?php $duetomorrow_loans_count; ?>)</small></div>
                                Number of overdue asset Loans due tomorrow <a href="loans?loan-type=3"><i class="fa fa-external-link"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="box box-default box-solid box-primary">
                            <div class="box-header with-border">
                                <span class="box-title">Collection Rate</span>
                            </div>
                            <div class="box-body">
                                <div class="box-title font-bold font-18"><?php echo $collection_rate ."%"; ?></div>
                                Collection Rate of Current loans <a href="loans?loan-type=3"><i class="fa fa-external-link"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="box box-default box-solid box-default">
                            <div class="box-header with-border">
                                <span class="box-title">PAR</span>
                            </div>
                            <div class="box-body">
                                <div class="box-title font-bold font-18">87% <small class="text-muted font-light">(400)</small> </div>
                               Portfolio at Risk in Asset Loans <a href="loans?loan-type=4"><i class="fa fa-external-link"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="box box-default box-solid">
                            <div class="box-body">
                              <a href="assets.php?cat=assets" class="btn btn-primary btn-block"> Go to Assets <i class="fa fa-external-link"></i></a>
                              <a href="loans?loan-type=4" class="btn btn-success btn-block"> Go to Loans <i class="fa fa-external-link"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="box box-default bg-purple-gradient box-solid">
                            <div class="box-body bg-purple-gradient">
                                <a href="assets.php?cat=cart" class="btn btn-default"> Go to Cart <i class="fa fa-external-link"></i></a>
                                <a href="assets.php?cat=cart"> </a>
                            </div>
                        </div>
                    </div>



                </div>
                <!-- /.box-body -->

            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
</section>
