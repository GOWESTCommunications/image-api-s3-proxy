<?php 
$config = [
    'api_key' => 'XXXXXXXXXXXXXXXXXXXXXXXX', // GO.WEST Image API Key
    's3' => [
        'bucket' => 's3-bucket', // S3 Bucket Key
        'key' => 'XXXXXXXXXXXXXXXXXXXXXXXX',
        'secret' => 'XXXXXXXXXXXXXXXXXXXXXXXX',
        'options' => [
            'region' => 'eu-central-1',
            'version' => '2006-03-01',
            'signature_version' => 'v4',
        ],
    ],
    'encryption' => [
        'key' => hex2bin("aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa"), // 32 charakter hex string - has to match nuxt application setting
        'iv' =>  hex2bin("aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa"), // 32 charakter hex string - has to match nuxt application setting
    ],
];