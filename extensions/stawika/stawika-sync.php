<?php
//postgresql://stawikauser:PenangTrang!(*7@165.22.194.168:51963/smart_loan
$host= '165.22.194.168';
$port = '51963';
$db = 'smart_loan';
$user = 'stawikauser';
$password = 'PenangTrang!(*7'; // change to your password

$offset = $_GET['offset'];
$rpp = $_GET['rpp'];

$db_connection = pg_connect("host=$host port=$port dbname=$db user=$user password=$password");


$result = pg_query($db_connection, "SELECT \"trans_type\", trans_id, trans_time, trans_amount, business_short_code, bill_ref_number, invoice_number, msisdn, kyc_name, kyc_value, result_code, result_desc, third_party_transid, id, request_type, request_status, rcpt_id, c2b_company
FROM js_smart_revenue.mpesa_c2btransactions where id >= 0  order by id desc offset $offset limit $rpp;");

if (!$result) {
    echo "An error occurred.\n";
    exit;
}

while ($row = pg_fetch_assoc($result)) {
    $id = $row['id'];
     $trans_id = $row['trans_id'];
     $trans_time = $row['trans_time'];
     $trans_amount = $row['trans_amount'];
     $trans_amount = $row['trans_amount'];
     $bill_ref_number = $row['trans_amount'];
     $business_short_code = $row['business_short_code'];
     $third_party_transid = $row['third_party_transid'];
     $msisdn = $row['msisdn'];
     $name = $row['kyc_value'];
     $name_array = explode(' ', $name);
     $first = $name_array[0];
     $middle = $name_array[1];
     $last = $name_array[2];




       // echo "" . $id . " $trans_id<br/>";

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://143.110.148.227/lender/apis/incoming-pays.php?c=5',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS =>'{
            "TransactionType": "Pay Bill",
            "TransID": "'.$trans_id.'",
            "TransTime": "'.$trans_time.'",
            "TransAmount": "'.$trans_amount.'",
            "BusinessShortCode": "'.$business_short_code.'",
            "BillRefNumber": "'.$bill_ref_number.'",
            "InvoiceNumber": "0",
            "OrgAccountBalance": "'.$third_party_transid.'",
            "ThirdPartyTransID": "'.$third_party_transid.'",
            "MSISDN": "'.$msisdn.'",
            "FirstName": "'.$first.'",
            "MiddleName": "'.$middle.'",
            "LastName": "'.$last.'"
        }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Cookie: PHPSESSID=8p9m9c3060gjjafgoh6gb71i9v'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo $response.'<br/>';



}