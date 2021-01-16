<?php

// ./vendor/bin/sail up

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;
use App\Database;

require __DIR__ . "/../vendor/autoload.php";
$database = (new Database())->connect();

$app = AppFactory::create();

$app->options("/{roots:.+}", function(Request $request, Response $response, $args){
return $response;
}
);

$app->add(function($request, $handler){
    $response = $handler->handle($request);
    return $response
        ->withHeader("Access-Control-Allow-Origin","http://localhost:3000")
        ->withHeader("Access-Control-Allow-Headers", "X-Requested-With, Content-Type, Accept, Origin, Authorization")
        ->withHeader("Acess-Control-Allow-Methods","GET, POST, PUT, DELETE, PATCH, OPTIONS");
});

$app->addRoutingMiddleware();

$app->get("/", function(Request $request, Response $response, $array) {
    $response->getBody()->write("Hello World");
    return $response;
});

$app->post("/login", function(Request $request, Response $response, $args) {
    $data = $request->getParsedBody();
    $response->getBody()->write($data["name"]);
    return $response;
});

// Edit user //

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

// Retrieve all users //

$app->get("/users", function(Request $request, Response $response, $args)use($database){
    $users = $database->getAllUsers();
    $response->getBody()->write(json_encode($users));
    return $response->withHeader("Content-Type", "application/json");
});

// Retrieve user questions //

$app->get("/questions", function(Request $request, Response $response, $args)use($database){
    $questions = $database->getQuestionsWithOptions();
    $response->getBody()->write(json_encode($questions));
    return $response->withHeader("Content-Type", "application/json");
});

$app->get("/questions/answered", function(Request $request, Response $response, $args)use($database){
    $questions = $database->getQuestionsForUsersWithAnsweredStatus(true);
    $response->getBody()->write(json_encode($questions));
    return $response->withHeader("Content-Type", "application/json");
});

$app->get("/questions/unanswered", function(Request $request, Response $response, $args)use($database){
    $questions = $database->getQuestionsForUsersWithAnsweredStatus(false);
    $response->getBody()->write(json_encode($questions));
    return $response->withHeader("Content-Type", "application/json");
});

// Create user question //

$app->post("/questions", function(Request $request, Response $response, $args)use($database){
    $createQuestion = $database->createQuestionWithOptions($request->getParsedBody());
    $response->getBody()->write(json_encode($createQuestion));
    return $response->withHeader("Content-Type", "application/json");
});

$app->post("/questions/{questionId}/answer", function(Request $request, Response $response, $args)use($database){
    $userInputData = $request->getParsedBody();
    $addVote = $database->toggleVoteForQuestionById($args["questionId"], $userInputData["optionId"], $userInputData["userId"]);
    $response->getBody()->write(json_encode($addVote));
    return $response->withHeader("Content-Type", "application/json");
});

$app->run();