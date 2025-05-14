<?php

session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$this_year = date('Y');
$this_month = date('m');
$this_day = date('d');
// $currencyUsed = currencyUsed();
// $currencyUsed = "<small class='font-14'>$currencyUsed</small>";
$currencyUsed = "";

$userd = session_details();
$userbranch = $userd['branch'];
$view_summary = permission($userd['uid'], 'o_summaries', "0", "read_");
$inarchive_ = $_SESSION['archives'] ?? 0;
if ($view_summary == 1 || $inarchive_ == 1) {
    $andbranch_loans = "";
    $andbranch_payments = "";
} else {

    $andbranch_loans = "AND current_branch = $userbranch";
    $andbranch_payments = "AND branch_id = $userbranch";

    //////-----Check users who view multiple branches
    $staff_branches = table_to_array('o_staff_branches', "agent=" . $userd['uid'] . " AND status=1", "1000", "branch", "uid", "asc");
    if (sizeof($staff_branches) > 0) {
        ///------Staff has been set to view multiple branches
        array_push($staff_branches, $userd['branch']);
        $staff_branches_list = implode(",", $staff_branches);

        $andbranch_loans = "AND current_branch IN ($staff_branches_list)";
        $andbranch_payments = "AND branch_id IN ($staff_branches_list)";
    }
}

$loans_today = totaltable('o_loans', "given_date='$date' AND status !=0 AND disbursed=1 $andbranch_loans", "loan_amount");
$payments_today = totaltable('o_incoming_payments', "payment_date='$date' AND status=1 $andbranch_payments", "amount");
$due_today = totaltable('o_loans', "final_due_date='$date' AND disbursed=1 AND paid=0 AND status IN (3,4) $andbranch_loans", "loan_balance");
if ($cc == 256) {
    $utility_balance = fetchrow('o_summaries', "name='MTN_UTILITY_BALANCE' $andbranch_loans", "value_");
    $inline_text = 'MTN B2C Balance:';

    $airtel_ug_utility_balance = fetchrow('o_summaries', "name='AIRTEL_UG_UTILITY_BALANCE' $andbranch_loans", "value_");
    $ug_airtel_utility_inline_text = "Airtel B2C Balance:";

    $airtel_ug_paybill_balance = fetchrow('o_summaries', "name='AIRTEL_UG_PAYBILL_BALANCE' $andbranch_loans", "value_");
    $airtel_ug_paybill_inline_text = "Airtel C2B Balance:";
} else {
    $utility_balance = fetchrow('o_summaries', "name='UTILITY_BALANCE' $andbranch_loans", "value_");
    $inline_text = 'B2C Balance:';
}

$paybill_balance = fetchrow('o_summaries', "name='PAYBILL_BALANCE' $andbranch_loans", "value_");
$sms_balance = fetchrow('o_summaries', "name='SMS_BALANCE'", "value_");

?>

<div class="row">
    <div class="col-sm-2">
        <a href="loans?start_date=<?php echo $date; ?>&end_date=<?php echo $date; ?>" class="small-box box box-solid bg-blue-gradient">
            <div class="inner">
                <span class='font-14'>Loans Today <i class="fa fa-external-link"></i></span>
                <p class="text-bold"> <?php echo $currencyUsed . " <span class='font-16' id='loans_today_lbl'>" . money($loans_today); ?></p>
            </div>

        </a>
    </div>
    <div class="col-sm-2">
        <a href="incoming-payments?start_date=<?php echo $date; ?>&end_date=<?php echo $date; ?>" class="small-box box box-solid bg-green-gradient">
            <div class="inner">
                <span class='font-14'>Payments Today <i class="fa fa-external-link"></i></span>
                <p class="text-bold font-16"> <?php echo $currencyUsed . " <span class='font-16' id='payments_today_lbl'>" . money($payments_today); ?></span></p>
            </div>

        </a>
    </div>
    <div class="col-sm-2">
        <a href="falling-due?start_date=<?php echo $date; ?>&end_date=<?php echo $date; ?>" class="small-box box box-solid bg-black">
            <div class="inner">
                <span class='font-14'>Due Today <i class="fa fa-external-link"></i> </span>
                <p class="text-bold"> <?php echo $currencyUsed . " <span class='font-16' id='due_today_lbl'>" . money($due_today); ?></span></p>
            </div>

        </a>
    </div>
    <div class="col-sm-2">
        <div class="small-box box box-solid bg-red">
            <div class="inner">
                <span class="font-14"><?php echo $inline_text; ?></span>
                <p class="text-bold"> <?php echo $currencyUsed . " <span class='font-16' id='utility_balance_lbl'>" . money($utility_balance); ?></span></p>

                <?php
                if ($cc == 256) { ?>
                    <span class="font-14"><?php echo $ug_airtel_utility_inline_text; ?></span>
                    <p class="text-bold"> <?php echo $currencyUsed ?> <span class="font-16" id='airtel_ug_utility_balance_lbl'><?php echo money($airtel_ug_utility_balance); ?></span></p>

                <?php } ?>
            </div>

        </div>
    </div>
    <div class="col-sm-2">
        <div class="small-box box box-solid bg-orange-active">
            <div class="inner">
                <?php
                if ($cc == 256) {
                    echo "<span class='font-14'>" . $airtel_ug_paybill_inline_text . "</span>";
                    echo "<p id='airtel_ug_paybill_balance' class='text-bold font-14'>$currencyUsed <span class='font-16' id='airtel_ug_paybill_balance_lbl'>" . money($airtel_ug_paybill_balance) . "</span></p>";
                } else {
                    echo "<span class='font-14'> C2B Balance:</span>";
                    echo "<p class='text-bold'>" . $currencyUsed . " <span class='font-16' id='paybill_balance_lbl'>" . money($paybill_balance) . "</span></p>";
                }
                ?>
            </div>

        </div>
    </div>
    <div class="col-sm-2">
        <div class="small-box box box-solid bg-aqua-gradient">
            <div class="inner">
                <span class='font-14'>SMS Balance:</span>
                <p class="text-bold">
                    <?php echo "<span class='font-16' id='sms_balance_lbl'>" . money($sms_balance) . "</span>"; ?>
                </p>
            </div>
        </div>
    </div>
</div>

<?php

function currencyUsed()
{
    global $cc;
    $currency = "";
    if ($cc == 256) {
        $currency = "UGX";
    } elseif ($cc == 254) {
        $currency = 'KES';
    } else if ($cc == 255) {
        $currency = 'TZS';
    }

    return $currency;
}

?>
