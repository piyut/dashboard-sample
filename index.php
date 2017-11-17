<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();


$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', 'homepage');
    $r->addRoute('GET', '/contacts', 'contacts');
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        switch ($handler) {
            case 'homepage':
                require_once 'home.html';
                break;
            case 'contacts':
                getContactList();
                break;
        }
        break;
}

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
