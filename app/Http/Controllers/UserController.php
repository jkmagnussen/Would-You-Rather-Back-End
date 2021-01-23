<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UserController extends Controller{
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

    // Authentication

    public function pendingFriendRequest(Request $request, $userId){
        $insertFriendQuery = $this->pdo->prepare("INSERT INTO friendsList (frienderId, friendId) VALUES (:frienderId, :friendId)");
        $success = $insertFriendQuery->execute(array("frienderId"=>$userId, "friendId"=>$request->friendId));
        
        return response()->json(array("status"=>$success));
    }

    public function removeFriendRow(Request $request, $userId, $friendId){
        $deleteFriendQuery = $this->pdo->prepare("DELETE FROM friendsList WHERE frienderId = ? AND friendId = ? OR frienderId = ? AND friendId = ?");
        // use : as oppsed to $ variable sign for assigning function argument paramiter

        $success = $deleteFriendQuery->execute(array($userId, $friendId, $friendId, $userId));
        
        return response()->json(array("status"=>$success));
    }

        public function acceptFriendRequest(Request $request, $userId, $friendId){
        $acceptFriendQuery = $this->pdo->prepare("UPDATE friendsList SET accepted = TRUE WHERE frienderId = ? AND friendid = ? ");
        // use : as oppsed to $ variable sign for assigning function argument paramiter

        $success = $acceptFriendQuery->execute(array($friendId, $userId));
        
        return response()->json(array("status"=>$success));
    } 

}