<?php
session_start();

include_once("../../php_functions/functions.php");
include_once("../../configs/conn.inc");
include_once("../../configs/");


if ($OBJECT_STORAGE_BUCKET == 1){
    include_once("../../vendor/autoload.php");
    include_once("../../php_functions/objectStorage.php");
}


$userd = session_details();

$title = addslashes($_POST['title']);
$description = addslashes($_POST['description']);
$category = $_POST['type_'];
$rec = $_POST['rec'];
$file_name = $_FILES['file_']['name'];
$file_size = $_FILES['file_']['size'];
$file_tmp = $_FILES['file_']['tmp_name'];
$make_thumbnail = $_POST['make_thumbnail'];

///---If file type is pdf, don't make thumbnail
if(strtolower(substr($file_name, -4)) === '.pdf')  //////Is pdf
{
  $make_thumbnail = 0;
}

$reference_number = $_POST['reference_number'];
$upload_location = '../../uploads_/';

$upload_perm  = permission($userd['uid'], 'o_documents', "0", "create_");
if ($upload_perm == 0) {
    exit(errormes("You don't have permission to upload a file"));
}


if ((input_available($title)) == 0) {
    die(errormes("Title is required"));

    exit();
}
if ($category > 0) {
} else {
    die(errormes("Upload type is required"));
    exit();
}

$allowed_formats = fetchrow("o_customer_document_categories", "uid=$category", "formats");
$allowed_formats_array = explode(",", $allowed_formats);

if ($file_size > 0) {
    if ((file_type($file_name, $allowed_formats_array)) == 0) {
        die(errormes("This file format is not allowed. Only $allowed_formats "));
        exit();
    }
} else {
    die(errormes("File not attached or has invalid size"));
    exit();
}

$upload = upload_file($file_name, $file_tmp, $upload_location);
if ($upload === 0) {
    echo errormes("Error uploading file, please retry");
    exit();
}
if($category == 1){
    $make_thumbnail = 1;  /////-----If its a profile picture, make a thumbnail always
}

$file_name_only = pathinfo($upload, PATHINFO_FILENAME);
if ($make_thumbnail == 1) {
    makeThumbnails($upload_location, $upload, 100, 100, "thumb_" . $file_name_only);
}

// echo errormes(makeThumbnails($upload_location, "7UpkJa8zGa.jpg",50,50,"ddd.jpg"));


$added_by = $userd['uid'];
$added_date = $fulldate;
$tbl = $_POST['tbl'];
// $rec = $_POST['rec'];
$stored_address = $upload;
$status = 1;


////----Check the optional file resize flag
if ($resize_photos == 1) {
    $imagePath = "../../uploads_/$stored_address";
    $res = resizeAndCompressImage($imagePath);
    if ($res == 1) {
        // echo sucmes("Resized");
    }


    //// ---- Check if OBJECT_STORAGE_BUCKET is set 
    if ($OBJECT_STORAGE_BUCKET == 1) {

        // upload resized image
        $resp = uploadFileToBucket($imagePath);

        // upload thumbnail
        if($make_thumbnail == 1){
            $thumbPath = "../../uploads_/thumb_$stored_address";
            $resp = uploadFileToBucket($thumbPath);
        }

        // update stored address to take full path
        $stored_address = $UPLOAD_BASE_URL . $stored_address;
    }
    //// ---- End of check if OBJECT_STORAGE_BUCKET is set 
}

/// ----End of check file resize


$fds = array('title', 'description', 'category', 'added_by', 'added_date', 'tbl', 'rec', 'stored_address', 'status');
$vals = array("$title", "$description", "$category", "$added_by", "$added_date", "$tbl", "$rec", "$stored_address", "$status");
$create = addtodb('o_documents', $fds, $vals);
if ($create == 1) {
    echo sucmes('File Uploaded Successfully');
    $proceed = 1;
} else {
    echo errormes('Unable to Upload File' . $create);
}

?>
<script>
    if ('<?php echo $proceed; ?>') {
        setTimeout(function() {
            reload();
        }, 1000);
        upload_list('<?php echo encurl($rec); ?>', 'EDIT');
    }
</script>