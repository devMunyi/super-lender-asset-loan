<?php
$loan_id = $_GET['loan'];
recalculate_loan(decurl($loan_id));
$staff_obj = table_to_obj('o_users', "uid>0", "100000", "uid", "name");
$l = fetchonerow("o_loans", "uid='" . decurl($loan_id) . "'", "*");
$customer_ = encurl($l['customer_id']);
$total_repaid = $l['total_repaid'];
$balance = $l['loan_balance'];
$group_id = $l['group_id'];
$account_number = $l['account_number'];
$loan_type = $l['loan_type'];
$loan_code = trim($l['loan_code']);
$with_ni_validation = intval($l['with_ni_validation']);
if ($loan_type == 4) {
    $asset_id = $l['asset_id'];
    $asset_details = fetchonerow('o_assets', "uid='$asset_id'", "uid, name, photo, description");
}
$product_ = fetchonerow("o_loan_products", "uid='" . $l['product_id'] . "'", "name, disburse_method");
$product_name = $product_['name'];
$stage_name = fetchrow('o_loan_stages', "uid='" . $l['loan_stage'] . "'", "name");
$last_repay = fetchmaxid('o_incoming_payments', "loan_code='" . decurl($loan_id) . "'", "payment_date");
$last_repay_date = $last_repay['payment_date'];
$cleared_date = $l['cleared_date'];
$next_repay_date = $l['next_due_date'];
$next_repay_date_peep = "";
$customer = fetchonerow("o_customers", "uid='" . $l['customer_id'] . "'", "full_name, primary_mobile, national_id, phone_number_provider");
$national_id = $customer['national_id'];
$status = $l['status'];
$state_name = loan_state($status);
$primary_number = $customer['primary_mobile'];
$mobile_phone_provider = intval($customer['phone_number_provider'] ?? 1);
$MSISDN_provider = ($cc == 254) ? 'KE_SAF' : getMSISDNProvider($mobile_phone_provider);

$phone_providers = [
    1 => 'Mpesa',
    2 => 'Airtel Money',
    3 => 'Airtel Money',
    4 => 'MTN Money'
];

$mobile_phone_provider = $customer['phone_number_provider'];
$phone_provider_name = intval($customer['phone_number_provider']) > 0 ? "(" . $phone_providers[$mobile_phone_provider] . ")" : "";

///////////////////-----------------
////----------------Clear older Loans
$cust_id = fetchrow('o_loans', "uid=" . decurl($loan_id) . "", "customer_id");
$latest_ = fetchmax('o_loans', "customer_id='$cust_id'", "given_date", "uid");
$latest_loan = $latest_['uid'];
//$clearall = updatedb('o_loans',"disbursed=1, paid=1, status=5","customer_id='$cust_id' AND uid!='$latest_loan' AND status!=0 AND disbursed=1 AND status!=5");
////--------------Clear Older Loans
//////////////////------------------
$customer_id = $cust_id;
///---------------------Check for custom scripts
$scr = after_script($l['product_id'], "LOAN_CLEAR");
if ($scr != '0') {
    include_once "$scr";
    // echo $scr;
}
////------------------End of checking for custom scripts


$resend_version = $B2C_RMQ_IS_SET == 1 ? "2" : "";
$current_lo = $l['current_lo'];
$current_co = $l['current_co'];
$current_collector = $l['current_agent'];
$created_by = $l['added_by'];

if ($current_lo > 0) {
    $current_lo_name = $staff_obj[$current_lo];
} else {
    $current_lo_name = "<i>Unspecified</i>";
}
if ($current_co > 0) {
    $current_co_name = $staff_obj[$current_co];
} else {
    $current_co_name = "<i>Unspecified</i>";
}
if ($group_id > 0) {
    $group_name = fetchrow('o_customer_groups', "uid='$group_id'", "group_name");
} else {
    $group_name = 'Unspecified';
}

$payment_breakdown = obj2string(payment_break_down($l['product_id'], decurl($loan_id), $l['loan_amount'], $total_repaid));



$method = fetchonerow("o_disburse_methods", "uid='" . $product_['disburse_method'] . "'", "name, via_api");
$method_name = $method['name'];
$via_api = $method['via_api'];
if ($via_api == 1) {
    $mode = "Automatically";
} else {
    $mode = "Manually";
}



$npl_uid = $l['npl_uid'] ? $l['npl_uid'] : 0;
if ($npl_uid > 0) {
    $npl_details = fetchonerow('o_npl_categories', "uid=$npl_uid AND status = 1", "title, icon");

    $npl_icon = $npl_details['icon'] ?? '';
    $npl_title = $npl_details['title'] ?? '';
    $npl_det = "<a class='custom-badge pull' title='NPL:$npl_title'><img class='badge_img' height='24px'  src=\"badges/$npl_icon\"/></a>";
    $npl_form_action = "<i class=\'fa fa-pencil\'></i> Change NPL Tag";
} else {
    $npl_form_action = "<i class=\'fa fa-plus\'></i> Add NPL Tag";
    $npl_det = "";
}

?>

<section class="content-header">
    <h1>
        Loan Details
        <small>Loan #<?php echo decurl($loan_id) . ' ' . $badge_det;; ?> </small>





    </h1>
    <ol class="breadcrumb">
        <li>
        <table class="tablex" id="nav_direct">
            <tr>
                <td><a href="#" onclick="direct_search('o_loans','PREV','<?php echo $loan_id ?>')" class="btn btn-sm bg-gray-active font-bold font-14" title="Previous Loan"> &laquo; Prev</a> </td>
                <td><a href="#" onclick="direct_search('o_loans','NEXT','<?php echo $loan_id ?>')" class="btn btn-sm bg-gray-active font-bold font-14 pull-right" title="Next Loan"> &raquo; Next</a></td>
                <td><a href="#" onclick="direct_search('o_loans','ALL','<?php echo $loan_id ?>')" class="btn btn-sm bg-gray-light"> &raquo; All</a> </td>
                <td><input type="text" class="form-control" placeholder="Go to another loan #id" id="inp_goto"></td>
                <td><button  onclick="direct_search('o_loans','SEARCH','<?php echo $loan_id ?>')" class="btn btn-sm bg-blue-gradient"><i class="fa fa-arrow-right"></i></button></td>
            </tr>
        </table>
        </li>
    </ol>
</section>
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <?php
            if (isset($_GET['just-created'])) {
                /*  echo "<div class=\"alert bg-green\">
                <span class=\"text-white font-16 font-italic font-bold\"><i class=\"icon fa fa-check-circle\"></i> The Loan Was Created Successfully. You can add the AddOns or Deductions Now</span>
            </div>"; */
            } else {
                /* echo ' <div class="alert bg-yellow-gradient">
                <span class="text-black font-18 text-bold"><i class="icon fa fa-warning"></i> This loan requires your action</span>
            </div>'; */
            }
            ?>

            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs nav-justified font-16">
                    <li class="nav-item nav-100 active"><a href="#tab_1" data-toggle="tab" aria-expanded="false"><i class="fa fa-info"></i> Loan Info</a></li>
                    <li class="nav-item nav-100"><a href="#tab_2" onclick="loan_addons('<?php echo $loan_id; ?>')" data-toggle="tab" aria-expanded="false"><i class="fa fa-plus-circle"></i> AddOns</a></li>
                    <li class="nav-item nav-100"><a href="#tab_3" onclick="loan_deductions('<?php echo $loan_id; ?>')" data-toggle="tab" aria-expanded="false"><i class="fa fa-minus-circle"></i> Deductions</a></li>
                    <li class="nav-item nav-100"><a href="#tab_7" onclick="loanPaySchedule('<?php echo $loan_id; ?>')" data-toggle="tab" aria-expanded="false"><i class="fa fa-calendar"></i> Pay Schedule</a></li>
                    <?php if ($show_loan_statement == 1) {
                    ?>
                        <li class="nav-item nav-100"><a href="#tab_8" onclick="get_loan_statement('<?php echo $loan_id; ?>')" data-toggle="tab" aria-expanded="false"><i class="fa fa-money"></i> Loan Statement</a></li>
                    <?php } ?>
                    <li class="nav-item nav-100"><a href="#tab_9" onclick="loan_collateral_list('<?php echo $loan_id; ?>')" data-toggle="tab" aria-expanded="false"><i class="fa fa-car"></i> Collateral</a></li>
                    <li class="nav-item nav-100"><a href="#tab_4" onclick="loan_repayment_list('<?php echo $loan_id; ?>')" data-toggle="tab" aria-expanded="false"><i class="fa fa-arrow-circle-o-down"></i> Repayments</a></li>
                    <li class="nav-item nav-100"><a href="#tab_6" onclick="loanEvents('<?php echo $loan_id; ?>')" data-toggle="tab" aria-expanded="false"><i class="fa fa-clock-o"></i> Events</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="tab_1">

                        <div class="row">
                            <div class="col-md-2">
                                <?php
                                if ($loan_type == 4) {
                                    $image = $asset_details['photo'];
                                    $name = $asset_details['name'];
                                    $description = $asset_details['description'];
                                    $asset_id_ = encurl($asset_id);
                                    echo "<a href='assets?cat=cart&loan=$loan_id'> <h4>Assets</h4> <span class=\"image-card img-bordered\"><i class='fa fa-shopping-cart font-24'></i></span></a>
                                        ";
                                } else {
                                ?>
                                    <span class="info-box-icon"><i class="fa fa-info"></i></span>
                                <?php
                                }
                                ?>
                            </div>
                            <div class="col-md-6">
                                <table class="table-bordered font-14 table table-hover">
                                    <tr>
                                        <td class="text-bold">CODE</td>
                                        <td class="font-light font-18"><?php echo decurl($loan_id) . ' ' . $npl_det; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold"><?php echo $phone_providers[$mobile_phone_provider] ?> Code</td>
                                        <td class="font-light font-18"><?php echo $l['transaction_code']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Customer</td>
                                        <td><span class="font-16"><?php echo $customer['full_name'];  ?></span>
                                            <br /><span class="font-italic"><?php echo $customer['national_id'];  ?></span>
                                            <span class="font-italic text-muted"></span> <a href="customers?customer=<?php echo encurl($l['customer_id']); ?>"><i class="fa fa-external-link"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Mobile </td>
                                        <td>
                                            <h4 class="text-bold"><?php echo $account_number;  ?>

                                                <span class="font-italic"><?php echo $phone_provider_name; ?></span>
                                            </h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Principal</td>
                                        <td>
                                            <h4 class="text-bold"><?php echo money($l['loan_amount']);  ?></h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">AddOns</td>
                                        <td><?php echo money($l['total_addons']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Deductions</td>
                                        <td><?php echo money(loan_deductions(decurl($loan_id))); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Disbursed Amount</td>
                                        <td><?php echo money($l['disbursed_amount']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Repayable Amount</td>
                                        <td><?php echo money($l['total_repayable_amount']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Repaid.</td>
                                        <td><?php echo money($l['total_repaid']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Balance</td>
                                        <td>
                                            <h4 class="text-red text-bold"><?php echo money($balance); ?></h4>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Product</td>
                                        <td><?php echo $product_name; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Disbursed Date</td>
                                        <td><?php echo $l['given_date']; ?> <?php echo fancydate($l['given_date']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Due Date</td>
                                        <td><?php echo $l['final_due_date']; ?> <?php echo fancydate($l['final_due_date']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Last Repay Date</td>
                                        <td><?php echo $last_repay_date; ?> <span class="label label-default"><?php echo ($last_repay_date_peep); ?></span></td>
                                    </tr>
                                    <?php
                                    if($cleared_date != '0000-00-00'){

                                    ?>
                                    <tr>
                                        <td class="text-bold">Cleared Date</td>
                                        <td><?php echo $cleared_date; ?> <?php echo fancydate($cleared_date); ?></td>
                                    </tr>
                                    <?php
                                    }
                                    ?>
                                    <tr>
                                        <td class="text-bold">Next Repay Date</td>
                                        <td><?php echo $next_repay_date; ?> <?php echo fancydate($next_repay_date); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Current Instalment</td>
                                        <td><?php echo $l['current_instalment']; ?> </td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Current Instalment Amount</td>
                                        <td><?php echo money($l['current_instalment_amount']); ?> </td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Current LO </td>
                                        <td><i class='fa text-red fa-user-circle'></i> <?php echo $current_lo_name; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Current CO </td>
                                        <td><i class='fa text-blue fa-user-circle'></i> <?php echo $current_co_name; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Current Collector </td>
                                        <td><i class='fa text-blue fa-user-circle'></i> <?php echo $staff_obj[$current_collector] . "($current_collector)"; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Created By </td>
                                        <td><i class='fa text-blue fa-user-circle'></i> <?php echo $staff_obj[$created_by] . "($current_collector)"; ?></td>
                                    </tr>
                                    <?php
                                    if ($group_loans == 1) {
                                    ?>
                                        <tr>
                                            <td class="text-bold">Group </td>
                                            <td><i class='fa text-green fa-users'></i> <?php echo $group_name; ?></td>
                                        </tr>
                                    <?php
                                    }
                                    ?>

                                    <?php

                                    $sec = $l['other_info'];
                                    $sec_obj = (json_decode($sec, true));

                                    foreach ($sec_obj as $key => $value) {
                                        echo " <tr class='bg-gray'><td class=\"text-bold\">$key</td><td class='font-bold; font-16; text-bold'>$value</td></tr>";
                                    }


                                    ?>
                                    <tr>
                                        <td class="text-bold">Stage</td>
                                        <td><?php echo ($stage_name); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="text-bold">Status</td>
                                        <td><?php echo ($state_name); ?></td>
                                    </tr>
                                </table>
                                <table style="display: none;" class="table-bordered font-14 table table-hover">
                                    <thead>
                                        <tr>
                                            <td>
                                                <h4>Breakdown</h4>
                                            </td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <?php
                                                echo $payment_breakdown;
                                                ?>
                                            </td>
                                        </tr>
                                    </tbody>

                                </table>

                                <table class="table table-bordered font-14 table table-hover">
                                    <tr>
                                        <td>
                                            <?php
                                            $edit_permission = permission($userd['uid'], 'o_loans', "0", "update_");
                                            if ($edit_permission == 1) {
                                                echo "<button class='btn btn-primary' onclick=\"modify_loan('$loan_id');\"><i class=\"fa fa-edit\"></i> Modify</button>";

                                                if ($has_archive == 1) {
                                                    if (isset($_SESSION['archives'])) {
                                                        echo " <button class='btn bg-maroon-gradient' onclick=\"move_loan_to_current('" . decurl($loan_id) . "');\"><i class=\"fa fa-angle-double-right\"></i> Move to Current</button> ";
                                                    }
                                                }
                                            }
                                            $change_pair  = permission($userd['uid'], 'o_pairing', "0", "update_");

                                            if ($change_pair == 1) {
                                                $cu = $l['customer_id'];
                                                echo " <button onclick=\"change_customer_agent('$cu')\" class=\"btn  bg-maroon-gradient btn-md\"><i class=\"fa  fa-user-circle-o\"></i> Change Agent</button> ";
                                            }
                                            ?>

                                            <button class="btn btn-warning btn-md"><i class="fa fa-flag"></i> Flag</button>
                                            <?php

                                            if ($edit_permission == 1) {?>
                                            <button onclick="loan_action_modal('<?php echo $loan_id; ?>')" class="btn btn-success btn-md"><i class="fa fa-hand-o-up"></i> Action</button>
                                            <?php
                                            }
                                            ?>

                                        </td>
                                    </tr>
                                </table>

                            </div>
                            <div class="col-md-4">

                                <?php

                                if ($status == 1) {
                                ?>

                                    <?php
                                    $change_loan_stage = permission($userd['uid'], 'o_loan_stages', "0", "general_");
                                    if ($change_loan_stage == 1) {
                                    ?>
                                        <h4 class="font-bold">Loan Stages</h4>
                                        <div id="loan_stages">
                                            Loading stages...
                                        </div>


                                    <?php
                                    }
                                } elseif (in_array($status, [2, 12])) {
                                    //////------Pending Disbursement
                                    echo "<h4 class=\"font-bold\">Loan Status " . $state_name . "</h4>";
                                    if ($via_api == 1) {
                                        $resend_action = "<button onclick=\"resend_loan($loan_id, '$MSISDN_provider', '$resend_version');\" class='btn btn-block btn-primary bg-blue-gradient'><i class='fa fa-refresh'></i> Resend</button>";
                                        echo "<div class='well well-sm'>
                                           <h4 class='font-italic'>The loan will be sent automatically via <b>$method_name</b> </h4>
                                            $resend_action
                                          </div>";
                                    } else {
                                        $record_action = "<button class='btn btn-block btn-primary bg-blue-gradient'><i class='fa fa-check'></i> Done</button>";
                                        echo "<div class='well well-sm'>
                                           <h4 class='font-italic'>Please send the funds manually via <b>$method_name</b> </h4>
                                           $record_action
                                          </div>";
                                    }


                                    if ($cc == 256 && strlen($loan_code) == 12 && $mobile_phone_provider == 3) {
                                    ?>
                                        <table class="table-bordered font-14 table table-hover">
                                            <tr>
                                                <td>
                                                    <button id="b2c-resend" onclick="ugAirtelUGB2CStatusEnquiry('<?php echo $loan_id; ?>')" class="btn btn-block btn-info bg-info-gradient">Enquire B2C Transaction Status</button>
                                                </td>
                                            </tr>
                                        </table>

                                    <?php
                                    }

                                    if ($cc == 256 && strlen($loan_code) == 36 && $mobile_phone_provider == 4) {
                                    ?>
                                        <table class="table-bordered font-14 table table-hover">
                                            <tr>
                                                <td>
                                                    <button id="b2c-resend" onclick="mtnUGB2CStatusEnquiry('<?php echo $loan_id; ?>')" class="btn btn-block btn-info bg-info-gradient">Enquire B2C Transaction Status</button>
                                                </td>
                                            </tr>
                                        </table>
                                <?php
                                    }



                                    // add btn for allowing toggle with_nid_validation either 0 or 1
                                    if ($send_money_with_nid_validation == 1) {
                                        if ($with_ni_validation == 0) {
                                            echo "<div class='well well-sm'><button id='toggle-b2c-validation' class='btn btn-block btn-md btn-info bg-blue-gradient' onclick=\"toggle_b2c_validation($loan_id, 1, 'Enable B2C Validation');\">Enable B2C Validation</button></div>";
                                        } elseif ($with_ni_validation == 1) {
                                            echo "<div class='well well-sm'><button id='toggle-b2c-validation' class='btn btn-block btn-md btn-info bg-blue-gradient' onclick=\"toggle_b2c_validation($loan_id, 0, 'Disable B2C Validation');\">Disable B2C Validation</button></div>";
                                        }
                                    }
                                } elseif ($status == 3) {
                                    echo "<h4 class=\"font-bold\">Loan Status " . $state_name . "</h4>";
                                } elseif ($status == 4) {
                                    echo "<h4 class=\"font-bold\">Loan Status " . $state_name . "</h4>";
                                } elseif ($status == 5) {
                                    ////-------Cleared
                                    echo "<h4 class=\"font-bold\">Loan Status " . $state_name . "</h4>";
                                    echo "<br/><div class='well well-sm'><button class='btn btn-block btn-lg btn-primary bg-blue-gradient' onclick=\"change_loan_status('$loan_id','3','Disbursed');\">Mark as Not Cleared</button></div>";
                                } elseif ($status == 6) {
                                    echo "<h4 class=\"font-bold\">Loan Status " . $state_name . "</h4>";
                                }

                                if ($stk_push_ready == 1) {
                                    echo "<br/><div style='display: noe;' class='well well-sm'>
                                     <input type=\"number\" class='form-control' style='width: 50%; display: inline-block;' id=\"stk_amount\" value=\"$balance\">
<button class='btn btn  bg-maroon' onclick=\"stk_push('$account_number','$balance');\"><i class=\"fa fa-exchange\"></i> STK Push</button></div>";
                                }

                                ?>
                                <table class="table-bordered font-14 table table-hover">
                                    <tr>
                                        <td><button onclick="interactions_popup(<?php echo encurl($cust_id); ?>)" class="btn btn-block bg-orange-active btn-md"><i class="fa  fa-comments-o"></i> Interactions</button></td>
                                    </tr>

                                    <tr>
                                        <td>
                                            <button style="background: #800080; color: #FFF;" class="btn btn-default btn-block btn-md" onclick="modal_view('/forms/loan-add-edit-npltag','loan_id=<?php echo $loan_id; ?>','<?php echo $npl_form_action; ?>'); modal_show();">
                                                <?php echo $npl_uid > 0 ? '<i class=\'fa fa-pencil\'></i>' : '<i class=\'fa fa-plus\'></i>'; ?> <?php echo $npl_form_action; ?>
                                            </button>
                                        </td>
                                    </tr>
                                </table>



                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_2">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-plus-circle"></i></span>
                            </div>
                            <div class="col-md-7" id="loan_addons">
                                Loading...
                            </div>
                            <div class="col-md-3">
                                <table class="table" style="display: none;">
                                    <tr>
                                        <td><button class="btn btn-default"><i class="fa  fa-gear"></i> AddOn Settings</button></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_3">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-minus"></i></span>
                            </div>
                            <div class="col-md-7">
                                <div class="col-md-7" id="loan_deductions">
                                    Loading...
                                </div>
                            </div>
                            <div class="col-md-3">
                                <table class="table" style="display: none;">
                                    <tr>
                                        <td><button class="btn btn-default"><i class="fa  fa-gear"></i> Deduction Settings</button></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_4">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-arrow-circle-o-down"></i></span>
                            </div>
                            <div class="col-md-7" id="repayments_">
                                Loading repayments ...
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr>
                                        <td><a href="incoming-payments?add-edit" class="btn btn-primary btn-block btn-md"><i class="fa fa-plus"></i> Record Payment</a></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_6">
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
                                    <tbody id="loan_events_placeholder">
                                        <tr>
                                            <td><i>Loading events...</i></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_7">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-minus"></i></span>
                            </div>
                            <div class="col-md-7">
                                <table class="table-bordered font-14 table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Balance</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="repay_schedule_placeholder">
                                        <tr>
                                            <td><i>Loading repayment schedule...</i></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr>
                                        <td><button class="btn btn-default"><i class="fa  fa-print"></i> Print Schedule</button></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- /.tab-pane -->
                    <div class="tab-pane" id="tab_8">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                            </div>
                            <div class="col-md-7" id="statement_">
                                Loading statement ...
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr>
                                        <td><a href='extensions/loan_statement_pdf?loan_id=<?php echo $loan_id; ?>' class="btn btn-default" target="_blank"><i class="fa fa-print"></i> Generate Statement</a></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane" id="tab_9">
                        <div class="row">
                            <div class="col-md-2">
                                <span class="info-box-icon"><i class="fa fa-car"></i></span>
                            </div>
                            <div class="col-md-7" id="collateral_">
                                Loading collateral ...
                            </div>
                            <div class="col-md-3">
                                <table class="table">
                                    <tr>
                                        <td><a href="customers?customer-add-edit=<?php echo $customer_; ?>&collateral" class="btn btn-primary"><i class="fa  fa-edit"></i> Add/
                                                Edit Collateral</a></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.tab-content -->
            </div>
        </div>
    </div>
</section>