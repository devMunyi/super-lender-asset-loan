<?php

include_once("php_functions/crb.utils.php");

////-------------Check if a user has permission to update a loan to tie it with viewing unallocated
$whereCondition = "";
$permi = permission($userd['uid'], 'o_incoming_payments', "0", "general_");
if ($permi == 1) {
    $whereCondition .= " AND ip.loan_id > -1";
} else {
    $whereCondition .= " AND ip.loan_id > 0";
}

$query = "SELECT ip.payment_date as PaymentDate, ip.amount as PaymentAmount, c.full_name as CustomerNames, c.national_id as NationalID, l.account_number as AccountNumber, l.loan_amount as OriginalAmount, l.final_due_date as FinalDueDate, l.loan_balance as CurrentBalance FROM o_incoming_payments ip RIGHT JOIN o_loans l ON ip.loan_id = l.uid LEFT JOIN o_customers c ON c.uid = l.customer_id WHERE ip.status = 1 AND JSON_UNQUOTE(JSON_EXTRACT(l.other_info, '$.CRB_LISTED')) = '1' AND JSON_UNQUOTE(JSON_EXTRACT(l.other_info, '$.CRB_LISTED_DATE')) >= ip.payment_date AND ip.payment_date BETWEEN '$start_date' AND '$end_date' $andbranch_pay $whereCondition";

$payments = mysqli_query($con, $query);
// {"CRB_LISTED": "1", "CRB_LISTED_DATE": "2024-05-03"}

?>

<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>Snapshot Date </th>
                <th>Client Type</th>
                <th>Surname</th>
                <th>Forename 1</th>
                <th>Forename 2</th>
                <th>Forename 3</th>
                <th>Company Name</th>
                <th>Primary Identification Document Type</th>
                <th>Primary Identification Doc Number</th>
                <th>Account Number</th>
                <th>Currency of Facility</th>
                <th>Original Amount</th>
                <th>Payment Amount</th>
                <th>Payment Date</th>
                <th>Current Balance</th>
                <th>Installments in Arrears</th>
                <th>Days in Arrears</th>
                <th>Account Status</th>
            </tr>
        </thead>
        <tbody>
            <?php

            $sum_OriginalAmount = $sum_PaymentAmount = $sum_CurrentBalance = 0;
            while ($p = mysqli_fetch_assoc($payments)) {
                $customerNames = $p['CustomerNames'] ?? '';
                $names = splitCustomerNames($customerNames);

                $firstname = $names['firstname'];
                $middleName = $names['middleName'];
                $lastname = $names['lastname'];

                $surname = $lastname ? $lastname : ($middleName ? $middleName : ($firstname ? $firstname : ''));
                $forename1 = $firstname ? $firstname : ($middleName ? $middleName : ($lastname ? $lastname : ''));
        
                
                $PaymentDate = $p['PaymentDate'] ?? '';
                $PaymentAmount = $p['PaymentAmount'] ?? 0;
                $NationalID = $p['NationalID'] ?? '';
                $AccountNumber = $p['AccountNumber'] ?? '';
                $OriginalAmount = $p['OriginalAmount'] ?? 0;
                $FinalDueDate = $p['FinalDueDate'] ?? '';
                $CurrentBalance = $p['CurrentBalance'] ?? 0;
                $DaysInArrears = datediff($date, $FinalDueDate);

                if($DaysInArrears < 0){
                    $DaysInArrears = 0;
                }

                if($DaysInArrears == 0){
                    $intallmentsInArrears = 0;
                }else{
                    $intallmentsInArrears = 30;
                }

                echo "<tr>
                        <td>$PaymentDate</td>
                        <td>A</td>
                        <td>$surname</td>
                        <td>$forename1</td>
                        <td></td>
                        <td></td>
                        <td>SimplePay Capital Limited</td>
                        <td>001</td>
                        <td>$NationalID</td>
                        <td>$AccountNumber</td>
                        <td>KES</td>
                        <td>$OriginalAmount</td>
                        <td>$PaymentAmount</td>
                        <td>$PaymentDate</td>
                        <td>$CurrentBalance</td>
                        <td>$intallmentsInArrears</td>
                        <td>$DaysInArrears</td>
                        <td>H</td>
                    </tr>";


                $sum_OriginalAmount += $OriginalAmount;
                $sum_PaymentAmount += $PaymentAmount;
                $sum_CurrentBalance += $CurrentBalance;
            }

            ?>
        </tbody>

        <tfoot>
            <tr>
                <th colspan="11"></th>
                <th><?php echo money($sum_OriginalAmount); ?></th>
                <th><?php echo money($sum_PaymentAmount); ?></th>
                <th colspan="2"></th>
                <th><?php echo money($sum_CurrentBalance); ?></th>
                <th colspan="3"></th>
            </tr>
        </tfoot>
    </table>


</div>