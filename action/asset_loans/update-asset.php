<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

/////----------Session Check
$userd = session_details();
if($userd == null){
    die(errormes("Your session is invalid. Please re-login"));
}
/////---------End of session check
$asset_id_ = $_POST['asset_id_'];
$asset_id = decurl($asset_id_);
$added_by = $userd['uid'];
$name = addslashes($_POST['name']);
$description = addslashes($_POST['description']);
$category = $_POST['category'];
$buying_price = $_POST['buying_price'];
$selling_price = $_POST['selling_price'];
$stock = $_POST['stock'];
$photo = $_POST['existing_image_'];

$file_name = $_FILES['image_']['name'];
$file_size = $_FILES['image_']['size'];
$file_tmp = $_FILES['image_']['tmp_name'];
$make_thumbnail = 1;


$upload_location = '../../assets-upload/';

$status = $_POST['status'];

////////////////validation

if(input_available($name) == 0){
    echo errormes("Name is required");
    die();
}elseif((input_length($name, 3)) == 0){
    echo errormes("Name is too short");
    die();
}

if(input_available($description) == 0){
    echo errormes("Description is required");
    die();
}elseif((input_length($description, 10)) == 0){
    echo errormes("Description is too short");
    die();
}

if($category > 0){}
else{
    echo errormes("Please select category");
    die();
}

if(input_available($buying_price) == 0){
    echo errormes("Buying price is required");
    die();
}

if(input_available($selling_price) == 0){
    echo errormes("Selling price is required");
    die();
}

if(intval($stock) < 0){
    echo errormes("Invalid stock entry");
    die();
}

$allowed_formats = fetchrow("o_customer_document_categories","uid=1","formats");
$allowed_formats_array = explode(",", $allowed_formats);

$andphoto = "";
if($file_size > 100) {
    $upload = upload_file($file_name, $file_tmp, $upload_location);
    if ($upload === 0) {
        echo errormes("Error uploading file, please retry");
        exit();
    }
    $file_name_only = pathinfo($upload, PATHINFO_FILENAME);
    if ($make_thumbnail == 1) {
        makeThumbnails($upload_location, $upload, 200, 200, "thumb_" . $file_name_only);
    }

    $andphoto = ",photo = \"$upload\"";
}

///////////------------------ update
$fds  = "name = \"$name\", description = \"$description\", category_ = $category, buying_price = $buying_price, selling_price = $selling_price $andphoto , status = $status";
$update = updatedb('o_assets', $fds, "uid=$asset_id"); 
if($update == 1){
    echo sucmes('Asset updated successfully.');
    $event = "Asset updated by ".$userd['name']."(".$userd['uid'].")";
    store_event('o_assets', $asset_id,"$event");
    $proceed = 1;
}
else{
    echo errormes('Unable to update asset'.$update);
}

?>


<script>
    if('<?php echo $proceed ?>'){
        setTimeout(function () {
            gotourl('assets.php?cat=asset&asset=<?php echo $asset_id_; ?>');
        }, 1500);

    }
</script>