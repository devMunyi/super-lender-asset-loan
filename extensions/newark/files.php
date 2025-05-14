<?php

$folderPath = 'passport_photo/';


// Array of allowed file extensions
$allowedExtensions = ['jpg', 'jpeg', 'png'];

// Get the list of image files in the folder
$images = [];
foreach ($allowedExtensions as $extension) {
    $images = array_merge($images, glob($folderPath . "*.$extension"));
}

//var_dump($images);

foreach ($images as $image) {
    // Extract the file name without extension
    $imageName = pathinfo($image, PATHINFO_FILENAME);

    // Create SQL statement
    $sql = "UPDATE o_customers SET flag = 1 WHERE national_id = '$imageName' AND uid > 0";

    // Output the SQL statement
    echo $sql . ";<br>";
}