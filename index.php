<?php
define("CONSUMER_KEY", "");
define("CONSUMER_SECRET", "");
define("OAUTH2_TOKEN_URL", "");
define("EMAIL", "");
define("PASSWORD", "");

// Enable error display
ini_set("display_errors", "1");
error_reporting(E_ALL);

// Include libraries
require_once("./vendor/autoload.php");

// Get gateway access token
$data = [
    "grant_type" => "client_credentials"
];
$options = [
    "http" => [
        "method" => "POST",
        "header" => "Content-Type: application/x-www-form-urlencoded\r\n" .
            "Authorization: Basic " . base64_encode(CONSUMER_KEY . ":" . CONSUMER_SECRET) . "\r\n",
        "content" => http_build_query($data)
    ]
];
$context  = stream_context_create($options);
$response = file_get_contents(OAUTH2_TOKEN_URL, false, $context);
$data = json_decode($response, true);

if (!isset($data["access_token"])) {
    http_response_code(500);
    exit;
}

// Inject gateway access token to SDK
$config = Swagger\Client\Configuration::getDefaultConfiguration()->setAccessToken($data["access_token"]);

// Init SDK clients
$userApi = new Swagger\Client\Api\UserApi(
    new GuzzleHttp\Client(),
    $config
);
$devicesApi = new Swagger\Client\Api\DevicesApi(
    new GuzzleHttp\Client(),
    $config
);

// Get API access token
$data = [
    "email" => EMAIL,
    "password" => PASSWORD
];
$body = new \Swagger\Client\Model\LoginAPI($data);

$result = $userApi->loginUser($body);

define("ACCESS_TOKEN", $result->getObject()->getToken()->getValue());

// Get devices list
$devices = $devicesApi->listAllDevices(ACCESS_TOKEN);

// Print devices list
var_dump($devices);
