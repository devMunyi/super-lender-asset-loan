<?php


if ($product_id == 2) {
    ///-----Verify inua biashara
    $supplier = fetchrow('o_group_members', "customer_id='$customer_id' AND status=1", "group_id");

    $cust_det = fetchonerow('o_customers', "uid = $customer_id", "uid, primary_product, loan_limit, status, primary_mobile, branch, full_name, national_id");
    $product_id = $primary_product = $cust_det['primary_product'];
    $loan_limit = $cust_det['loan_limit'];
    $status = $cust_det['status'];
    $cust_id = $cust_det['uid'];
    $primary_mobile = $cust_det['primary_mobile'];
    $cust_branch = $cust_det['branch'];

    ////-----------------------------------------Process B2B Loans
    ///
    if ($supplier > 0 && $product_id == 2) {
        $sup = fetchonerow('o_customer_groups', "uid='$supplier'", "uid, group_phone, till, group_name");
        include_once('tenakata_sms.php');
        $customer_id = $cust_id;
        $customer_name = $cust_det['full_name'];
        $customer_group_id = $supplier;
        $disbursed_amount = $loan_amount;
        $national_id = $cust_det['national_id'];
        $id_protected = hideMiddleDigits($national_id);
        $distributor_phone = $sup['group_phone'];
        $customer_phone = $primary_mobile;
        ///----Loan has been created but won't be disbursed

        ///--------Queue the B2B request

        $till = $sup['till'];

        if ($till > 1000) {
            $fds = array('loan_id', 'amount', 'added_date', 'trials', 'short_code', 'status');
            $vals = array("$loan_id", "$disbursed_amount", "$fulldate", "0", "$till", '1');
            $queue = addtodb('o_b2b_queues', $fds, $vals);
        } else {
            $event_details = "B2B transaction not scheduled because the Till number is unavailable";
            $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
            $vals = array("o_loans", $loan_id, "$event_details", "$fulldate", 0, 1);
            $event_logged = addtodb('o_events', $fds, $vals);
        }

        ///
        /// -------Send Messages
        $distributor_message = "Kindly Provide $customer_name ID $id_protected with stock worth $loan_amount. OrderNo. $loan_id Thank you";
        $customer_message = "Good news! Cash worth $loan_amount disbursed to distributor. Your goods will be delivered. OrderNo. $loan_id Thank you";


        // $automatic_disburse = fetchrow('o_loan_products', "uid=$product_id", "automatic_disburse");
        $res = sendSMS($distributor_phone, $distributor_message) . "<br/>";
        $res = sendSMS($customer_phone, $customer_message) . "<br/>";

        b2b(3033631, $till, round($disbursed_amount, 0));

        ///
        /// -------Mark Loan as disbursed
        $mark = updatedb('o_loans', "disbursed=1, status=3", "uid='$loan_id'");
        if ($mark == 1) {
            $event_details2 = "Loan marked as disbursed by system awaiting B2B process";
            $fds = array('tbl', 'fld', 'event_details', 'event_date', 'event_by', 'status');
            $vals = array("o_loans", $loan_id, "$event_details2", "$added_date", 0, 1);
            $event_logged = addtodb('o_events', $fds, $vals);
        }
    }
    ///
    /// ------------------------------------------End of process B2B loans


}
