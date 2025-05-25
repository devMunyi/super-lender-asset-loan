<?php
session_start();
include_once ("../../php_functions/functions.php");
include_once ("../../configs/conn.inc");

if ($OBJECT_STORAGE_BUCKET == 1){
    include_once("../../vendor/autoload.php");
    include_once("../../php_functions/objectStorage.php");
}

/////----------Session Check
$userd = session_details();
if($userd == null){
    exit(errormes("Your session is invalid. Please re-login"));
}
/////---------End of session check
$added_by = $userd['uid'];
$name = addslashes($_POST['name']);
$description = addslashes($_POST['description']);
$category = $_POST['category'];
$buying_price = $_POST['buying_price'];
$selling_price = $_POST['selling_price'];
$added_date = $fulldate; // gotten from conn.inc
$photo = "";
$make_thumbnail = 1;

$file_name = $_FILES['image_']['name'];
$file_size = $_FILES['image_']['size'];
$file_tmp = $_FILES['image_']['tmp_name'];
$stock = $_POST['stock'];

//echo "FILE NAME => $file_name <br>";
//echo "FILE SIZE => $file_size <br>";
//echo "FILE TMP => $file_tmp <br>";

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

if($stock < 0){
    echo errormes("Invalid stock entry");
    die();
}

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
}

$stored_address = $upload;

////----Check the optional file resize flag
if ($resize_photos == 1) {
    $imagePath = "../../assets-upload/$stored_address";
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
            $thumbPath = "../../assets-upload/thumb_$stored_address";
            $resp = uploadFileToBucket($thumbPath);
        }

        // update stored address to take full path
        $stored_address = $UPLOAD_BASE_URL . $stored_address;
    }
    //// ---- End of check if OBJECT_STORAGE_BUCKET is set 
}

/// ----End of check file resize

///////////------------------Save
$fds = array('name','description', 'category_', 'added_by', 'added_date', 'buying_price','selling_price', 'photo', 'status', 'stock');
$vals = array("$name", "$description", $category, $added_by, "$added_date", $buying_price, $selling_price, $upload, $status, $stock);

$create = addtodb('o_assets', $fds, $vals);
if($create == 1){
    echo sucmes('Asset added successfully.');
    $last_asset = fetchmax('o_assets',"photo=\"$upload\"","uid","uid");
    $asset_id = $last_asset['uid'];

    $event = "Asset added by ".$userd['name']."(".$userd['uid'].")";
    store_event('o_assets', $asset_id,"$event");
    $proceed = 1;
}
else{
    echo errormes('Unable to create asset'.$create);
}

?>


<script>
    if('<?php echo $proceed ?>'){
        setTimeout(function () {
            gotourl('assets?cat=asset&asset=<?php echo encurl($asset_id); ?>');
        }, 1500);

    }
</script>