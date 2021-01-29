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

Route::get("/users", "App\Http\Controllers\UserController@getAllUsers");

// Needs finishing 
Route::post("/users", [UserController::class, "createUser"]);
// Needs finishing 
Route::patch("/users/{id}", [UserController::class, "updateUser"]);

Route::post("/users/{id}/friends", [UserController::class, "pendingFriendRequest"]);
Route::delete("/users/{id}/friends/{friendId}", [UserController::class, "removeFriendRow"]);
Route::patch("/users/{id}/friends/{friendId}", [UserController::class, "acceptFriendRequest"]);
// POST /users/joe/friends {friendId:'james'}; -> {frienderId:'Joe', friendId:'james', accepted:false}
// PATCH /users/james/friends/joe -> UPDATE friendsList (accepted) VALUES (true) WHERE frienderId = :friendId AND friendId = :frienderId

//Questions 

Route::get("/questions/unanswered", [QuestionController::class, "getUnansweredQuestions"]);
Route::get("/questions/answered", [QuestionController::class, "getAnsweredQuestions"]);
Route::post("/questions", [QuestionController::class, "createQuestionWithOptions"]);

Route::delete("/questions/{id}", [QuestionController::class, "deleteQuestion"]);
Route::patch("/questions/{id}", [QuestionController::class, "editQuestion"]);

// Route delete question 
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