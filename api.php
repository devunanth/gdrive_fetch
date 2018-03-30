<?php

$authkey = $_POST['authkey'];
//file id on gdrive
$id = $_POST['url'];;
// MIME type of file.
$content_type = $_POST['mime'];
// Name of content on S3
$content_title = $_POST['name'];;
$fp = fopen (dirname(__FILE__) . '/'.$content_title, 'w+');
 $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/drive/v3/files/'.$id.'?alt=media');

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
   "Authorization: Bearer ".$authkey;
    ));

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 curl_setopt($ch, CURLOPT_FILE, $fp); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
// get curl response
curl_exec($ch); 
curl_close($ch);
fclose($fp);
 // file_get_contents(filename)
// echo aws($content,$content_type,$content_title);

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\S3\Model\MultipartUpload\UploadBuilder;

require 'vendor/autoload.php';


 function aws($content,$content_type,$content_title){

/// AWS API keys
$aws_access_key_id = 'AKIAJLNYOZTHT24WM44Q';
$aws_secret_access_key = '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';

// Bucket
$bucket_name = 'bahetihawa';

// AWS region and Host Name (Host names are different for each AWS region)
// As an example these are set to us-east-1 (US Standard)
$aws_region = 'us-east-2';
$host_name = $bucket_name . '.s3.amazonaws.com';

// AWS file permissions
$content_acl = 'authenticated-read';

// Service name for S3
$aws_service_name = 's3';

// UTC timestamp and date
$timestamp = gmdate('Ymd\THis\Z');
$date = gmdate('Ymd');

// HTTP request headers as key & value

$request_headers = array();
$request_headers['Content-Type'] = $content_type;
$request_headers['Date'] = $timestamp;
$request_headers['Host'] = $host_name;
$request_headers['x-amz-acl'] = $content_acl;
$request_headers['x-amz-content-sha256'] = hash('sha256', $content);
// Sort it in ascending order
ksort($request_headers);

// Canonical headers
$canonical_headers = [];
foreach($request_headers as $key => $value) {
	$canonical_headers[] = strtolower($key) . ":" . $value;
}
$canonical_headers = implode("\n", $canonical_headers);

// Signed headers
$signed_headers = [];
foreach($request_headers as $key => $value) {
	$signed_headers[] = strtolower($key);
}
$signed_headers = implode(";", $signed_headers);

// Cannonical request 
$canonical_request = [];
$canonical_request[] = "PUT";
$canonical_request[] = "/" . $content_title;
$canonical_request[] = "";
$canonical_request[] = $canonical_headers;
$canonical_request[] = "";
$canonical_request[] = $signed_headers;
$canonical_request[] = hash('sha256', $content);
$canonical_request = implode("\n", $canonical_request);
$hashed_canonical_request = hash('sha256', $canonical_request);

// AWS Scope
$scope = [];
$scope[] = $date;
$scope[] = $aws_region;
$scope[] = $aws_service_name;
$scope[] = "aws4_request";

// String to sign
$string_to_sign = [];
$string_to_sign[] = "AWS4-HMAC-SHA256"; 
$string_to_sign[] = $timestamp; 
$string_to_sign[] = implode('/', $scope);
$string_to_sign[] = $hashed_canonical_request;
$string_to_sign = implode("\n", $string_to_sign);

// Signing key
$kSecret = 'AWS4' . $aws_secret_access_key;
$kDate = hash_hmac('sha256', $date, $kSecret, true);
$kRegion = hash_hmac('sha256', $aws_region, $kDate, true);
$kService = hash_hmac('sha256', $aws_service_name, $kRegion, true);
$kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);

// Signature
$signature = hash_hmac('sha256', $string_to_sign, $kSigning);

// Authorization
$authorization = [
	'Credential=' . $aws_access_key_id . '/' . implode('/', $scope),
	'SignedHeaders=' . $signed_headers,
	'Signature=' . $signature
];
$authorization = 'AWS4-HMAC-SHA256' . ' ' . implode( ',', $authorization);

// Curl headers
$curl_headers = [ 'Authorization: ' . $authorization ];
foreach($request_headers as $key => $value) {
	$curl_headers[] = $key . ": " . $value;
}

$url = 'https://' . $host_name . '/' . $content_title;
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if($http_code != 200) 
	exit('Error : Failed to upload');

return $http_code;
}

