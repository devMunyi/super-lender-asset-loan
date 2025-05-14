<?php 

function crbMoneyFormatter($amount)
{
    if (empty($amount)) {
        return strval(000);
    }
    return strval($amount * 100);
}

function crbDateFormatter($date)
{
    // Check if the input is a non-empty string
    if (!is_string($date) || trim($date) === '') {
        return '';
    }

    // Format the date string (YYYY-MM-DD) to YYYYMMDD
    return str_replace('-', '', $date);
}

function calculateIncomeAmount($account)
{
    // Decode SecData JSON into an associative array

    // first check if the key is set
    if (!isset($account['SecData'])) {
        return 0;
    }

    $secData = json_decode($account['SecData'], true);

    $incomeAmount = 0;

    // Check for "26" and calculate incomeAmount
    if ($secData && isset($secData["26"])) {
        $incomeAmount = (float)$secData["26"] * 4;
    }

    // If incomeAmount is empty, check for "24" and calculate
    if (empty($incomeAmount)) {
        if ($secData && isset($secData["24"])) {
            $incomeAmount = (float)$secData["24"] * 4;
        }
    }

    // Ensure incomeAmount is a float
    $incomeAmount = (float)$incomeAmount;

    // Validate incomeAmount against OriginalAmount
    if (
        $incomeAmount == 0 ||
        $incomeAmount < $account['OriginalAmount'] ||
        is_nan($incomeAmount)
    ) {
        $incomeAmount = $account['OriginalAmount'] * 2;
    }

    return $incomeAmount;
}

function splitCustomerNames($fullname)
{
    $customer_names_arr = explode(" ", $fullname);
    return [
        'firstname' => $customer_names_arr[0] ?? null,
        'middleName' => $customer_names_arr[1] ?? null,
        'lastname' => $customer_names_arr[2] ?? null,
    ];
}

?>
