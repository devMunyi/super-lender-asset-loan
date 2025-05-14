<?php
include_once("../../configs/auth.inc");
include_once '../../configs/20200902.php';
include_once("../../php_functions/functions.php");


$db = $db_;
$_SESSION['db_name'] = $db;
include_once("../../configs/conn.inc");

$rpp = $_GET['rpp'];
$offset = $_GET['offset'];


$last = fetchrow('o_key_values',"uid=26","value_");

if($last < 100){
    die();
}

$uid = $last;

echo $rpp;
$docs = fetchtable('o_documents',"uid<$last AND added_date <= '2024-11-24 00:00:00'","uid","desc","$rpp","uid, stored_address");
while($d = mysqli_fetch_array($docs)){
    $uid = $d['uid'];
    $stored = $d['stored_address'];

    echo $uid.' '.$stored.'<br/>';


    $imageUrl = 'http://simplepay.co.ke/userdocs/'.$stored;
    downloadAndSaveImage($imageUrl);
}

$upd = updatedb('o_key_values',"value_='$uid'","uid=26");

function downloadAndSaveImage($imageUrl) {
    // Check if the folder exists, create it if not
    $folderPath = __DIR__ . '/to_resize';
    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0755, true);
    }

    // Get the image file name from the URL
    $imageName = basename($imageUrl);

    // Construct the full path to save the image
    $savePath = $folderPath . '/' . $imageName;

    // Download the image and save it
    $imageContent = file_get_contents($imageUrl);
    if ($imageContent !== false) {
        file_put_contents($savePath, $imageContent);
        echo "Image downloaded and saved to: $savePath";
    } else {
        echo "Failed to download the image.";
    }
}

// Example usage:



