<?php
function sendSMS($phone, $message, $response = null, $error = null, $hashKey = null)
{
    $from = 'Tenakata';
    $username = 'Tenakata';
    $transactionID = "00007";
    $clientid = "1062";
    $curl = curl_init();
    //    $password = "d43f6f1689fb9978e8416bce1330725d57c7cf659098135bafe5cddc6805c995aaec9771aed50dcc5dc6e78a7c6efb4f5ea30dca8c4c04c696d7011f8f825088";
    $password = "b5eb755333f8a41fd6bbf6701582f4087b7ee48287d7266268c9d1698b97a6350e09b21ab693c8b1e449be00f2a84cd
b843b5f49c4dd43b2d49ad2e0e476c1f1";
    $stopMessage = "STOP*456*9*5#";
    if (isset($hashKey)) {
        $message .= " " . $hashKey;
    }

    $message .= " $stopMessage";

    curl_setopt_array($curl, array(
        CURLOPT_PORT => "8095",
        CURLOPT_URL => "https://eclecticsgateway.ekenya.co.ke:8095/ServiceLayer/pgsms/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "{\n\t\"to\":\"$phone\",\n\t\"message\":\"$message\",\n\t\"from\":\"$from\",\n\t\"transactionID\":\"$transactionID\",\n\t\"username\":\"$username\",\n\t\"password\":\"$password\",\n\t\"clientid\":\"$clientid\"\n}",
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/json",
            "postman-token: 3a34b9f9-e3ec-75ed-07ad-bc1eab9c486f",
            "token: LVwlhYsOteZ8c9TDRjBf",
            "x-api-key: admin@123"
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);

    //  var_dump($response);
    // var_dump($err);

    curl_close($curl);

    if ($err) {
        $error = $err;
        return $err;
    } else {

        $result = json_decode($response, true);
        $balance = $result['ResultDetails']['CREDIT_BALANCE'];
        $upd = updatedb('o_summaries', "value_='$balance'", "name='SMS_BALANCE'");
        echo $upd."$upd";

        return $response;

    }
}

echo sendSMS(254716330450, "Test");