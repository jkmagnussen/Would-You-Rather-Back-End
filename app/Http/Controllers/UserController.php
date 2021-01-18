<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

// 

    function __construct(){
        $this->pdo = DB::connection()->getPdo();
    }

    public function getAllUsers(){

        $usersInfo = $this->pdo->prepare("SELECT id, username, password,  avatarUrl FROM users");
        $usersInfo->execute();

        return response()->json($usersInfo->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function createUser(Request $request){
        return response()->json([
            "userName"=>$request->userName,
            "bio"=>$request->bio
        ]);
    }

    public function updateUser(Request $request, $id){
        return response()->json([
            "updateUserId"=>$id,
            "newName"=>$request->newName
        ]);
    }

    // login? 
}