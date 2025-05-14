<?php 

$expected_http_method = 'POST';
include_once("../../../vendor/autoload.php");
include_once ("../../../configs/allowed-ips-or-origins.php");
include_once("../../../configs/conn.inc");
include_once("../../../configs/jwt.php");
include_once("../../../php_functions/jwtAuthUtils.php");
include_once("../../../php_functions/jwtAuthenticator.php");
include_once("../../../php_functions/functions.php");

$data = json_decode(file_get_contents('php://input'), true);
$customer_id = intval($data["customer_id"]);

if ($customer_id > 0) {
}else{
    sendApiResponse(400, "Customer ID Missing!");
}

// get customer details
$cust_det = fetchonerow('o_customers', "uid = $customer_id", "uid, primary_product, loan_limit, status, primary_mobile, branch");
$product_id = $cust_det['primary_product'];
$loan_limit = $cust_det['loan_limit'];
$status = $cust_det['status'];
$cust_id = $cust_det['uid'];
$primary_mobile = $cust_det['primary_mobile'];
$cust_branch = $cust_det['branch'];
$week_ago = datesub($date, 0, 0, 2000);

if (empty($cust_det)) {
    $http_status_code = 404;
    $message = "Customer Not Found.";

    store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
    sendApiResponse($http_status_code, "$message");
    
}


//////-------------Check if customer is required to pay any amount before getting a loan
/// --------------Check all upfront fees
$upfronts = fetchtable('o_addons', "paid_upfront = 1", "uid", "asc", 10, "uid, amount, amount_type, applicable_loan");
$total_upfront = 0;

while ($up = mysqli_fetch_array($upfronts)) {
    $aid = $up['uid'];
    $product_addon = fetchrow('o_product_addons', "addon_id='$aid' AND status=1 AND product_id='$product_id'", "uid");
    if ($product_addon > 0) {
        $upfront_addon = $up['uid'];
        $applicable_loan = $up['applicable_loan'];
        $amount = $up['amount'];
        $amount_type = $up['amount_type'];

        if ($amount_type == 'FIXED_VALUE') {
            $a_amount = $amount;
        } else {
            $a_amount = $loan_amount * ($amount / 100);
        }


        if ($applicable_loan == 0) {
            $total_upfront += $a_amount;
        } else {
            $total_loans_taken = countotal_withlimit('o_loans',"customer_id = $customer_id AND disbursed = 1","uid","1000");
            if ($total_loans_taken < $applicable_loan) {
                $total_upfront += $a_amount;
            }
        }
    }
}
 

$paid = totaltable('o_incoming_payments', "(mobile_number='$primary_mobile' OR customer_id = '$customer_id') AND loan_id=0 AND payment_category IN (0, 1, 2, 4) AND status=1 AND payment_date >= '$week_ago'", "amount");
$total_repaid = totaltable('o_loans', "disbursed=1 AND paid=1 AND status!=0 AND customer_id='$customer_id'", "total_repaid");
$total_repayable = totaltable('o_loans', "disbursed=1 AND paid=1 AND status!=0 AND customer_id='$customer_id'", "total_repayable_amount");
$overpayments = false_zero($total_repaid - $total_repayable);

$http_status_code = 200;
$status = "OK";
$message = "";
$balance = 0.00;

$paid_plus_overpayments = $paid + $overpayments;
if ($paid_plus_overpayments < $total_upfront) {
    $balance = $total_upfront - $paid_plus_overpayments;

    ////-----Tenakata Temporaly solution
    $total_temp = totaltable('o_incoming_payments', "(mobile_number='$primary_mobile' OR customer_id = '$customer_id') AND loan_id = 1 AND payment_category IN (0, 1, 2, 4) AND status = 1 AND payment_date >= '$week_ago'", "amount");

    if ($total_temp >= 500) {
        $upd = updatedb('o_incoming_payments', "loan_id = 2", "(mobile_number='$primary_mobile' OR customer_id = '$customer_id') AND loan_id = 1 AND payment_category IN (0, 1, 2, 4) AND status = 1 AND payment_date >= '$week_ago'");
    } else {
        ///
        ///------End of Tenakata Temporaly
        $message = "";
        $http_status_code = 402;
    }
}

store_event_return_void('o_customers', $customer_id, "$message, $http_status_code");
sendApiResponse($http_status_code, "$message", $status, $balance);

?>