<?php 
function send_sms_interactive_v2($mobile_number, $message, $linkId = 0, $sms_settings = [])
{
    try{
        $curl = curl_init();

        $bulk_code = $sms_settings['property_value'] ?? '';
        $username = $sms_settings['short_code_username'] ?? '';
        $apiKey = $sms_settings['aft_2way_key'] ?? '';
        $keyword = $sms_settings['aft_2way_keyword'] ?? '';
    
    
        if(empty($bulk_code) || empty($username) || empty($apiKey) || empty($keyword)){
            return "SMS settings not configured";
        }
    
        if (input_available($keyword) == 0) {
            $keyword = $username;
        }
    
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.africastalking.com/version1/messaging',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array('username' => '' . $username . '', 'to' => '' . $mobile_number . '', 'message' => '' . $message . '', 'from' => '' . $bulk_code . '', 'bulkSMSMode ' => '0', 'keyword' => '' . $keyword . '', 'linkId' => '' . $linkId . ''),
            CURLOPT_HTTPHEADER => array(
                'Apikey: ' . $apiKey . ''
            ),
        ));
    
        $response = curl_exec($curl);
    
        curl_close($curl);
    
        return $response;
    }catch(Exception $e){
        return $e->getMessage();
    }finally{
        // close curl
        curl_close($curl);
    }


}