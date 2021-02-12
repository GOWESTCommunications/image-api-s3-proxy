<?php
$baseDir = dirname(__DIR__, 1);
require_once $baseDir . '/vendor/autoload.php';
require_once $baseDir . '/config.php';

$config['s3']['options']['credentials'] = new Aws\Credentials\Credentials($config['s3']['key'], $config['s3']['secret']);

$s3Client = new Aws\S3\S3Client($config['s3']['options']);


$requestUri = $_SERVER['REQUEST_URI'];
$requestUriParts = explode('?', $requestUri);
$requestUri = $requestUriParts[0];
$imageConfPart = $requestUriParts[1];
$s3OriginalUri = $requestUri;
$s3NewPath = preg_replace('/^\//', '', $requestUri);

$imageConf = trim(openssl_decrypt($imageConfPart, 'AES-128-CBC', $config['encryption']['key'], OPENSSL_ZERO_PADDING, $config['encryption']['iv']));


$requestUriCheckParts = explode('/', $requestUri);
$requestUriCheck = $requestUriCheckParts[2];

if(md5($imageConf) != $requestUriCheck) {
    http_response_code(400);
    exit;
}

$ch = curl_init('https://api.pixpic.at/v1/image');
curl_setopt($ch, CURLOPT_POSTFIELDS, $imageConf);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER,[
    'Content-Type:application/json',
    'x-api-key:' . $config['api_key'],
]);

$apiResponse = curl_exec($ch);
$httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if($httpResponseCode != 200) {
    http_response_code($httpResponseCode);
    exit;
}

$file_array = explode("\r\n\r\n", $apiResponse, 2);
$header_array = explode("\r\n", $file_array[0]);

if(isJson($file_array[1])) {
    http_response_code(400);
    exit;
}

foreach ($header_array as $header_value) {
    $header_pieces = explode(': ', $header_value);
    if (count($header_pieces) == 2) {
        $headers[strtolower($header_pieces[0])] = trim($header_pieces[1]);
    }
}

try {
    $s3Client->putObject([
        'Bucket' => $config['s3']['bucket'],
        'Key'    => $s3NewPath,
        'Body'   => $file_array[1],
        'ACL'    => 'public-read',
        'Metadata'   => $headers,
        'ContentType' => $headers['content-type'] ? $headers['content-type'] : '',

    ]);
} catch (Aws\S3\Exception\S3Exception $e) {
    echo "There was an error uploading the file.\n";
}

function isJson($string) {
    json_decode($string);
    return (json_last_error() == JSON_ERROR_NONE);
}
