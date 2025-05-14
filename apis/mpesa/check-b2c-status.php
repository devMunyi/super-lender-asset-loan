<?php

// enable error reporting
error_reporting(E_ALL);

include_once("../../configs/conn.inc");
include_once("../../php_functions/functions.php");
include_once("../../php_functions/mpesa.php");

try {

    $maxDaysAgo = datesub($date, 0, 0, 3);

    echo "maxDaysAgo: $maxDaysAgo <br/>";
    $mpesa_configs = fetchonerow('o_mpesa_configs', "uid=1", "property_value, initiator_name, security_credential, enc_token, enc_token_key");

    if (count($mpesaB2CStatusCheckLoanProducts) > 0) {
        $targetLoanProductsString = implode(",", $mpesaB2CStatusCheckLoanProducts);
        $pendingLoansUid = table_to_array(
            "o_loans",
            "given_date >= '$maxDaysAgo' AND disburse_state = 'NONE' AND disbursed = 0 AND status = 2 AND product_id IN ($targetLoanProductsString)",
            "5",
            "uid",
            "uid",
            "asc"
        );
    } else {
        exit("No target loan products found");
    }


    echo("Pending Loans: " . count($pendingLoansUid) . "<br/>");

    if (count($pendingLoansUid) > 0) {

        $pendingLoansList = implode(",", $pendingLoansUid);

        echo "Pending Loans List: $pendingLoansList <br/>";

        $successB2CMessage = "Accept the service request successfully";
        $linkedEvents = fetchtable("o_events", "fld in  ($pendingLoansList) AND event_details LIKE '%$successB2CMessage%'", "uid", "asc", "50", "uid, fld, event_details, event_date");

        echo "Linked Events: " . mysqli_num_rows($linkedEvents) . "<br/>";

        while ($le = mysqli_fetch_array($linkedEvents)) {
            $event_id = $le['uid'] ?? 0;
            $loan_id = $le['fld'] ?? 0;
            $event_details = $le['event_details'] ?? '';
            $event_date = $le['event_date'] ?? '';


            if ($event_date && strtotime($fulldate) > strtotime($event_date)) {
                $diff = strtotime($fulldate) - strtotime($event_date);
                $minutes = intval($diff / 60);

                // if greater than 5 minutes do something
                if ($minutes > 5) {
                    $OriginalConversationID = extractUUIDv4FromString($event_details);
                    if ($OriginalConversationID != null) {

                        $result = checkMpesaB2CTnxStatus($mpesa_configs, $loan_id, $OriginalConversationID);


                        echo "Result: " . json_encode($result) . "<br/>";

                        $responseCode = $result['ResponseCode'] ?? '';
                        $responseDescription = $result['ResponseDescription'] ?? '';

                        if($responseCode == '0'){
                            $event_details = "Mpesa B2C status queried via API for loan $loan_id";
                        }else {
                            $event_details = "Mpesa B2C status query via API failed with message $responseDescription, responseCode: $responseCode";
                        }
                        
                        store_event('o_loans', $loan_id, $event_details);
                    } else {
                        continue;
                    }
                } else {
                    echo "Minutes is less than 5 <br/>";
                }
            } else {
                echo "No event date or the event date is not in the past <br/>";
            }

            echo "Event ID: $event_id, Loan ID: $loan_id, Event Details: $event_details, Event Date: $event_date <br/>";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br/>";
}
