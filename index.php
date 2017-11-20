<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$route = new \Klein\Klein();

$route->respond('GET', '/', function(){
   return require_once 'home.html';
});

$route->respond('GET', '/rest/contacts', function($request){
    if(empty($request->page) || $request->page == 0 ) {
        $request->page = 1;
    }
    if(empty($request->limit) || $request->limit == 0) {
        $request->limit = 20;
    }
    $contacts = getContactList();
    return $contacts;
});
$route->respond('POST', '/rest/upload', function($request){
    if(empty($_FILES['file']['tmp_name'])){
        header('Content-Type: application/json', true, 400);
        return json_encode(['file'=>'required']);
    }
    $file = uploadPhoto($_FILES['file']['tmp_name']);
    return $file;
});
$route->dispatch();

function getContactList($page, $limit) {
    $call = callHttp("/api/v2.1/rest/get_user_list", [
        [
            'name'=> 'page',
            'contents' => $limit,
        ], [
            'name' => 'limit',
            'limit' => $limit,
        ]
    ]);
    header('Content-Type: application/json');
    echo $call;
}

function uploadPhoto($file) {
    $photo = callHttp("/api/v2/sdk/upload",[
        [
            'name' => 'file',
            'contents' => $file,
        ]
    ]);
    echo $photo;
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
