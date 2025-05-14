<?php
session_start();
include_once("../../vendor/autoload.php");
include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
include_once("../../php_functions/spinMobileUtil.php");
include_once("../../configs/spin-mobile-config.php");

$userd = session_details();
$customer_id = intval($_POST['customer_id_'] ?? 0);
$document_type = $score_type = trim($_POST['document_type']  ?? '');
$bank_code = trim($_POST['bank_code'] ?? '');
$decrypter = trim($_POST['decrypter'] ?? '');
$remoteIdentifier = trim($_POST['remoteIdentifier'] ?? '');

$file_name = $_FILES['file_']['name'];
$file_size = $_FILES['file_']['size'];
$file_tmp = $_FILES['file_']['tmp_name'];
$upload_dir = '../../mpesa_statements/';

// check if directory permissions are set


$upload_perm  = permission($userd['uid'], 'o_documents', "0", "create_");
if ($upload_perm == 0) {
    exit(errormes("You don't have permission to upload a file"));
}

if ($customer_id == 0) {
    exit(errormes("Customer ID is required"));
} else {

    // get national id as remote identifier
    $remoteIdentifier = fetchrow("o_customers", "uid=$customer_id", "national_id");

    if (empty($remoteIdentifier)) {
        exit(errormes("Customer not found"));
    }
}

if (empty($document_type)) {
    exit(errormes("Document type is required"));
}

if (empty($bank_code)) {
    exit(errormes("Bank code is required"));
}

if (empty($decrypter)) {
    $decrypter = '';
    // exit(errormes("Decrypter is required"));
}

$allowed_formats = 'pdf';
$allowed_formats_array = explode(",", $allowed_formats);

if ($file_size > 0) {
    if ((file_type($file_name, $allowed_formats_array)) == 0) {
        exit(errormes("This file format is not allowed. Only $allowed_formats "));
    }
} else {
    exit(errormes("File not attached or has invalid size"));
}

$upload_name = upload_file($file_name, $file_tmp, $upload_dir);

if (empty($upload_name)) {
    exit(errormes("Error uploading file, please retry"));
}

$documentPath = "$upload_dir/$upload_name";

//==== Generating Access Token
try {
    $authorizationToken = getAccessTokenSm();
} catch (Exception $e) {
    unlink($documentPath);
    exit(errormes("Error: " . $e->getMessage()));
}


// ==== Uploading Document
try {
    $resp = uploadDocumentSm(
        $document_type,
        $documentPath,
        $remoteIdentifier,
        $authorizationToken,
        $bank_code,
        $decrypter
    );
} catch (Exception $e) {
    unlink($documentPath);
    exit(errormes("Error: " . $e->getMessage()));
}


$doc_reference_id =  $resp['data']['id'] ?? null;
if (empty($doc_reference_id) || !isset($resp['code']) || $resp['code'] != '100.000.000') {
    $message = $resp['message'] ?? 'Error uploading document!';
    unlink($documentPath);
    exit(errormes($message));
}


$added_date = $fulldate;
$added_by = $userd['uid'];
$spin_type = 'INDIVIDUAL'; // => Individual Document


$fds = array('customer_id', 'doc', 'decrypter', 'doc_reference_id', 'spin_type', 'score_type', 'added_date', 'added_by');
$vals = array($customer_id, $upload_name, "$decrypter", $doc_reference_id, "$spin_type", "$score_type", $added_date, $added_by);

// ==== Adding Reference to Database
try{
    $create = addtodb('o_spin_scoring', $fds, $vals);
    if ($create == 1) {
        echo sucmes('File Uploaded Successfully');
        $proceed = 1;
    } else {
        unlink($documentPath);
        echo errormes('Unable to Upload File' . $create);
    }
}catch (Exception $e) {
    unlink($documentPath);
    exit(errormes("Error: " . $e->getMessage()));
}

?>
<!-- <script>
    if ('<?php echo $proceed; ?>') {
        setTimeout(function() {
            reload();
        }, 1000);
        upload_list('<?php echo encurl($rec); ?>', 'EDIT');
    }
</script> -->