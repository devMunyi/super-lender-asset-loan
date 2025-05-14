<?php
function fetchAndProcessData($url, $x)
{
    // Fetch the data using cURL with SSL verification disabled
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false, // Disable SSL cert verification
        CURLOPT_SSL_VERIFYHOST => false, // Disable host verification
    ));
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        die("Error fetching data from URL: " . curl_error($ch));
    }

    curl_close($ch);

    // Match JSON objects from the response
    preg_match_all('/\{.*?\}/s', $response, $matches);

    if (empty($matches[0])) {
        die("No valid JSON objects found");
    }

    // Decode each JSON string into an associative array
    $objects = array_map('json_decode', $matches[0]);

    // Get the last $x objects
    $selectedObjects = array_slice($objects, -$x);

    // Assign each object to a variable
    foreach ($selectedObjects as $index => $obj) {
        ${"object_" . ($index + 1)} = $obj;
        print_r(json_encode($obj)) . '<br/>'; // For debugging, you can remove this

        $datax = json_encode($obj);
        ///-----Send to API
        $redirect_url = "https://pembeni.supersystems.co.ke/lender/apis/incoming-pays";

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "$redirect_url",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $datax,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Cookie: PHPSESSID=sqiuiucom3r6rrs1boq4rkhl4v'
            ),
        ));

        $result_ = curl_exec($curl);
        echo $result_;
        curl_close($curl);
    }

    return $selectedObjects;
}

// Example usage
$url = "https://mfs.pembenicash.co.ke/deni/lipadeni/madeni/pay/pembenic/ltd/callbacks/log.txt";
$x = 3;
$results = fetchAndProcessData($url, $x);
