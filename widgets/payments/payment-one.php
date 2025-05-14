<section class="content-header">
    <?php
    $rep_ = $_GET['repayment'];
    $rep = decurl($rep_);
    $j = fetchonerow("o_incoming_payments", "uid= $rep", "*");
    $customer_id = intval($j['customer_id']);
    $payment_method = $j['payment_method'];
    $mobile_number = $j['mobile_number'];
    $amount = $j['amount'];
    $transaction_code = $j['transaction_code'];
    $loan_id = $j['loan_id'];
    $group_id = $j['group_id'];
    $payment_date = $j['payment_date'];
    $payment_datetime = $j['recorded_date'];
    $record_method = $j['record_method'];
    $status = $j['status'];
    $payment_category = $j['payment_category'];
    $collected_by = $j['collected_by'];
    $comments = $j['comments'];
    $added_by = $j['added_by'];


    $staff_names = table_to_obj('o_users', "status!=0", "100000", "uid", "name");

    $pay_meth = fetchrow('o_payment_methods', "uid='$payment_method'", "name");
    $pay_for = fetchrow('o_payment_categories', "uid='$payment_category'", "name");
    $status_name = fetchrow('o_payment_statuses', "uid='$status'", "name");
    if ($customer_id > 0) {
        $i = fetchonerow("o_customers", "uid='$customer_id'", "full_name, primary_mobile, national_id");
        $full_name = $i['full_name'];
        $primary_mobile = $i['primary_mobile'];
        $national_id = $i['national_id'];
    } else {
        $full_name = "<i>Unspecified</i>";
    }
    $loan_id = $j['loan_id'];
    $overpayment = 0;
    $split_for = "";
    if ($loan_id > 0) {
        $loan_balance_ = $j['loan_balance'];
        $loan_balance = money($loan_balance_);
        $l = loan_obj($loan_id);
        $next_due = $l['next_due_date'];
        $current_loan_bal = fetchrow('o_loans', "uid='$loan_id'", "loan_balance");
        if ($current_loan_bal < 0) {
            $overpayment = abs($current_loan_bal);
            $split_for = "overpayment";
        }
    } else {
        $loan_balance = "<i>Unspecified</i>";
        $next_due = "<i>Unspecified</i>";
        $split_for = "unallocated";
    }

    // a payment with no customer and no loan
    if ($loan_id == 0 && $customer_id == 0 && $amount > 0) {
        $split_for = "straypayment";
    }

    ?>
    <h1>
        <?php echo arrow_back("incoming-payments", "Payments") ?> Payment Info
        <small>Payment #<?php echo $rep; ?></small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="index"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Payments</li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs nav-justified font-16">
                    <li class="nav-item nav-100 active"><a href="#tab_1" data-toggle="tab" aria-expanded="false"><i class="fa fa-info"></i> Payment Info</a></li>
                    <li class="nav-item nav-100"><a href="#tab_2" onclick="otherPayments('<?php echo encurl($customer_id); ?>')" data-toggle="tab" aria-expanded="false"><i class="fa fa-money" aria-hidden="true"></i> Other Payments</a></li>
                    <li class="nav-item nav-100"><a href="#tab_3" onclick="paymentEvents('<?php echo $rep_; ?>')" data-toggle="tab" aria-expanded="false"><i class="fa fa-clock-o"></i> Events</a></li>

                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">

                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-info"></i></span>
                            </div>
                            <div class="col-md-7">
                                <table class="table-bordered font-14 table table-hover">
                                    <tr>
                                        <td class="text-bold">UID</td>
                                        <td><?php echo $rep; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Customer</td>
                                        <td><?php echo $full_name; ?>
                                            <span class="font-italic text-muted"></span><?php echo $national_id; ?> <a href="customers?customer=<?php echo encurl($customer_id) ?>"><i class="fa fa-external-link"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Amount</td>
                                        <td>
                                            <h4 class="text-bold text-20"><?php echo money($amount); ?></h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Pay Method</td>
                                        <td><?php echo $pay_meth; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Payment for</td>
                                        <td><?php echo $pay_for; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Record Type</td>
                                        <td><?php echo $record_method; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Loan Code</td>
                                        <td><?php echo $loan_id; ?> <a href="loans?loan=<?php echo encurl($loan_id) ?>" <i class='fa fa-external-link'></i> </a></td>
                                    </tr>
                                    <tr></tr>
                                    <tr>
                                        <td class="text-bold">Loan Balance</td>
                                        <td>
                                            <h4 class="text-bold text-20 text-danger"><?php echo $loan_balance_; ?></h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Pay Date</td>
                                        <td><?php echo $payment_date.'<br/><i>'.$payment_datetime; ?></i> </td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Transaction Code</td>
                                        <td><?php echo $transaction_code; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Collected By</td>
                                        <td><?php echo $staff_names[$collected_by] . "($collected_by)"; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Added By</td>
                                        <td><?php echo $staff_names[$added_by] . "($added_by)"; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Comments</td>
                                        <td><?php echo $comments; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Status</td>
                                        <td><?php echo $status_name; ?></td>
                                    </tr>


                                </table>
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr>
                                        <td><a href="incoming-payments?add-edit" class="btn btn-success btn-block  btn-md grid-width-10"><i class="fa fa-plus"></i> Add a Payment</a></td>
                                    </tr>
                                    <?php
                                    $permi = permission($userd['uid'],'o_incoming_payments',"0","update_");
                                    if ($permi == 1 && $status != 2) {
                                    ?>
                                        <tr>
                                            <td><a href="incoming-payments?add-edit=<?php echo $rep_; ?>" class="btn btn-warning btn-block btn-md"><i class="fa fa-pencil"></i> Edit this Payment</a></td>
                                        </tr>

                                    <?php
                                    }
                                    ?>

                                    <?php
                                    if ($display_payment_remove_btn == 1) {
                                    ?>
                                        <tr>
                                            <td><button onclick="delete_payment(<?php echo $rep_; ?>)" class="btn btn-danger btn-block btn-md"><i class="fa fa-pencil"></i> Remove this Payment</button></td>
                                        </tr>

                                    <?php
                                    }

                                    ?>



                                    <?php


                                    if (($overpayment == 0 || $amount < $overpayment) && ($loan_id > 0)) {

                                        /*
                                        split button should not be displayed:
                                        // - if the payment is already split
                                        - if the payment amount is less than the allocated loan overpayment
                                        - no overpayment for the allocated loan
                                        */
                                    // } else if (strpos($transaction_code, '-') === false) {
                                    } else {
                                        $modal_title = '';

                                        if ($split_for == "overpayment" && $group_id == 0) {
                                            $modal_title = 'Split an Overpayment';
                                        } elseif ($loan_id == 0 && $amount > 0 && $customer_id > 0 && $split_for == "unallocated" && $group_id == 0) {
                                            $modal_title = 'Split Unallocated Payment';
                                        } elseif ($split_for == "straypayment" && $group_id == 0) {
                                            $modal_title = 'Split Stray Payment';
                                        }

                                        if (!empty($modal_title)) {
                                    ?>
                                            <tr>
                                                <td>
                                                    <button style="background: #800080; color: #FFF;" class="btn btn-default btn-block btn-md" onclick="modal_view('/forms/split-<?php echo strtolower($split_for); ?>','pid=<?php echo $rep_; ?>&split_for=<?php echo $split_for; ?>&overpayment=<?php echo $overpayment; ?>&customer_id=<?php echo encurl($customer_id); ?>','<?php echo $modal_title; ?>'); modal_show();">
                                                        <i class='fa fa-chain-broken'></i> Split this Payment
                                                    </button>
                                                </td>
                                            </tr>
                                    <?php
                                        }
                                    }
                                    ?>


                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_2">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-money" aria-hidden="true"></i></span>
                            </div>
                            <div class="col-md-8">
                                <table class="table-bordered font-14 table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Amount</th>
                                            <th>Pay Method</th>
                                            <th>Record Type</th>
                                            <th>Transaction Code</th>
                                            <th>Loan Balance</th>
                                            <th>Pay Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="other_payments_placeholder">
                                        <tr>
                                            <td colspan='9'>
                                                <i>Loading other payments...</i>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_3">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                            </div>
                            <div class="col-md-10">
                                <table class="table-bordered font-14 table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Event</th>
                                            <th>Event ID</th>
                                        </tr>
                                    </thead>
                                    <tbody id="payment_events_placeholder">
                                        <tr>
                                            <td colspan='3'>
                                                <i>Loading events...</i>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                    <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
            </div>
        </div>
    </div>
</section>