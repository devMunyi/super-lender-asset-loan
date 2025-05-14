<?php
//== This script is meant for a product with addon_id = 9, which is the yearly insurance fee.
//== $customerID, $total_upfront and $has_archive will be available in the context of the script.
$charge_insurance_fee = false;
$insurance_addon_id = 9;
$product_uids = table_to_array("o_product_addons", "addon_id = $insurance_addon_id", "1000", "product_id");
$product_list = implode(",", $product_uids);

$recent_platinum_loan_charged_insurance_sql = "
    SELECT l.uid, l.given_date 
    FROM o_loans l 
    LEFT JOIN o_loan_addons la ON la.loan_id = l.uid 
    WHERE l.customer_id = $customerID 
      AND l.disbursed = 1 
      AND l.product_id IN ($product_list) 
      AND la.addon_id = $insurance_addon_id 
      AND la.status = 1
    ORDER BY l.uid DESC 
    LIMIT 1";

$today = new DateTime();

// === 1️⃣ Check Current Database ===
$result = mysqli_query($con, $recent_platinum_loan_charged_insurance_sql);

$current_result_count = mysqli_num_rows($result);
if ($current_result_count > 0) {
    $loan = mysqli_fetch_assoc($result);
    $given_date = new DateTime($loan['given_date']);
    $interval = $today->diff($given_date);
    $yearDifference = $interval->y + ($interval->m / 12) + ($interval->d / 365);

    // echo "Year Difference (Current DB): $yearDifference <br>";
    $charge_insurance_fee = $yearDifference >= 1;
} else {
    // === 2️⃣ Check Archive Database if Needed === 
    if ($has_archive == 1 && empty($charge_insurance_fee)) {
        $result_archive = mysqli_query($con1, $recent_platinum_loan_charged_insurance_sql);

        $archive_result_count = mysqli_num_rows($result_archive);
        if ($archive_result_count > 0) {
            $loan = mysqli_fetch_assoc($result_archive);
            $given_date = new DateTime($loan['given_date']);
            $interval = $today->diff($given_date);
            $yearDifference = $interval->y + ($interval->m / 12) + ($interval->d / 365);

            // echo "Year Difference (Archive DB): $yearDifference <br>";
            $charge_insurance_fee_archive = $yearDifference >= 1;
        }else{
            $charge_insurance_fee = true;
        }
    }
}

// === 3️⃣ Update Total Upfront if Fee is Charge is true ===
if ($charge_insurance_fee) {
    $insurance_fee = fetchrow('o_addons', "uid=$insurance_addon_id", "amount");
    $total_upfront += $insurance_fee;
}
