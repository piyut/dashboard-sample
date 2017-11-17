<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$route = new \Klein\Klein();

$route->respond('GET', '/', function(){
   return require_once 'home.html';
});
$route->respond('GET', '/rest/contacts', function(){
    $contacts = getContactList();
    return $contacts;
});
$route->dispatch();

function getContactList() {
    $call = callHttp("/api/v2.1/rest/get_user_list");
    header('Content-Type: application/json');
    echo $call;
}

function callHttp($url, $params = []){
    $base_url = 'https://' . getenv('APP_ID') . '.qiscus.com/';
    $client = new Client(['base_uri' => $base_url]);
    $httpResp = $client->request('GET', $url, [
        'multipart' => $params,
        'headers' => [
            'Accept' => 'application/json',
            'QISCUS_SDK_APP_ID' => getenv('APP_ID'),
            'QISCUS_SDK_SECRET' => getenv('SECRET_KEY')
        ]
    ]);

    return $httpResp->getBody();
}
