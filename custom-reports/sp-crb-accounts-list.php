<?php

include_once("php_functions/crb.utils.php");

$loan_detail_query = "SELECT l.customer_id, l.uid as loan_id FROM o_loans l WHERE l.disbursed = 1 AND l.paid = 0 AND l.loan_balance >= 30000 AND (
        JSON_UNQUOTE(JSON_EXTRACT(other_info, '$.CRB_LISTED')) != '1' OR JSON_EXTRACT(other_info, '$.CRB_LISTED') IS NULL) AND final_due_date BETWEEN '$start_date' AND '$end_date' $andbranch_loan";

$loan_details = mysqli_query($con, $loan_detail_query);

$loan_ids = [];
$customer_ids = [];
while ($lds = mysqli_fetch_array($loan_details)) {
    $loan_id = intval($lds['loan_id'] ?? 0);
    $customer_id = intval($lds['customer_id']  ?? 0);

    if ($loan_id > 0) {
        array_push($loan_ids, $loan_id);
    }

    if ($customer_id > 0) {
        array_push($customer_ids, $customer_id);
    }
}


//=== implode the array to a string
$loan_ids_list  =  implode(",", $loan_ids);
$customer_ids_list  =  implode(",", $customer_ids);


//=== get customer latest payments
$customer_payments_query = "SELECT loan_id, payment_date, amount FROM o_incoming_payments WHERE loan_id IN ($loan_ids_list) AND status = 1 ORDER BY payment_date ASC";

// echo "customer_payments_query is: " . $customer_payments_query . "<br>";

$customer_payments = mysqli_query($con, $customer_payments_query);
$lastet_payments = []; // expected output: [loan_id => [payment_date, amount], ...]

while ($payment = mysqli_fetch_array($customer_payments)) {
    $loan_id = intval($payment['loan_id'] ?? 0);
    $payment_date = $payment['payment_date'] ?? '';
    $amount = floatval($payment['amount'] ?? 0);

    if ($loan_id > 0) {
        // if key exists I want it overwritten
        $lastet_payments[$loan_id] = [$payment_date, $amount];
    }
}


//==  customer contacts query;
$customer_contacts_query = "SELECT customer_id, value FROM o_customer_contacts WHERE customer_id IN ($customer_ids_list) AND contact_type = 1 AND `status` = 1";


// echo  "customer_contacts_query is: " . $customer_contacts_query . "<br>";

$customer_contacts = mysqli_query($con, $customer_contacts_query);
$customer_phones = []; // expected output: [customer_id => [phone_number, ...], ...]

while ($contact = mysqli_fetch_array($customer_contacts)) {
    $customer_id = intval($contact['customer_id'] ?? 0);
    $phone_number = $contact['value'] ?? '';

    if ($customer_id > 0) {
        // should not overwrite the key instead push the value
        if (array_key_exists($customer_id, $customer_phones)) {
            array_push($customer_phones[$customer_id], $phone_number);
        } else {
            $customer_phones[$customer_id] = [$phone_number];
        }
    }
}


//=== loans to be listed
$crbAccounts_query = "SELECT l.uid as LoanCode, c.full_name as CustomerNames, l.customer_id, c.dob as DateOfBirth, c.primary_mobile as ClientNumber, l.account_number as AccountNumber, c.gender as Gender, c.national_id as PrimaryIdentificationDocNumber, b.name as LocationTown, c.sec_data as SecData, l.given_date as DateAccountOpened, l.final_due_date as InstalmentDueDate, l.disbursed_amount as OriginalAmount, l.loan_balance as LoanBalance FROM o_loans l RIGHT JOIN o_customers c on l.customer_id = c.uid LEFT JOIN o_branches b on c.branch = b.uid WHERE l.uid IN ($loan_ids_list) AND c.uid IN ($customer_ids_list) ORDER BY c.uid DESC LIMIT 1000000";

// echo "crbAccounts_query is: " . $crbAccounts_query . "<br>";

$crbAccounts = mysqli_query($con, $crbAccounts_query);

?>


<div class="col-sm-12">
    <table id="example2" class="table table-condensed table-striped table-bordered">
        <thead>
            <tr>
                <th>Surname</th>
                <th>Forename 1</th>
                <th>Forename 2</th>
                <th>Forename 3</th>
                <th>Trading As</th>
                <th>Date of Birth</th>
                <th>Client Number</th>
                <th>Account Number</th>
                <th>Old Account Number</th>
                <th>Gender</th>
                <th>Nationality</th>
                <th>Marital Status</th>
                <th>Primary Identification Document Type</th>
                <th>Primary Identification Doc Number</th>
                <th>Secondary Identification Document Type</th>
                <th>Secondary Identification Document Number</th>
                <th>Other Identification Document Type</th>
                <th>Other Identification Document Number</th>
                <th>Passport Country Code</th>
                <th>Mobile Telephone Number</th>
                <th>Home Telephone Number</th>
                <th>Work Telephone Number</th>
                <th>Postal Address 1</th>
                <th>Postal Address 2</th>
                <th>Postal Location Town</th>
                <th>Postal Location Country</th>
                <th>Post code</th>
                <th>Physical Address 1</th>
                <th>Physical Address 2</th>
                <th>Plot Number</th>
                <th>Location Town</th>
                <th>Location Country</th>
                <th>Type of Residency</th>
                <th>PIN Number</th>
                <th>Consumer E-Mail</th>
                <th>Employer Name</th>
                <th>Occupational Industry Type</th>
                <th>Employment Date</th>
                <th>Employment type</th>
                <th>Income Amount</th>
                <th>Lenders Registered Name</th>
                <th>Lenders Trading Name</th>
                <th>Lenders Branch Name</th>
                <th>Lenders Branch Code</th>
                <th>Account Joint/Single Indicator</th>
                <th>Account Product Type</th>
                <th>Date Account Opened</th>
                <th>Instalment Due Date</th>
                <th>Original Amount</th>
                <th>Currency of Facility</th>
                <th>Current Balance In Kenya Shillings</th>
                <th>Current Balance</th>
                <th>Overdue Balance</th>
                <th>Overdue Date</th>
                <th>Number of Days In Arrears</th>
                <th>Number of Instalments In Arrears</th>
                <th>Prudential Risk Classification</th>
                <th>Account Status</th>
                <th>Account Status Date</th>
                <th>Account Closure Reason</th>
                <th>Repayment Period</th>
                <th>Deferred Payment Date</th>
                <th>Deferred Payment Amount</th>
                <th>Payment Frequency</th>
                <th>Disbursement Date</th>
                <th>Next Instalment Amount</th>
                <th>Date of Latest Payment</th>
                <th>Last Payment Amount</th>
                <th>Type of Security</th>
                <th>Group ID</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($account = mysqli_fetch_assoc($crbAccounts)) {
                $customerNames = $account['CustomerNames'] ?? '';
                $names = splitCustomerNames($customerNames);

                $firstname = $names['firstname'];
                $middleName = $names['middleName'];
                $lastname = $names['lastname'];

                $surname = $lastname ? $lastname : ($middleName ? $middleName : ($firstname ? $firstname : ''));
                $forename1 = $firstname ? $firstname : ($middleName ? $middleName : ($lastname ? $lastname : ''));
                $forename2 =  '';
                $forename3 = '';
                $tradingAs = $account['LoanCode'] ?? ''; // we use loan code for reference
                $dateOfBirth = crbDateFormatter($account['DateOfBirth'] ?? '');
                $clientNumber = $account['ClientNumber'] ?? '';
                $accountNumber = $account['AccountNumber'] ?? '';
                $oldAccountNumber = '';
                $gender = $account['Gender'] ?? '';
                $nationality = 'KE';
                $maritalStatus = '';
                $primaryIdentificationDocumentType = '001';
                $primaryIdentificationDocNumber = $account['PrimaryIdentificationDocNumber'];
                $secondaryIdentificationDocumentType = '';
                $secondaryIdentificationDocumentNumber = '';
                $otherIdentificationDocumentType = '';
                $otherIdentificationDocumentNumber = '';
                $passportCountryCode = '';
                $mobileTelephoneNumber = $clientNumber;
                $customer_id = $account['customer_id'] ?? 0;
                $homeTelephoneNumber = $customer_phones[$customer_id][0] ?? '';
                $workTelephoneNumber = $clientNumber;
                $postalAddress1 = '';
                $postalAddress2 = '';
                $postalLocationTown = $account['LocationTown'] ?? '';
                $postalLocationCountry = 'KE';
                $postCode = '';
                $physicalAddress1 = '';
                $physicalAddress2 = '';
                $plotNumber = '';
                $locationTown = $postalLocationTown;
                $locationCountry = 'KE';
                $typeOfResidency = '';
                $pinNumber = '';
                $consumerEmail = '';
                $employerName = '';
                $occupationalIndustryType = '006';
                $employmentDate = '';
                $employmentType = '';
                $incomeAmount = crbMoneyFormatter(calculateIncomeAmount($account));
                $lendersRegisteredName = 'SimplePay Capital Limited';
                $lendersTradingName = 'SimplePay Capital Limited';
                $lendersBranchName = 'HQ';
                $lendersBranchCode = 'M01955';
                $accountJointSingleIndicator = 'S';
                $accountProductType = 'D';
                $dateAccountOpened = crbDateFormatter($account['DateAccountOpened'] ?? '');
                $final_due_date = crbDateFormatter($account['InstalmentDueDate'] ?? '');
                $instalmentDueDate = crbDateFormatter($final_due_date);
                $originalAmount = crbMoneyFormatter($account['OriginalAmount']);
                $currencyOfFacility = 'KES';
                $currentBalanceInKenyaShillings = crbMoneyFormatter($account['LoanBalance'] ?? '');
                $currentBalance = $currentBalanceInKenyaShillings;
                $overdueBalance = $currentBalanceInKenyaShillings;
                $overdueDate = $instalmentDueDate;
                $numberOfDaysInArrears = datediff($final_due_date, $date);
                $numberOfInstalmentsInArrears = '30';
                $prudentialRiskClassification = 'A';
                $accountStatus = 'J';
                $accountStatusDate = $instalmentDueDate;
                $accountClosureReason = $account['AccountClosureReason'] ?? '';
                $repaymentPeriod = '1';
                $deferredPaymentDate = '';
                $deferredPaymentAmount = '';
                $paymentFrequency = 'M';
                $disbursementDate = $dateAccountOpened;
                $nextInstalmentAmount = $currentBalance;
                $dateOfLatestPayment = crbDateFormatter($lastet_payments[$account['LoanCode']][0] ?? '');
                $lastPaymentAmount = crbMoneyFormatter($lastet_payments[$account['LoanCode']][1] ?? '');
                $typeOfSecurity =  'S';
                $groupId = '.';


                echo "<tr><td>$surname</td><td>$forename1</td><td>$forename2</td><td>$forename3</td><td>$tradingAs</td><td>$dateOfBirth</td><td>$clientNumber</td><td>$accountNumber</td><td>$oldAccountNumber</td><td>$gender</td><td>$nationality</td><td>$maritalStatus</td><td>$primaryIdentificationDocumentType</td><td>$primaryIdentificationDocNumber</td><td>$secondaryIdentificationDocumentType</td><td>$secondaryIdentificationDocumentNumber</td><td>$otherIdentificationDocumentType</td><td>$otherIdentificationDocumentNumber</td><td>$passportCountryCode</td><td>$mobileTelephoneNumber</td><td>$homeTelephoneNumber</td><td>$workTelephoneNumber</td><td>$postalAddress1</td><td>$postalAddress2</td><td>$postalLocationTown</td><td>$postalLocationCountry</td><td>$postCode</td><td>$physicalAddress1</td><td>$physicalAddress2</td><td>$plotNumber</td><td>$locationTown</td><td>$locationCountry</td><td>$typeOfResidency</td><td>$pinNumber</td><td>$consumerEmail</td><td>$employerName</td><td>$occupationalIndustryType</td><td>$employmentDate</td><td>$employmentType</td><td>$incomeAmount</td><td>$lendersRegisteredName</td><td>$lendersTradingName</td><td>$lendersBranchName</td><td>$lendersBranchCode</td><td>$accountJointSingleIndicator</td><td>$accountProductType</td><td>$dateAccountOpened</td><td>$instalmentDueDate</td><td>$originalAmount</td><td>$currencyOfFacility</td><td>$currentBalanceInKenyaShillings</td><td>$currentBalance</td><td>$overdueBalance</td><td>$overdueDate</td><td>$numberOfDaysInArrears</td><td>$numberOfInstalmentsInArrears</td><td>$prudentialRiskClassification</td><td>$accountStatus</td><td>$accountStatusDate</td><td>$accountClosureReason</td><td>$repaymentPeriod</td><td>$deferredPaymentDate</td><td>$deferredPaymentAmount</td><td>$paymentFrequency</td><td>$disbursementDate</td><td>$nextInstalmentAmount</td><td>$dateOfLatestPayment</td><td>$lastPaymentAmount</td><td>$typeOfSecurity</td><td>$groupId</td></tr>";
            }
            ?>
        </tbody>

        <!-- <tfoot>
            <tr>
                <th>#</th>
                <th>--</th>
                <th>--</th>
                <th>--</th>
                <th>--</th>
                <th>--</th>
            </tr>
        </tfoot> -->
    </table>


</div>