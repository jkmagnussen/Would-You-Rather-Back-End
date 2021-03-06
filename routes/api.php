<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\QuestionController;

 // ./vendor/bin/sail up
/*
|--------------------------------------------------------------------------
| API Routess
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Users

Route::group([
    "middleware"=>"api"
], function($router){
    $router->get("/users", [UserController::class, "getAllUsers"]);
    // Needs finishing 
    $router->post("/users", [UserController::class, "createUser"]);
    $router->post("/users/session", [UserController::class, "login"]);
    // Needs finishing 
    $router->put("/users/{id}", [UserController::class, "updateUser"]);
    $router->post("/users/{id}/avatar", [UserController::class, "putAvatar"]);
    $router->get("/users/me", [UserController::class, "getAuthenticatedUserProfile"]);
    
    $router->post("/users/{id}/friends", [UserController::class, "pendingFriendRequest"]);
    $router->delete("/users/{id}/friends/{friendId}", [UserController::class, "removeFriendRow"]);
    $router->patch("/users/{id}/friends/{friendId}", [UserController::class, "acceptFriendRequest"]);
});


// POST /users/joe/friends {friendId:'james'}; -> {frienderId:'Joe', friendId:'james', accepted:false}
// PATCH /users/james/friends/joe -> UPDATE friendsList (accepted) VALUES (true) WHERE frienderId = :friendId AND friendId = :frienderId

//Questions 
Route::group([
    "middleware"=>"api"
],function($router){
    $router->get("/questions/unanswered", [QuestionController::class, "getUnansweredQuestions"]);
    $router->get("/questions/answered", [QuestionController::class, "getAnsweredQuestions"]);
    $router->post("/questions", [QuestionController::class, "createQuestionWithOptions"]);
    $router->delete("/questions/{id}", [QuestionController::class, "deleteQuestion"]);
    $router->patch("/questions/{id}", [QuestionController::class, "editQuestion"]);
});


// Route delete question n
// Route update Question ute delete question 
// Route update Question ute delete question 
// Route update Question ute delete question 
// Route update Question ute delete question 
// Route update Question ute delete question 
// Route update Question ute delete question 
// Route update Question ute delete question 
// Route update Question ute delete question 
// Route update Question ute delete question 
// Route update Question 