<?php
/*
This are reusable functions that are used in various parts of the system but not widely used. We have separated them
from main functions to reduce load time
*/


function loan_addons_array($loan_array)
{

    $loan_interest_array = array();
    $loan_other_charges_array = array();

    $addons_array = array();
    $loan_list = implode(',', $loan_array);
    $interest_addons = table_to_array('o_addons', "addon_category='INTEREST'", "100", "uid");
    $other_addons = table_to_array('o_addons', "addon_category!='INTEREST'", "100", "uid");
    $o_loan_addons = fetchtable('o_loan_addons', "status=1 AND loan_id in ($loan_list)", "uid", "asc", "1000000000", "loan_id, addon_id, addon_amount");
    while ($a = mysqli_fetch_array($o_loan_addons)) {
        $loan_id = $a['loan_id'];
        $addon_id = $a['addon_id'];
        $addon_amount = $a['addon_amount'];

        if (in_array($addon_id, $interest_addons)) {
            $loan_interest_array = obj_add($loan_interest_array, $loan_id, $addon_amount);
        }
        if (in_array($addon_id, $other_addons)) {
            $loan_other_charges_array = obj_add($loan_other_charges_array, $loan_id, $addon_amount);
        }


    }

    $addons_array[0] = $loan_interest_array;
    $addons_array[1] = $loan_other_charges_array;

    return $addons_array;


}

function phoneNumberMatch($pattern, $phoneNumber) {
    // Remove any non-numeric characters from the phone number
    $cleanPhoneNumber = preg_replace('/\D+/', '', $phoneNumber);

    // Escape the pattern to be used in regex, replacing asterisks with regex wildcard .
    $escapedPattern = preg_quote($pattern, '/');
    $regexPattern = str_replace('\*', '.', $escapedPattern);

    // Use regex to check if the clean phone number matches the pattern
    if (preg_match('/^' . $regexPattern . '$/', $cleanPhoneNumber)) {
        return 1;
    } else {
        return 0;
    }
}

function send_mail($fromemail,$toemail,$heading,$body)    ////send html email
{

    $message = '<html><body>';
    $message .= "<h3>$heading</h3>";
    $message.=$body;
    $message .= '</body></html>';

    $headers = "From: $fromemail" . "\r\n" .
        "Reply-To: $fromemail";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";


    $sm=mail($toemail, $heading, $message, $headers);
    return $sm;
}

function generateSQLQuery($userQuestion)
{
    global $api_key;

    // Initialize a cURL session
    $ch = curl_init();

    // Set the cURL options
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);

    // Set the headers
    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $api_key,
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Set the request body
    $data = [
        'model' => 'gpt-4o',
        'messages' => [
            ['role' => 'user', 'content' => ''.$userQuestion.'']
        ]
    ];
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        echo 'cURL error: ' . curl_error($ch);
    }

    // Check for HTTP errors
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode >= 400) {
        echo 'HTTP error: ' . $httpCode . "\nResponse: " . $response;
    }

    // Close the cURL session
    curl_close($ch);

    // Decode and return the response
    $decodedResponse = json_decode($response, true);
    if (isset($decodedResponse['error'])) {
        echo 'API error: ' . $decodedResponse['error']['message'];
    } else {
        return $decodedResponse;
    }
}

function isSelectQueryOnly($query) {
    // Remove all whitespaces from the beginning and end of the query
    $trimmedQuery = trim($query);

    // Regular expression to check if the query starts with "SELECT" and contains nothing else
    $pattern = '/^SELECT\b/i';

    // Check if the query matches the pattern and doesn't contain unwanted keywords
    if (preg_match($pattern, $trimmedQuery) &&
        stripos($trimmedQuery, 'UPDATE') === false &&
        stripos($trimmedQuery, 'INSERT') === false &&
        stripos($trimmedQuery, 'DELETE') === false &&
        stripos($trimmedQuery, 'DROP') === false &&
        stripos($trimmedQuery, 'ALTER') === false) {
        return true;
    }

    return false;
}

function ensureKeys(array $arr, array $keys, $defaultValue = 0) {
    foreach ($keys as $key) {
        if (!array_key_exists($key, $arr)) {
            $arr[$key] = $defaultValue;
        }
    }
    return $arr;
}

function shortenNumber($n) {
    // Check for trillions
    if ($n >= 1000000000000) {
        return intval($n / 1000000000000) . 'T';
    }
    // Check for billions
    elseif ($n >= 1000000000) {
        return intval($n / 1000000000) . 'B';
    }
    // Check for millions
    elseif ($n >= 1000000) {
        return intval($n / 1000000) . 'M';
    }
    // Check for thousands
    elseif ($n >= 1000) {
        return intval($n / 1000) . 'k';
    }
    // Return the number as a string if less than 1000
    return (string)$n;

}
function truncateToTwoDecimals($number) {
    return floor($number * 100) / 100;
}