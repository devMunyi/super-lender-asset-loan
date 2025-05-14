<?php
//== This script checks if a loan applied qualifies to be marked as a platinum loan
// Conditions: if disbursed amount is greater than 21,000 and happens to be first loan for the customer
//== $customerID, $loanID, $currentLoanAmount and $has_archive will be available in the context of the script.
//==  Hook it to Simplified produtct

//== will only be executed if $currentLoanAmount >= 21000
if ($currentLoanAmount >= 21000) {
    $total_loans_so_far = intval(countotal_withlimit('o_loans', "customer_id = $customerID AND disbursed = 1", "uid", "10"));
    if ($has_archive == 1 && empty($total_loans_so_far)) {
        $total_loans_so_far = intval(countotal_archive('o_loans', "customer_id = $customerID AND disbursed = 1", "uid", "10"));
    }

    if ($total_loans_so_far <= 0) {
        // mark as platinum loan
        $mark_platinum_query = "UPDATE o_loans 
                        SET other_info = JSON_SET(
                            IFNULL(other_info, '{}'), 
                            '$.IS_PLATINUM', '1' 
                        ) WHERE uid = $loanID";

        // execute query
        mysqli_query($con, $mark_platinum_query);
    }
}
