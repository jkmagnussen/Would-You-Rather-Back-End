<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestingController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //
    }

    public function helloWorld($name){
        return response()->json(["message"=>"Hello world $name"], 201);
    }

    public function createGreeting(Request $request){
        return response()->json(["message"=>"Created greeting for $request->username"]);
    }
}