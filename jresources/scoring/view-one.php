<?php
session_start();
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");

$iid = $_POST['iid'];
if ($iid > 0) {
    $spin_status_names = [
        1 => "Processing",
        2 => "Completed",
        3 => "Failed"
    ];

    $user_names = table_to_obj("o_users", "uid > 0", "100000", "uid", "name");
    $spin_counter = 1;
    $sp = fetchmaxid("o_spin_scoring", "uid=$iid AND record_status = 1", "uid, result, spin_type, spin_status, added_date, processed_date, added_by, doc_reference_id, score_type");

    $uid = $sp['uid'];
    $result = $sp['result'];
    $spin_type = $sp['spin_type'];
    $doc_reference_id = $sp['doc_reference_id'];
    $score_type = $sp['score_type'];
    $spin_status = $sp['spin_status'];
    $spin_status_name = $spin_status_names[$spin_status] ?? '';
    $added_date = $sp['added_date'];
    $added_date_only = date('Y-m-d', strtotime($added_date));
    $time = date('h:iA', strtotime($added_date));
    $processed_date = $sp['processed_date'];

    $added_by = $sp['added_by'];
    $added_by = $user_names[$added_by] ?? '';

    $added_date = "<span>$added_date_only</span><br/> <span class='text-orange font-13 font-bold'>" . fancydate($added_date_only) . "<br><span class='text-blue font-400'>$time</span></span>";

    if (!empty($processed_date) && $processed_date != '0000-00-00 00:00:00') {
        $time = date('h:iA', strtotime($processed_date));
        $processed_date = date('Y-m-d', strtotime($processed_date));
        $processed_date = "<span>$processed_date</span><br/> <span class='text-orange font-13 font-bold'>" . fancydate($processed_date) . "<span class='text-blue font-400'>$time</span></span>";
    } else {
        $processed_date = '';
    }

    $status_color = $spin_status == 3 ? "#ff0000" : ($spin_status == 1 ? "#ff8c00" : "#6cce05");
    $status = "<span class='label custom-color' style='background-color:$status_color'> $spin_status_name</span>";

    echo "
        <table class='table-bordered font-14 table table-hover'>
            <thead>
                <tr>
                    <th>Analysis Type</th>
                    <th>Added By</th>
                    <th>Added At</th>
                    <th>Processed At</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>$spin_type</td>
                    <td>$added_by</td>
                    <td>$added_date</td>
                    <td>$processed_date</td>
                    <td>$status</td>
                </tr>";

    // Decode JSON safely
    $scoringData = json_decode($result, true);
    $jsonData = $scoringData['json_data'] ?? [];

    // Loanable Amounts
    $loanableAmounts = [
        'General Loanable Amount' => $jsonData['old_scores']['g_score']['loanable'] ?? 'N/A',
        'Long-term Loanable Amount' => $jsonData['old_scores']['d_score']['long_term']['loanable'] ?? 'N/A',
        'Mobile Loanable Amount' => $jsonData['old_scores']['m_score']['loanable'] ?? 'N/A'
    ];

    // Risk Levels
    $riskLevels = [
        'Mobile Risk Level (M Score)' => $jsonData['old_scores']['m_score']['risk_level'] ?? 'N/A',
        'General Risk Level (G Score)' => $jsonData['old_scores']['g_score']['risk'] ?? 'N/A',
        'Mobile Risk Level (D Score)' => $jsonData['old_scores']['d_score']['mobile']['risk_level'] ?? 'N/A',
        'Long-term Risk Level (D Score)' => $jsonData['old_scores']['d_score']['long_term']['risk_level'] ?? 'N/A'
    ];

    // Financial Behavior
    $financialBehavior = [
        'Income Score' => $jsonData['old_scores']['d_score']['data']['income_matrix'] ?? 'N/A',
        'Indebtedness Score' => $jsonData['old_scores']['d_score']['data']['indebtedness_matrix'] ?? 'N/A',
        'History of Repaying Other Mobile Loans' => $jsonData['old_scores']['d_score']['data']['history_of_repaying_other_mobile_loans'] ?? 'N/A',
        'Low Activity Indicator' => $jsonData['old_scores']['d_score']['data']['low_activity'] ?? 'N/A',
        'High Betting Activity' => $jsonData['old_scores']['d_score']['data']['high_betting'] ?? 'N/A',
        'High Healthcare Expenses Indicator' => $jsonData['old_scores']['d_score']['data']['healthcare_ratio_high'] ?? 'N/A',
        'High Debt-to-Income Ratio' => $jsonData['old_scores']['d_score']['data']['debt_to_income_ratio_high'] ?? 'N/A'
    ];

    // Financial Institutions (Lenders)
    $financialInstitutions = [];
    if (isset($jsonData['last_data']['body']['mobile_mfi_trends']['MFIs'])) {
        foreach ($jsonData['last_data']['body']['mobile_mfi_trends']['MFIs'] as $mfi) {
            if (!empty($mfi['amount'])) {
                $financialInstitutions[] = [
                    'name' => trim(str_replace('-', '', $mfi['name'])),
                    'amount' => 'KES '. number_format($mfi['amount'], 2),
                    'date' => date('Y-m-d', strtotime($mfi['date']))
                ];
            }
        }
    }

    // Add Fuliza (if available)
    if (isset($jsonData['last_data']['body']['fuliza']['received']['total'])) {
        $fulizaTotal = $jsonData['last_data']['body']['fuliza']['received']['total'];
        if ($fulizaTotal > -1) {
            $financialInstitutions[] = [
                'name' => 'Safaricom Fuliza',
                'amount' => 'KES '. number_format($fulizaTotal, 2),
                'date' => ''
            ];
        }
    }

    // Add kcb_mpesa (if available)
    if (isset($jsonData['last_data']['body']['kcb_mpesa']['loan']['disburse']['last_amount'])) {
        $kcbMpesaLoanTotal = $jsonData['last_data']['body']['kcb_mpesa']['loan']['disburse']['last_amount'];
        if ($kcbMpesaLoanTotal > -1) {
            $financialInstitutions[] = [
                'name' => 'KCB Mpesa Loan',
                'amount' => 'KES '. number_format($kcbMpesaLoanTotal, 2),
                'date' => $jsonData['last_data']['body']['kcb_mpesa']['loan']['disburse']['last'] ?? 'N/A'
            ];
        }
    }

    // Add mshwari (if available)
    if (isset($jsonData['last_data']['body']['mshwari']['loan']['disburse']['last_amount'])) {
        $mshwariLoanTotal = $jsonData['last_data']['body']['mshwari']['loan']['disburse']['last_amount'];
        if ($mshwariLoanTotal > -1) {
            $financialInstitutions[] = [
                'name' => 'KCB Mpesa Loan',
                'amount' => 'KES '. number_format($mshwariLoanTotal, 2),
                'date' => $jsonData['last_data']['body']['mshwari']['loan']['disburse']['last'] ?? 'N/A'
            ];
        }
    }


    // Add hustler_fund (if available)
    if (isset($jsonData['last_data']['body']['hustler_fund']['loan']['disburse']['last_amount'])) {
        $hustlerFundLoanTotal = $jsonData['last_data']['body']['hustler_fund']['loan']['disburse']['last_amount'];
        if ($hustlerFundLoanTotal > -1) {
            $financialInstitutions[] = [
                'name' => 'KCB Mpesa Loan',
                'amount' => 'KES '. number_format($hustlerFundLoanTotal, 2),
                'date' => $jsonData['last_data']['body']['hustler_fund']['loan']['disburse']['last'] ?? 'N/A'
            ];
        }
    }

    // Render nested tables inside Bootstrap grid
    echo "<tr>
            <td colspan='5'>
                <div class='row'>";

    // Loanable Amounts
    if (!empty(array_filter($loanableAmounts))) {
        echo "<div class='col-md-4'>";
        renderTable('Loanable Amounts', $loanableAmounts);
        echo "</div>";
    }

    // Risk Levels
    if (!empty(array_filter($riskLevels))) {
        echo "<div class='col-md-3 p-2'>";
        renderTable('Risk Levels', $riskLevels);
        echo "</div>";
    }

    // Financial Behavior
    if (!empty(array_filter($financialBehavior))) {
        echo "<div class='col-md-3 p-2'>";
        renderTable('Financial Behavior', $financialBehavior);
        echo "</div>";
    }

    // Financial Institutions
    if (!empty($financialInstitutions)) {
        echo "<div class='col-md-12 p-2'>";
        echo "<table class='table table-bordered table-hover table-condensed'>
                <thead>
                    <tr><th class='font-16' colspan='3'>Financial Institutions</th></tr>
                </thead>
                <thead>
                    <tr><th>Lender</th><th>Amount</th><th>Date</th></tr>
                </thead>
                <tbody>";

        foreach ($financialInstitutions as $institution) {
            echo "<tr>
                    <td>{$institution['name']}</td>
                    <td class='text-blue font-400'>{$institution['amount']}</td>
                    <td>{$institution['date']}</td>
                  </tr>";
        }

        echo "</tbody></table>";
        echo "</div>";
    }

    echo "      </div>
            </td>
          </tr>";

    echo '</tbody></table>';
} else {
    echo errormes("Scoring Invalid");
}

// Function to render table
function renderTable($title, $data) {
    echo "<table class='table table-bordered table-hover table-condensed'>
            <thead>
                <tr><th class='font-16' colspan='2'>$title</th></tr>
            </thead>
            <thead>
                <tr><th>Metric</th><th>Value</th></tr>
            </thead>
            <tbody>";

    foreach ($data as $key => $value) {
        echo "<tr><td>{$key}</td><td class='text-blue font-400'>{$value}</td></tr>";
    }

    echo "</tbody></table>";
}

// Include close connection
include_once("../../../configs/close_connection.inc");