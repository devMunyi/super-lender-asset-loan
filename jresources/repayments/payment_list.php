<?php
session_start();

include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
$userd = session_details();

$where_ = $_POST['where_'] ?? '';
$offset_ = $_POST['offset'] ?? 0;
$rpp_ = $_POST['rpp'] ?? 10;
$page_no = $_POST['page_no'] ?? 1;
$orderby = $_POST['orderby'] ?? "uid";
$dir = $_POST['dir'] ?? "DESC";
$search_ = trim($_POST['search_'] ?? '');
$limit = "$offset_, $rpp_";
$alltotal = 0;
$rows = "";
$noRows = noRowSpan(11);


function getPaymentSearchCondition($search_, $branchCondition)
{
    $andsearch = "";
    $branchUserCondition = $branchCondition['branchUserCondition'] ?? "";
    $cust_array = array();

    if (preg_match("/\d{4}-\d{2}-\d{2}/", $search_)) {
        return " (payment_date = '$search_')";
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

            // check if search has @ symbol to search  by email
            if (strpos($search_, '@') !== false) {
                $customerWhereCondition = " (email_address LIKE '%$search_%')";
            } else {
                // search by full_name
                $customerWhereCondition = " (full_name LIKE '%$search_%')";
            }
        }

        $customers = fetchtable('o_customers', "$customerWhereCondition $branchUserCondition", "uid", "asc", "10", "uid");
        $customer_lists = mysqli_num_rows($customers);
        if ($customer_lists > 0) {
            while ($cu = mysqli_fetch_array($customers)) {
                $customer_uid = $cu['uid'];
                array_push($cust_array, $customer_uid);
            }
            $customer_list = implode(",", $cust_array);
            $andsearch = " customer_id in ($customer_list)";
        }

        function hasNumbersAndLetters($str)
        {
            return preg_match('/[a-zA-Z]/', $str) && preg_match('/[0-9]/', $str);
        }

        if ($andsearch == "") {
            global $cc;
            if (validate_phone($validated_phone) == 1) {
                $andsearch = " (mobile_number = '$validated_phone')";
            }elseif ($cc == 254 && hasNumbersAndLetters($search_)) {
                $andsearch = " (transaction_code LIKE '%$search_%')";
            } elseif ($cc == 254 && is_numeric($search_)) {
                $andsearch = " (uid = '$search_' OR amount = '$search_')";
            } else {
                $andsearch = " (uid = '$search_' OR transaction_code LIKE '%$search_%' OR amount = '$search_')";
            }
        }
    }

    return $andsearch;
}

$branchCondition = getBranchCondition($userd, 'o_incoming_payments', 'branch_id', 'branchPaymentCondition');
$branchPaymentCondition = $branchCondition['branchPaymentCondition'] ?? "";
$whereCondition = "$where_ $branchPaymentCondition";

////-------------Check if a user has permission to update a loan to tie it with viewing unallocated
$permi = permission($userd['uid'], 'o_incoming_payments', "0", "general_");
if ($permi == 1) {
    $whereCondition .= " AND loan_id > -1";
} else {
    $whereCondition .= " AND loan_id > 0";
}

// if there is search input
if ((input_available($search_)) == 1) {
    // echo "searching...<br>";
    $searchCondition = getPaymentSearchCondition($search_, $branchCondition);
    $whereCondition = "$whereCondition AND $searchCondition";
} else {
    // echo "no search input<br>";
    $searchCondition = "";
}

if (empty($searchCondition) && !empty($search_)) {
    $rows = "$noRows";
    
} else {
    ///////////////===================End of search with given keywords

    $statuses = table_to_obj('o_payment_statuses', "uid > 0", "100", "uid", "name");
    $statuses[0] = 'Deleted';
    $branches_array = table_to_obj('o_branches', "uid>0", "1000", "uid", "name");
    $methods_array = table_to_obj('o_payment_methods', "uid>0", "1000", "uid", "name");


    //-----------------------------Reused Query
    $o_pays_ = fetchtable("o_incoming_payments", "$whereCondition AND status > 0", "$orderby", "$dir", "$limit", "*");

    $customer_phones = array();
    $customer_ids = array();
    $customers_list = fetchtable("o_incoming_payments", "$whereCondition AND status > 0", "$orderby", "$dir", "$limit", "customer_id, mobile_number");
    while ($c = mysqli_fetch_array($customers_list)) {
        $customer_id = $c['customer_id'];
        $customer_mob = $c['mobile_number'];

        array_push($customer_ids, $customer_id);
        array_push($customer_phones, $customer_mob);
    }

    $customer_ids_list = implode(',', $customer_ids);
    $customer_phones_list = implode("','", $customer_phones);
    $customer_phones_list = "'$customer_phones_list'";


    $i = array();
    $customer_names = array();
    $customer_ids = array();
    $customer_phones = array();
    $customer_details = fetchtable('o_customers', "uid in ($customer_ids_list) OR primary_mobile in ($customer_phones_list)", "uid", "asc", "1000000", "uid, full_name, national_id, primary_mobile");
    while ($cus = mysqli_fetch_array($customer_details)) {
        $i['uid'] = $cus['uid'];
        $i['full_name'] = $cus['full_name'];
        $i['national_id'] = $cus['national_id'];

        $customer_names[$cus['uid']] = $cus['full_name'];
        $customer_ids[$cus['uid']] = $cus['national_id'];
        $customer_uids[$cus['primary_phone']] = $cus['uid'];
    }


    ///----------Paging Option
    $alltotal = countotal_withlimit("o_incoming_payments", "$whereCondition AND status > 0", "uid", "1000");
    ///==========Paging Option

    //----End of Reused Query
    $payment_categories = table_to_obj('o_payment_categories', "uid > 0", "100", "uid", "name");
    if ($alltotal > 0) {
        while ($q = mysqli_fetch_array($o_pays_)) {
            $uid = $q['uid'];
            $customer_id = $q['customer_id'];
            $branch_id = $q['branch_id'];
            $branch_name_ = $branches_array[$branch_id] ?? '';
            $payment_method = $q['payment_method'];
            $pay_meth = $methods_array[$payment_method] ?? '';
            $mobile_number = $q['mobile_number'];
            $amount = money($q['amount']);
            $transaction_code = $q['transaction_code'];
            $payment_date = $q['payment_date'];
            $recorded_date = $q['recorded_date'];
            $status = $q['status'];
            $status_name = $statuses[$status] ?? '';
            $dtime = new DateTime($recorded_date);
            $time = $dtime->format("h:iA");
            $date_only = $dtime->format("Y-m-d");
            $payment_category = $q['payment_category'];
            $payment_category_name = $payment_categories[$payment_category] ?? '';
            $loan_id = $q['loan_id'];
            $loan_code = $q['loan_code'];
            if ($loan_id > 0) {
                $loan_link = "<a title='Go to Loan' href=\"loans?loan=" . encurl($loan_id) . "\"><i class=\"fa fa-external-link\"></i></a>";
            } else {
                $loan_link = "";
            }
            $record_method = $q['record_method'];
            $loan_balance = money($q['loan_balance']);

            if ($customer_id > 0) {
                $cid_en = encurl($customer_id);
                $full_name = $customer_names[$customer_id] ?? '';
                $primary_mobile = $customer_phones[$customer_id] ?? '';
                $national_id = $customer_ids[$customer_id] ?? '';
            } else {
                $payer_mobile = $q['mobile_number'];
                $customer_id = $cid_en = 0;
                $full_name = $primary_mobile = '';
                $national_id = "Payer Phone: $payer_mobile";
            }

            $loan_id = $q['loan_id'];
            if ($loan_id > 0) {
                $l = loan_obj($loan_id);
                $loan_balance_ = money($l['loan_balance']);
                $next_due = $l['next_due_date'];
            } else {
                $loan_balance = "<i>Unspecified</i>";
                $next_due = "<i>Unspecified</i>";
            }


            if (new DateTime($date_only) != new DateTime($payment_date) || $time == '12:00AM') {
                $time = "";
            }

            $recorded_date = $payment_date;
            $date_col = '';
            if ($payment_date == '0000-00-00') {
                $date_col = "<td>N/A</td>";
            } else {
                $date_col = "<td><span>$payment_date <span class='text-blue font-400'>" . $time . "</span> <a title='Recorded on: $recorded_date'><i class='fa fa-question-circle'></i></a></span><br/> <span class=\"text-orange font-13 font-bold\">" . fancydate($recorded_date) . "</span></td>";
            }

            $rows .= "<tr>
        <td>$uid</td>
        <td><a href=\"customers?customer=$cid_en\"><span class=\"font-16\">$full_name</span></a><br/> <span class=\"text-muted font-13 font-bold\">$national_id</span>
        </td>
        <td><span class=\"text-bold text-blue font-16\">$amount</span><br><span>$branch_name_</span></td>
        <td><span>$pay_meth </span><span class='text-green font-13 text-bold'><br/> <i class='fa fa-check-circle'></i> $payment_category_name</span>
        </td>
        <td>$record_method</td>
        <td>$transaction_code</td>
        <td>$loan_id $loan_link <br/> <span class=\"text-muted font-13 font-italic\">Acc: $loan_code</span> </td>
        <td><span>$loan_balance</span><br/> <span class=\"text-muted font-13 font-italic\">Next Due: <b>$next_due</b></span></td>
        $date_col
        <td><span class=\"text-bold\">$status_name</td>
        <td><span><a href=\"?repayment=" . encurl($uid) . "\"><span class=\"fa fa-eye text-green\"></span></a></span><h4></h4></td>
    </tr>";
        }
    } else {
        $rows = $noRows;
    }
}

echo trim($rows) . "<tr style='display: none;'><td><input type='hidden' id='_alltotal_' value='$alltotal'><input type='hidden' id='_pageno_' value='$page_no'></td></tr>";

include_once("../../configs/close_connection.inc");
