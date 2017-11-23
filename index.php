<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$route = new \Klein\Klein();
header("Access-Control-Allow-Origin: *");

$route->respond('GET', '/', function(){
    if(isset($_COOKIE['APP_ID']) && isset($_COOKIE['SECRET_KEY'])){
        echo "asd";
    } else {
        redirectHttp("/login");
    }
});

$route->respond('GET', '/login', function(){
    require_once 'login.html';
});

$route->respond('GET', '/api/contacts', function($request){
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
    return $call;
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
    $base_url = 'https://' . $_COOKIE['APP_ID'] . '.qiscus.com/';
    try{
        $client = new Client(['base_uri' => $base_url]);
        $httpResp = $client->request($method, $url, [
            'multipart' => $params,
            'headers' => [
                'Accept' => 'application/json',
                'QISCUS_SDK_APP_ID' => $_COOKIE['APP_ID'],
                'QISCUS_SDK_SECRET' => $_COOKIE['SECRET_KEY']
            ]
        ]);
    } catch (GuzzleException $e){
        return $e;
    }
    return $httpResp->getBody();
}


function redirectHttp($prefixUri) {
    if (isset($_SERVER["SERVER_PORT"])){
        header('Location:http://'.$_SERVER['SERVER_NAME'].$prefixUri);
    } else {
        header('Location:http://'.$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].$prefixUri);
    }
    exit;
}