<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();

$where_ = $_POST['where_'] ?? '';
$offset_ = $_POST['offset'] ?? 0;
$rpp_ = $_POST['rpp'] ?? 10;
$page_no = $_POST['page_no'] ?? 1;
$orderby = $_POST['orderby'] ?? 'uid';
$dir = $_POST['dir'] ?? 'DESC';
$search_ = sanitizeAndEscape($_POST['search_'], $con);
$need_approval = $_POST['need_approval'];

$limit = "$offset_, $rpp_";
$alltotal = 0;
$noRows = noRowSpan(13);

function getLoanSearchCondition($search_, $branchCondition)
{
    $andsearch = "";
    $branchUserCondition = $branchCondition['branchUserCondition'] ?? "";
    $cust_array = array();

    if (preg_match("/\d{4}-\d{2}-\d{2}/", $search_)) {
        $andsearch = " AND (given_date = '$search_' OR final_due_date = '$search_')";
        return $andsearch;
    } else {
        if (is_numeric($search_)) {
            // check if is a phone number & validate it
            $validated_phone = make_phone_valid($search_);
            if (validate_phone($validated_phone) == 1) {
                $customerWhereCondition = " (primary_mobile = '$validated_phone')";
            } else {
                //  national_id
                $customerWhereCondition = " (national_id = '$search_')";
            }
        } else {
            // search by full_name
            $customerWhereCondition = " (full_name LIKE '%$search_%')";
        }

        $customers = fetchtable('o_customers', "$customerWhereCondition $branchUserCondition", "uid", "asc", "10", "uid");
        $customer_lists = mysqli_num_rows($customers);
        if ($customer_lists > 0) {
            while ($cu = mysqli_fetch_array($customers)) {
                $customer_uid = $cu['uid'];
                array_push($cust_array, $customer_uid);
            }
            $customer_list = implode(",", $cust_array);
            $andsearch = " AND customer_id in ($customer_list)";
        }

        function hasNumbersAndLetters($str) {
            return preg_match('/[a-zA-Z]/', $str) && preg_match('/[0-9]/', $str);
        }

        if($andsearch == ""){
            global $cc;

            if (validate_phone($validated_phone) == 1) {
                $andsearch = " AND (account_number = '$validated_phone')";
            }elseif($cc == 254 && hasNumbersAndLetters($search_)){
                $andsearch = " AND (transaction_code = '$search_')";
            }elseif($cc == 254 && !hasNumbersAndLetters($search_)){
                $andsearch = " AND (uid = '$search_' OR loan_amount = '$search_')";
            }else {
                $andsearch = " AND (uid = '$search_' OR transaction_code = '$search_' OR loan_amount = '$search_')";
            }
        }
    }

    return $andsearch;
}

$branchCondition = getBranchCondition($userd, 'o_loans', 'current_branch');
$branchLoanCondition = $branchCondition['branchLoanCondition'] ?? "";
$whereCondition = "$where_ $branchLoanCondition";

// if there is search input
if ((input_available($search_)) == 1) {
    // echo "searching...<br>";
    $searchCondition = getLoanSearchCondition($search_, $branchCondition);
} else {
    // echo "no search input<br>";
    $searchCondition = "";
}

if ($searchCondition == "" && input_available($search_) == 1) {
    echo "$noRows";
} else {

    //-----------------------------Reused Query
    if ($need_approval == "need approval") {
        $o_loans_ = fetchtable('o_loans', "$whereCondition AND status IN (1, 2) $searchCondition", "$orderby", "$dir", "$limit", "*");
        ///----------Paging Option
        $alltotal = countotal_withlimit("o_loans", "$whereCondition AND status IN (1, 2) $searchCondition", "uid", "1000");
        $loan_total_amount = totaltable("o_loans", "$whereCondition AND status IN (1, 2) $searchCondition", "loan_amount");

        $loan_list = table_to_array('o_loans', "$whereCondition AND status IN (1, 2) $searchCondition", "$limit", "uid", "$orderby", "$dir");

        $customers_list_array = table_to_array('o_loans', "$whereCondition AND status IN (1, 2) $searchCondition", "$limit", "customer_id", "$orderby", "$dir");
        ///==========Paging Option
    } else {
        $o_loans_ = fetchtable('o_loans', "$whereCondition AND status > 0 $searchCondition", "$orderby", "$dir", "$limit", "*");
        ///----------Paging Option
        $alltotal = countotal_withlimit("o_loans", "$whereCondition AND status > 0 $searchCondition", "uid", "1000");

        $loan_list = table_to_array('o_loans', "$whereCondition AND status > 0 $searchCondition", "$limit", "uid", "$orderby", "$dir");

        $customers_list_array = table_to_array('o_loans', "$whereCondition AND status > 0 $searchCondition", "$limit", "customer_id", "$orderby", "$dir");
        ///==========Paging Option
    }

    $loan_l = implode(',', $loan_list);

    $loan_addons_array = array();
    $loan_addons = fetchtable('o_loan_addons', "status=1 AND loan_id in ($loan_l)", "uid", "asc", "1000000", "uid, loan_id");
    while ($la = mysqli_fetch_array($loan_addons)) {
        $lid = $la['uid'];
        $loan_id = $la['loan_id'];
        $loan_addons_array = obj_add($loan_addons_array, $loan_id, 1);
    }

    $customers_list = implode(',', $customers_list_array);


    $staff_obj = table_to_obj('o_users', "uid>0", "100000", "uid", "name");
   // $customer_name_array = table_to_obj('o_customers', "uid in ($customers_list)", "1000", "uid", "full_name");

    ////////////------------Customer details
    //  $customer_det = table_to_obj('o_customers', "uid in($customers_list)", 1000000, "uid", "full_name");
    $customer_det = array();
    $customer_name_array = array();
    $customer_flag_det = array();
    $customer_badge_det = array();

    $cust_ = fetchtable('o_customers', "uid in($customers_list)", 'uid','asc',100, "uid,full_name,badge_id, flag");
    while($cus = mysqli_fetch_array($cust_)){
        $cuid = $cus['uid'];
        $cust_name = $cus['full_name'];
        $badge_id = $cus['badge_id'];
        $flag = $cus['flag'];
        $customer_name_array[$cuid] = $cust_name;
        $customer_badge_det[$cuid] = $badge_id;
        $customer_flag_det[$cuid] = $flag;
    }
    //////////------------End of customer details

    $flagsDetails = table_to_obj2('o_flags', "uid>0", "100", "uid", array('name', 'color_code'));
    //---badges names =
    $badgesDetails = table_to_obj2('o_badges', "uid>0", "30", "uid", array('title', 'icon'));





    $statusDet = table_to_obj2('o_loan_statuses', "uid > 0", 100, "uid", array('name', 'color_code'));
    $branches_array = table_to_obj('o_branches', "uid>0", "1000", "uid", "name");
    $products_array = table_to_obj('o_loan_products', "uid > 0", "100", "uid", "name");
    $stages_array = table_to_obj('o_loan_stages', "uid > 0", "100", "uid", "name");
    $flags = flags();

    $flagsDetails = table_to_obj2('o_flags', "uid>0", "100", "uid", array('name', 'color_code'));
    //---badges names =
    $badgesDetails = table_to_obj2('o_badges', "uid>0", "30", "uid", array('title', 'icon'));


    $type_name_array = array();
    $type_icon_array = array();
    $loan_types = fetchtable('o_loan_types', "uid>0", "uid", "asc", "100");
    while ($l = mysqli_fetch_array($loan_types)) {
        $tid = $l['uid'];
        $ticon = $l['icon'];
        $tname = $l['name'];
        $type_name_array[$tid] = $tname;
        $type_icon_array[$tid] = $ticon;
    }


    if ($alltotal > 0) {
        while ($n = mysqli_fetch_array($o_loans_)) {
            $uid = $n['uid'];
            $customer_id = $n['customer_id'];
            $full_name = $customer_name_array[$customer_id];

            $product_id = $n['product_id'];
            $loan_amount = $n['loan_amount'];
            $mobile = $n['account_number'];
            $loan_type = $n['loan_type'];
            $group_id = $n['group_id'];
            $asset_id = $n['asset_id'];
            $disbursed_amount = $n['disbursed_amount'];
            $period = $n['period'];
            $period_units = $n['period_units'];
            $payment_frequency = $n['payment_frequency'];
            $payment_breakdown = $n['payment_breakdown'];
            $total_addons = $n['total_addons'];
            $count_addons = $loan_addons_array[$uid];
            $total_deductions = $n['total_deductions'];     // $count_deductions = countotal_withlimit('o_loan_deductions',"loan_id='$uid' AND status=1","uid","100");
            $total_instalments = $n['total_instalments'];
            $total_instalments_paid = $n['total_instalments_paid'];
            $current_instalment = $n['current_instalment'];
            $given_date = $n['given_date'];
            // $created_datetime = $n['transaction_date'] ?? $n['added_date'];
            $created_datetime = $n['added_date'];

            $dtime = new DateTime($created_datetime);
            $time = $dtime->format("h:iA");
            $next_due_date = $n['next_due_date'];
            $final_due_date = $n['final_due_date'];
            $added_by = $n['added_by'];
            $added_date = $n['added_date'];
            $loan_stage = $n['loan_stage'];

            $f = $n['loan_flag'];
            $loan_flag = $flags[$f]['name'];
            $flag_color = $flags[$f]['color_code'];
            $loan_status_id = $n['status'] ?? 0;
            $status_color = $statusDet[$loan_status_id]['color_code'] ?? '';
            $status_name = $statusDet[$loan_status_id]['name'] ?? '';

            if (input_available($loan_flag)) {
                $loan_flag_d = "<span style='color: $flag_color;'><i class='fa fa-flag'></i> $loan_flag</span>";
            } else {
                $loan_flag_d = "";
            }

            $current_branch = $n['current_branch'];
            $transaction_code = $n['transaction_code'];
            $transaction_date = $n['transaction_date'];
            $application_mode = $n['application_mode'];
            $current_lo = $n['current_lo'];
            $current_co = $n['current_co'];
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


            $status = $n['status'];
            $product_name = $products_array[$product_id];
            $stage_name = $stages_array[$loan_stage];
            $loan_branch = $branches_array[$current_branch];
            $repaid = $n['total_repaid'];
            $balance = $n['loan_balance'];

            if ($status != 1) {
                $stage_name = "";
            }

           /* if ($loan_type > 0) {
                $type_name = $type_name_array[$loan_type];
                $type_icon = $type_icon_array[$loan_type];
                $group_ide = encurl($group_id);
                if ($loan_type == 3) {
                    $url = "groups?group=$group_ide";
                } elseif ($loan_type == 4) {
                    $url = "assets.php?cat=asset&asset=" . encurl($asset_id);
                }
                $icon = "<div class='pull-left margin-r-5'><a href='$url' title='$type_name loan' class='text-blue font-18'><img height='24px' src='dist/img/$type_icon'/></a></div> ";
            } else {
                $icon = "";
            }   */


            $date_only = $dtime->format("Y-m-d");
            if (new DateTime($date_only) != new DateTime($given_date) || extractTimeFromDate($created_datetime) == "00:00:00") {
                $time = "";
            }

            $disbursed_date_cell = '';
            if ($given_date == '0000-00-00') {
                $disbursed_date_cell = "<td><i>N/A</i></td>";
            } else {
                $disbursed_date_cell = "<td><span>$given_date</span><br/> <span class=\"text-orange font-13 font-bold\">" . fancydate($given_date) . '<span class=\'text-blue font-400\'>' . $time . '</span>' . "</span></td>";
            }



            ////------------Badge and flag
            $flag = $customer_flag_det[$customer_id];
            if ($flag > 0) {
                $flag_n = $flagsDetails[$flag]['name'] ?? '';
                $flag_c = $flagsDetails[$flag]['color_code'] ?? '';
                $flag_d = "<span><i class='fa fa-flag' style='color: $flag_c;'></i> $flag_n</span>";
            } else {
                $flag_d = "";
            }

            $badge_id = $customer_badge_det[$customer_id];
            if ($badge_id > 0) {
                $badge_title = $badgesDetails[$badge_id]['title'] ?? '';
                $badge_icon = $badgesDetails[$badge_id]['icon'] ?? '';
                $badge = "<a title='$badge_title'><img src=\"badges/$badge_icon\" height='18px'/></a>";
            } else {
                $badge = "";
            }
            ///-------------Badge and Flag


            $row .= "<tr><td class='font-14 font-bold'>" . $uid . "<br/> <span style='font-size: 12px;' class=\"text-muted  text-maroon font-13 text-green\">$application_mode</span></td>
                            <td>$badge $full_name <a title='View Customer' href=\"customers?customer=" . encurl($customer_id) . "\"><i class=\"fa fa-external-link\"></i></a></span><br/> <span class=\"text-muted font-13 font-bold\">$mobile</span></div>
                            </td>
                            <td><span class=\"text-bold text-blue font-14\">$loan_amount</span><br/> <span class='font-13'>$loan_branch</span></td>
                            <td><span>$total_addons</span> <br/><span class='label label-default'>$count_addons</span>
                            </td>
                            <td><span>$total_deductions</span><br/> </td>
                            <td><span class='text-green'>" . money($repaid) . "</span></td>
                            <td><span class=\"font-bold text-red font-16\">" . money($balance) . "</span><br/> <span class=\"text-muted font-13 font-italic\">Next: " . $n['next_due_date'] . "</span></td>
                            $disbursed_date_cell
                            <td><span>Final: <b>$final_due_date</b></span><br/> <span class=\"text-black font-12 font-italic\">" . fancydate($final_due_date) . "</span></td>
                            <td><span class=\"text-black\"> <i class='fa text-red fa-user-circle'></i> LO: <b>$current_lo_name </b> <br/> <i class='fa text-blue fa-user-circle'></i> CO: <b>$current_co_name</b> </span></td>
    
                            <td><span class='label custom-color' style='background-color: " . $status_color . ";'>" . $status_name . "</span> <br/> $stage_name</td>
                            <td>$flag_d</td>
                            <td><span><a href=\"?loan=" . encurl($uid) . "\"><span class=\"fa fa-eye text-green\"></span></a></span><h4><a href=\"#\" title='Popup' onclick=\"interactions_popup('" . encurl($customer_id) . "')\"><i class=\"fa fa-comments-o text-orange\"></i></a></h4></td>
    
                        </tr>";
        }
    } else {
        echo "$noRows";
    }
}


echo   trim($row) . "<tr style='display: none;'><td><input type='hidden' id='_alltotal_' value='$alltotal'><input type='hidden' id='_pageno_' value='$page_no'><input type='hidden' value='$loan_total_amount' id='_summary_'></td></tr>";
include_once("../../configs/close_connection.inc");
