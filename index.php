<?php
require 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$route = new \Klein\Klein();
header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'POST') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: X-Requested-With, content-type, access-control-allow-origin, access-control-allow-methods, access-control-allow-headers');
    }
    exit;
}

$route->respond('GET', '/', function(){
    require_once 'home.html';
});

$route->respond('GET', '/api/contacts', function($request, $response){
    if(empty($request->page) || $request->page == 0 ) {
        $request->page = 1;
    }
    $show_all=false;
    if(!empty($request->show_all) && $request->show_all==true) {
        $show_all = true;
    }
    if(empty($request->limit) || $request->limit == 0) {
        $request->limit = 20;
    }
    $contacts = getContactList($request->page, $request->limit, $show_all);
    return $response->json($contacts);
});

$route->respond('GET', '/api/mobile/contacts', function($request, $response){
    if(empty($request->page) || $request->page == 0 ) {
        $request->page = 1;
    }
    if(empty($request->limit) || $request->limit == 0) {
        $request->limit = 20;
    }
    $contacts = getContactListIos($request->page, $request->limit);
    $payload = array();
    $users= $contacts->results->users;
    foreach ($users as $user) {
        $prefix = strtolower(substr($user->username, 0, 1));
        if(preg_match("/^[a-zA-Z0-9]*$/",$prefix)) {
            $payload[$prefix][] = $user;
        }else {
            $payload['0'][] = $user;
        }
    }
    ksort($payload);
    return $response->json(['results'=>$payload]);
});

$route->respond('POST', '/api/upload', function($request, $response){
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
        return $response->json($file);
    } catch (\Exception $e) {
        return $e->getMessage();
    }
});

$route->respond('POST', '/api/login_or_register', function($request, $response){
    if (empty($request->username)) {
        header('Content-Type: application/json', true, 400);
        return json_encode(['username'=>'required']);
    }
    if (empty($request->email)) {
        header('Content-Type: application/json', true, 400);
        return json_encode(['email'=>'required']);
    }
    $login = loginOrRegister($request->email, $request->password, $request->username, $request->avatar_url);
    header('Content-Type: application/json', true, 200);
    return $response->json(['results'=>$login]);
});

$route->respond('POST', '/api/update_profile', function($request, $response){
    if (empty($request->email)) {
        header('Content-Type: application/json', true, 400);
        return json_encode(['email'=>'required']);
    }
    $updateUser = updateUserProfile($request->email, $request->password, $request->name, $request->avatar_url);
    header('Content-Type: application/json', true, 200);
    return $response->json(['results'=>$updateUser]);
});

$route->dispatch();

function getContactList($page, $limit, $show_all=false) {
    $data = [
        [
            'name'=> 'page',
            'contents' => $page,
        ], [
            'name' => 'limit',
            'contents' => $limit,
        ],
    ];
    if ($show_all==true) {
        $data[] = [
            'name' => 'show_all',
            'contents' => true,
        ];
    }
    try {
        $call = callHttpPublic("/api/v2.1/rest/get_user_list", 'GET', $data);

        return $call;
    } catch (\Exception $e) {
        return $e->getMessage();
    }
}

function loginOrRegister($email, $password, $username, $avatar_url) {
    try {
        $call = callHttpPublic("/api/v2/rest/login_or_register", 'POST', [
            [
                'name'=> 'email',
                'contents' => $email,
            ], [
                'name' => 'password',
                'contents' => $password,
            ], [
                'name' => 'username',
                'contents' => $username,
            ], [
                'name' => 'avatar_url',
                'contents' => $avatar_url,
            ]
        ]);

        return $call;
    } catch (\Exception $e) {
        return $e->getMessage();
    }
}

function getContactListIos($page, $limit) {
    try {
        $call = callHttpPublic("/api/v2.1/rest/get_user_list", 'GET', [
            [
                'name'=> 'page',
                'contents' => $page,
            ], [
                'name' => 'limit',
                'contents' => $limit,
            ], [
                'name' => 'order_query',
                'contents' => 'email asc, created_at desc',
            ], [
                'name' => 'show_all',
                'contents' => true,
            ]
        ]);

        return $call;
    } catch (\Exception $e) {
        return $e->getMessage();
    }
    return $call;
}

function uploadPhoto($file, $token, $filename) {
    $photo = callHttpPublic("/api/v2/mobile/upload", 'POST', [
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

function updateUserProfile($email, $password, $name, $avatar_url) {
    try {
        $call = callHttpPublic("/api/v2/rest/update_profile", 'POST', [
            [
                'name'=> 'email',
                'contents' => $email,
            ], [
                'name' => 'password',
                'contents' => $password,
            ], [
                'name' => 'name',
                'contents' => $name,
            ], [
                'name' => 'avatar_url',
                'contents' => $avatar_url,
            ]
        ]);

        return $call;
    } catch (\Exception $e) {
        return $e->getMessage();
    }
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
        return json_decode($httpResp->getBody()->getContents());
    } catch (GuzzleException $e){
        return $e;
    }

}

function callHttpPublic($url, $method = 'GET', $params = []){
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
        return json_decode($httpResp->getBody()->getContents());
    } catch (GuzzleException $e){
        return $e;
    }
}

function redirectHttp($prefixUri) {
    if (isset($_SERVER["SERVER_PORT"])){
        header('Location:http://'.$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT'].$prefixUri);
    } else {
        header('Location:http://'.$_SERVER['SERVER_NAME'].$prefixUri);
    }
    exit;
}