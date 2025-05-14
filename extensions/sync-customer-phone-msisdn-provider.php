<?php 

include_once ("../configs/conn.inc");
include_once ("../php_functions/functions.php");

$providers = table_to_obj('o_telecomms', "uid > 0", "1000", "uid", "name");
$customers = fetchtable2('o_customers', 'uid > 0', 'uid', 'DESC', 'primary_mobile, uid');

$updated_count = $skipped_count = 0;
while($c = mysqli_fetch_assoc($customers)){
    $uid = $c['uid'];
    $msisdn = $c['primary_mobile'] ;
    $provider_uid = getMSISDNProviderUID($msisdn);
    $provider_name = $providers[$provider_uid] ?? 'UNKNOWN';

    $updated = updatedb('o_customers', "phone_number_provider = $provider_uid", "uid = $uid");
    if($updated = 1){
        echo "UPDATED CUSTOMER UID => $uid, PHONE => $msisdn <br>";
        $updated_count++;
    }else {
        echo "SKIPPED CUSTOMER UID => $uid, PHONE => $msisdn <br>";
        $skipped_count++;
    }
}

echo "UPDATED COUNT: $updated_count, SKIPPED COUNT: $skipped_count <br>";

include_once("../configs/close_connection.inc");
?>