<?php
require 'vendor/autoload.php';
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$route = new \Klein\Klein();
header("Access-Control-Allow-Origin: *");
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
    $contacts = getContactList($request->page, $request->limit);
    return $contacts;
});

$route->respond('POST', '/api/upload', function($request){
    if(empty($_FILES['file']['tmp_name'])){
        header('Content-Type: application/json', true, 400);
        return json_encode(['file'=>'required']);
    }
    if (empty($request->token)) {
        header('Content-Type: application/json', true, 400);
        return json_encode(['token'=>'required']);
    }
    $handle = fopen($_FILES["file"]["tmp_name"], 'r');
    try {
        header('Content-Type: application/json', true, 200);
        $file = uploadPhoto($handle, $request->token, $_FILES['file']['name']);
        return $file;
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});
$route->dispatch();

function getContactList($page, $limit) {
    $call = callHttp("/api/v2.1/rest/get_user_list", 'GET', [
        [
            'name'=> 'page',
            'contents' => $page,
        ], [
            'name' => 'limit',
            'contents' => $limit,
        ]
    ]);
    header('Content-Type: application/json');
    echo $call;
}

function uploadPhoto($file, $token, $filename) {
    $photo = callHttp("/api/v2/mobile/upload", 'POST', [
        [
            'name' => 'file',
            'contents' => $file,
            'filename' => $filename,
        ],[
            'name' => 'token',
            'contents' => $token,
        ]
    ]);
    return $photo;
}

function callHttp($url, $method = 'GET', $params = []){
    $base_url = 'https://' . getenv('APP_ID') . '.qiscus.com/';
    try{
        $client = new Client(['base_uri' => $base_url]);
        $httpResp = $client->request($method, $url, [
            'multipart' => $params,
            'headers' => [
                'Accept' => 'application/json',
                'QISCUS_SDK_APP_ID' => getenv('APP_ID'),
                'QISCUS_SDK_SECRET' => getenv('SECRET_KEY')
            ]
        ]);
    } catch (GuzzleException $e){
        return $e;
    }


    return $httpResp->getBody();
}
