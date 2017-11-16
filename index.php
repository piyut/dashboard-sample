<?php

include __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;

$app_id = 'sampleapp-65ghcsaysse';
$secret_key = 'dc0c7e608d9a23c3c8012c6c8572e788';
$base_url = 'https://' . $app_id . '.qiscus.com/';

$client = new Client(['base_uri' => $base_url]);
$router = new RouteCollector();
$router->get('/', function (){
    require_once "home.html";
});
$router->get('/rest/contact_list', function () use($client, $app_id, $secret_key){
    $httpResp = $client->request('GET', '/api/v2.1/rest/get_user_list', [
        'multipart' => [],
        'headers' => [
            'Accept' => 'application/json',
            'QISCUS_SDK_APP_ID' => $app_id,
            'QISCUS_SDK_SECRET' => $secret_key
        ]
    ]);
    header('Content-Type: application/json');
    return $httpResp->getBody();
});


$dispatcher =  new Dispatcher($router->getData());
$response = $dispatcher->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Print out the value returned from the dispatched function
echo $response;