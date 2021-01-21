<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TestingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\QuestionController;

 // ./vendor/bin/sail upp
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Users 

Route::get("/users", "App\Http\Controllers\UserController@getAllUsers");
Route::post("/users", [UserController::class, "createUser"]);
Route::patch("/users/{id}", [UserController::class, "updateUser"]);

//Questions 

Route::get("/questions/unanswered", [QuestionController::class, "getUnansweredQuestions"]);
Route::get("/questions/answered", [QuestionController::class, "getAnsweredQuestions"]);
Route::post("/questions", [QuestionController::class, "createQuestionWithOptions"]);