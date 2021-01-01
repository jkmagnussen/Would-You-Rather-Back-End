<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;
use App\Database;

require __DIR__ . "/../vendor/autoload.php";
$database = (new Database())->connect();

$app = AppFactory::create();

$app->get("/", function(Request $request, Response $response, $array) {
    $response->getBody()->write("Hello World");
    return $response;
});

$app->post("/login", function(Request $request, Response $response, $args) {
    $data = $request->getParsedBody();
    $response->getBody()->write($data["name"]);
    return $response;
});

$app->patch("/user/{name}", function(Request $request, Response $response, $args){
    $userObject = array(
        "id" => 23,
        "username" => "Joe",
        "email" => "joe@gmail.com",
        "dateReg"=>"12/02/2012"
    );

    $updateData = json_decode($request->getBody()->getContents());

    foreach($updateData as $key => $value){
        if($userObject[$key] == null){
            return;
        } else{
            $userObject[$key] = $value;
        }
    }
    $response -> getBody()->write(json_encode($userObject));
    return $response;
});

$app->get("/users", function(Request $request, Response $response, $args)use($database){
    $users = $database->getAllUsers();
    $response->getBody()->write(json_encode($users));
    return $response->withHeader("Content-Type", "application/json");
});


$app->get("/questions", function(Request $request, Response $response, $args)use($database){
    $questions = $database->getQuestionsWithOptions();
    $response->getBody()->write(json_encode($questions));
    return $response->withHeader("Content-Type", "application/json");

    
});

$app->run();