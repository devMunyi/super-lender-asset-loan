<?php
include_once("../configs/secure.php");
include_once("../php_functions/functions.php");

$rpp = $_GET['rpp'];
$offset = $_GET['offset'];
$resize = $_GET['resize'];

$last = fetchrow('o_key_values',"uid=27","value_");
$l = fetchrow('o_key_values',"uid=26","value_");

echo $last."";

if($last >= 19505003){
    die("Last reached");
}

$uid = $last;
$docs = fetchtable('o_documents',"uid >= $last","uid","asc","$offset,$rpp","uid, stored_address, category");
while($d = mysqli_fetch_array($docs)){
    $uid = $d['uid'];
    $stored = $d['stored_address'];
    $category = $d['category'];

    echo $uid.' '.$stored." Cat: $category".'<br/>';

    if($resize == 1) {
        $imagePath = "../uploads_/$stored";
        echo "(".resizeAndCompressImage($imagePath).")";
    }
}
if($resize == 1) {
    $upd = updatedb('o_key_values', "value_='$uid'", "uid=27");
}

function resizeAndCompressImage2($imagePath, $maxWidth = 720, $quality = 90) {
    // Get the original dimensions of the image
    list($originalWidth, $originalHeight) = getimagesize($imagePath);
    $pathInfo = pathinfo($imagePath);
    $imageExtension = strtolower($pathInfo['extension']);
    if($imageExtension == 'jpg' || $imageExtension == 'jpeg'){

    }
    else{
        return "Wrong extension";
    }

    // Check if the image needs resizing
    if($originalWidth > $originalHeight ){

        if($originalHeight < 720){
            return false;
        }
        else{
            $ar = $originalHeight / $originalWidth;
            $newHeight = 720;
            $newWidth = round(720 / $ar);
        }

    }
    elseif ($originalHeight > $originalWidth){
        if($originalWidth < 720){
            return false;
        }
        else{
            $ar =  $originalWidth / $originalHeight;
            $newWidth = 720;
            $newHeight = round(720 / $ar);
        }
    }
    elseif ($originalHeight == $originalWidth){
        if($originalHeight < 720){
            return false;
        }
        else{
            $ar = 1;
        }
    }
    else{
        return 0;
    }

    $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

    // Load the original image
    $originalImage = imagecreatefromjpeg($imagePath); // Change this to the appropriate function for your image type

    // Resize the image
    imagecopyresampled($resizedImage, $originalImage, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);

    // Save the resized and compressed image, overwriting the original
    imagejpeg($resizedImage, $imagePath, $quality); // Change this to the appropriate function for your image type

    // Free up memory
    imagedestroy($originalImage);
    return imagedestroy($resizedImage);

}

/*
// Get the list of files in the directory
$directory = "to_resize";
$files = scandir($directory);

// Loop through each file in the directory
foreach ($files as $file) {
    // Exclude "." and ".." (current directory and parent directory)
    if ($file != "." && $file != "..") {
        // Full path to the file
        $filePath = $directory . '/' . $file;

        // Check if it's a file (not a subdirectory)
        if (is_file($filePath)) {
            // Output the filename only
           // echo "File: " . basename($file) . "\n";
            $imagePath = "to_resize/".basename($file);
            echo "(".resizeAndCompressImage($imagePath).")";
        }
    }
}
*/

//$imagePath = '4ZYVw3OrkoJSH3VtR6pxgjdAO123456111.jpg';
//echo resizeAndCompressImage($imagePath);