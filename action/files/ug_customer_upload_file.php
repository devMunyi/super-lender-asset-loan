<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../php_functions/image_handler.php");
include_once ("../../configs/conn.inc");

$userd = session_details();

$title = sanitizeAndEscape($_POST['title'], $con);
$description = sanitizeAndEscape($_POST['description'], $con);
$category = $_POST['type_'];
$file_name = $_FILES['file_']['name'];
$file_size = $_FILES['file_']['size'];
$file_tmp = $_FILES['file_']['tmp_name'];
$make_thumbnail = $_POST['make_thumbnail'];

$reference_number = $_POST['reference_number'];
$upload_location = '../../uploads_/';

$upload_perm  = permission($userd['uid'],'o_documents',"0","create_");
if($upload_perm == 0) {
    die(errormes("You don't have permission to upload a file"));
    exit();
}

//die("Error occurred");

if((input_available($title)) == 0)
{
    die(errormes("Title is required"));
    exit();
}
if($category > 0){

}
else{
    die(errormes("Upload type is required"));
    exit();
}

$allowed_formats = fetchrow("o_customer_document_categories","uid=$category","formats");
$allowed_formats_array = explode(",", $allowed_formats);

// ensure file does not exceed 10MB
$file_size_limit = 10 * 1024 * 1024;
if($file_size > $file_size_limit){
    exit(errormes("File size should not exceed 10MB"));
}

if($file_size > 100){
    if((file_type($file_name, $allowed_formats_array)) == 0){
        die(errormes("This file format is not allowed. Only $allowed_formats "));
        exit();
    }

}
else{
    die(errormes("File not attached or has invalid size"));
    exit();
}

$result = upload_and_resize_image($file_name,$file_tmp,$upload_location);
if($result == null)
{
    echo errormes("Error uploading file, please retry");
    exit();
}


$original_upload = $result['original'];
$upload_resized = $upload = $result["resized"];

$file_name_only = pathinfo($upload, PATHINFO_FILENAME);
if($make_thumbnail == 1) {
    makeThumbnails($upload_location, $upload, 100, 100, "thumb_".$file_name_only);
}

//echo errormes(makeThumbnails($upload_location, "7UpkJa8zGa.jpg",50,50,"ddd.jpg"));


$added_by = $userd['uid'];
$added_date = $fulldate;
$tbl = $_POST['tbl'];
$rec = $_POST['rec'];
$stored_address = $upload_resized;
$status = 1;

$fds = array('title','description','category','added_by','added_date','tbl','rec','stored_address','status');
$vals = array("$title","$description","$category","$added_by","$added_date","$tbl","$rec","$stored_address","$status");
$create = addtodb('o_documents',$fds,$vals);
if($create == 1)
{
    echo sucmes('File Uploaded Successfully');
    $proceed = 1;

}
else
{
    echo errormes('Unable to Upload File'.$create);
}

// delete original_file to free up space
unlink("$upload_location/original/$original_upload");

?>
<script>
    if('<?php echo $proceed; ?>'){
        setTimeout(function (){
            reload();
        }, 400);
        upload_list('<?php echo encurl($rec); ?>','EDIT');
    }
</script>