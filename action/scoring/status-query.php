<?php 

session_start();
include_once("../../vendor/autoload.php");
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
include_once("../../php_functions/spinMobileUtil.php");
include_once("../../configs/spin-mobile-config.php");


$data = json_decode(file_get_contents('php://input'), true);
$score_type = trim($data['score_type'] ?? "");
$ref_id = trim($data['ref_id'] ?? '');

if (empty($score_type)) {
    exit(errormes("Score type is required!"));
}

if (empty($ref_id)) {
    exit(errormes("Reference ID is required!"));
}

try{

    $decrypter = fetchrow("o_spin_scoring", "doc_reference_id='$ref_id'", "decrypter");

    $resp = statusQuerySm($score_type, $ref_id, $decrypter);
    // using sample response as array:
    $state_name = $resp['data']['state_name'] ?? '';

    if($state_name == 'Failed'){
        updatedb("o_spin_scoring", "spin_status=3", "doc_reference_id='$ref_id'");
        exit(errormes("Document processing failed! Try uploading new document."));
    }

    if($state_name == 'Completed'){
        exit(sucmes("Document processing completed successfully."));
    }

    if($state_name == 'Processing'){
        exit(sucmes("Document processing is still in progress."));
    }

    // echo json_encode($resp);

    // could be just a message key value
    $message = $state_name ?? $resp['message'] ?? 'Something went wrong!';
    exit(errormes($message));

}catch(Exception $e){
    exit(errormes($e->getMessage()));
}
