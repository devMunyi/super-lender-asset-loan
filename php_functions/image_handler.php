<?php 
function upload_and_resize_image($fname, $tmpName, $upload_dir, $w = 1920, $h = 1080)
{

    // $payload = null;
    // $message = "";

    try {
        $ext = pathinfo($fname, PATHINFO_EXTENSION);
        $nfileName = generateRandomString(25) . '.' . "$ext";
        $resizedFilePath = $upload_dir . $nfileName;
        $originalFilePath = "$upload_dir/original/$fname";

        // Move the file to the upload directory
        if (move_uploaded_file($tmpName, $originalFilePath)) { 
        }else {
            return null;
            // throw new Exception("Failed to move the uploaded file.");
        }

        // Resize image if it's a JPEG or PNG
        if ($ext === 'jpg' || $ext === 'jpeg' || $ext === 'png') {
            // Create a new image from the uploaded file
            $sourceImage = null;
            if ($ext === 'jpg' || $ext === 'jpeg') {
                if (imagecreatefromjpeg($originalFilePath)) {
                    $sourceImage = imagecreatefromjpeg($originalFilePath);
                } elseif (imagecreatefrompng($originalFilePath)) {
                    $sourceImage = imagecreatefrompng($originalFilePath);
                }
            } elseif ($ext === 'png') {

                if (imagecreatefrompng($originalFilePath)) {
                    $sourceImage = imagecreatefrompng($originalFilePath);
                } elseif (imagecreatefromjpeg($originalFilePath)) {
                    $sourceImage = imagecreatefromjpeg($originalFilePath);
                }
            }

            if (!$sourceImage) {
                return null;
                // throw new Exception("Failed to create the source image.");
            }

            // Get the dimensions of the original image
            $sourceWidth = imagesx($sourceImage);
            $sourceHeight = imagesy($sourceImage);

            // Calculate the proportional resize dimensions
            $aspectRatio = $sourceWidth / $sourceHeight;
            if ($sourceWidth > $w || $sourceHeight > $h) {
                if ($aspectRatio > 1) {
                    $newWidth = min($w, $sourceWidth);
                    $newHeight = $newWidth / $aspectRatio;
                } else {
                    $newHeight = min($h, $sourceHeight);
                    $newWidth = $newHeight * $aspectRatio;
                }

                // Create a new blank image with the desired dimensions
                $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

                if (!$resizedImage) {
                    return null;
                    // throw new Exception("Failed to create the resized image.");
                }

                // Resize the original image to the new dimensions
                if (!imagecopyresampled(
                    $resizedImage, // Destination image resource
                    $sourceImage, // Source image resource
                    0,
                    0, // Destination x, y coordinates
                    0,
                    0, // Source x, y coordinates
                    $newWidth,
                    $newHeight, // Destination width, height
                    $sourceWidth,
                    $sourceHeight // Source width, height
                )) {
                    return null;
                    // throw new Exception("Failed to resize the image.");
                }

                // Save the resized image to the file path
                if ($ext === 'jpg' || $ext === 'jpeg') {
                    if (!imagejpeg($resizedImage, $resizedFilePath, 90)) { // Adjust the quality (90) as needed
                        return null;
                        // throw new Exception("Failed to save the resized image.");
                    }
                } elseif ($ext === 'png') {
                    if (!imagepng($resizedImage, $resizedFilePath, 9)) { // Adjust the compression level (9) as needed
                        return null;
                        // throw new Exception("Failed to save the resized image.");
                    }
                }

                // Free up memory
                imagedestroy($resizedImage);
            }else {
                // have similar copy of image for resize version
                copy($originalFilePath, $resizedFilePath);
            }

            // Free up memory
            imagedestroy($sourceImage);
        }

        // Check if both files exist and return the file names if successful, otherwise return null
        if (file_exists($resizedFilePath) && file_exists($originalFilePath)) {
            return array(
                'resized' => $nfileName,
                'original' => $fname
            );
        } else {
            return null;
            // throw new Exception("One or both files do not exist.");
        }
    } catch (Exception $e) {
        // Handle exceptions here, you can log the error or return an error message as needed.

        echo $e->getMessage();
        return null;
    }
}