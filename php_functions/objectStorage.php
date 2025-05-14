<?php 

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;



function uploadFileToBucket($localFilePath) {
    global $BUCKET_NAME;

    $allowedContentTypes = [
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'csv' => 'text/csv'
    ];
 
    $fileName = basename($localFilePath);
    $ext = strtolower(pathinfo($localFilePath, PATHINFO_EXTENSION));
    $contentType = $allowedContentTypes[$ext] ?? null;

    // Validate file type
    if (!$contentType) {
        return ("Error: Invalid file type for {$fileName}. Only .png, .jpg, .jpeg, .gif, .svg, and .csv are allowed.");
    }

    // Use the createS3Client function to get the S3 client
    $s3Client = createS3Client();

    try {

        // check if the file exists
        if (!file_exists($localFilePath)) {
            return "Error: File {$localFilePath} does not exist.";
        }

        // Open the file for reading
        $fileStream = fopen($localFilePath, 'r');

        if (!$fileStream) {
            return ("Error: Could not open file {$localFilePath} for reading.");
        }

        // Upload the file to S3
        $result = $s3Client->putObject([
            'Bucket'      => $BUCKET_NAME,
            'Key'         => $fileName,
            'Body'        => $fileStream,
            'ACL'         => 'public-read', // Set ACL for public access
            'ContentType' => $contentType,
        ]);

        // echo "result ==> ".$result;

        $statusCode = trim($result['@metadata']['statusCode']);
        $uploadUrl = trim($result['ObjectURL']);

        if ($statusCode != 200) {
            return ("Error: S3 upload failed for {$fileName} with status code {$statusCode}.");
        }

        // Return the upload URL and status code
        return [
            'upload_url' => $uploadUrl,
            'status_code' => $statusCode
        ];

    } catch (S3Exception $e) {
        return ("Error uploading {$fileName} to S3: " . $e->getMessage());
    } catch (Exception $e) {
        // Catch any other general exceptions
        return ("Error processing {$fileName}: " . $e->getMessage());
    } finally {
        // Ensure the file stream is closed
        if (isset($fileStream) && is_resource($fileStream)) {
            fclose($fileStream);
        }

        // Delete the local file if it exists and was uploaded successfully
        if (isset($uploadUrl) && file_exists($localFilePath)) {
            unlink($localFilePath);
        }
    }
}


function createS3Client() {

    global $BUCKET_REGION, $BUCKET_ENDPOINT, $BUCKET_ACCESS_KEY, $BUCKET_SECRET_KEY;

    return new S3Client([
        'version' => 'latest',
        'region'  => $BUCKET_REGION,
        'endpoint' => $BUCKET_ENDPOINT,
        'use_path_style_endpoint' => false, // Configures to use subdomain/virtual calling format.
        'credentials' => [
            'key'    => $BUCKET_ACCESS_KEY,
            'secret' => $BUCKET_SECRET_KEY,
        ],
        'suppress_php_deprecation_warning' => true, // Suppress the deprecation warning
    ]);
}