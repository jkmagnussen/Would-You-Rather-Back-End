<?php
namespace App\Http\Controllers;

use App\Models\Users;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Validator;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    function __construct(){
        $this->pdo = DB::connection()->getPdo();
        $this->middleware('auth:api', ["except"=>["login", "createUser"]]);
    }

    public function getAllUsers(){

        $usersInfo = $this->pdo->prepare("SELECT id, username, password,  avatarUrl FROM Users");
        $usersInfo->execute();

        return response()->json($usersInfo->fetchAll(\PDO::FETCH_ASSOC));
    }

    public function getAuthenticatedUserProfile(Request $request){
        return response()->json(auth()->user()); 
    }   

    private function saveAvatarForUser($avatar, $user){
        $validator = Validator::make(array("avatar"=>$avatar), array("avatar"=>"mimes:png"));
        if($validator->fails()){
            return false;
        }
        $fileName = $user->id;
        $fileFormat = $avatar->extension();
        $path = $avatar->storeAs("avatars", "{$fileName}.{$fileFormat}");
        return true;
    }

    public function createUser(Request $request){
        $user = Users::firstOrCreate(array("userName"=>$request->userName, "email"=>$request->email, "password"=>\Hash::make($request->password), "avatarUrl"=>"something.png"));
        
        $avatarStatus = $this->saveAvatarForUser($request->file("avatar"), $user);
        
        if(!$avatarStatus){
            return response()->json(["error" => "invalid avatar please ensure a valid image has been selected."]);
        }
        return response()->json([
            "status"=>"Success",
            "userObj"=>$user
        ]);
    }

    public function putAvatar(Request $request, $id){
        if($id != $request->user()->id){
            return response()->json(array("301"=>"error"));
        }
        $avatarStatus = $this->saveAvatarForUser($request->file("avatar"), $request->user());
        if(!$avatarStatus){
            return response()->json(["error" => "invalid avatar please ensure a valid image has been selected."]);
        }
        return response()->json(array("status"=>"success"));
    }


    

    public function updateUser(Request $request, $id){
        $editUserInfo = $this->pdo->prepare("UPDATE Users SET userName = ?, email = ?, avatarUrl = ? WHERE id=?");
        $success = $editUserInfo->execute(array($request->userName, $request->email, $request->avatarUrl, $id));
        return response()->json(array("status"=>$success));
    }

    // Authentication
    public function login(Request $request){    
        $token = auth()->attempt(request(['email', 'password']));
        
        if(!$token){
            return response()->json(["status"=>"failed, wrong password or email"]);
        }
        return $this->respondWithToken($token);
    }

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

    protected function respondWithToken($token){
        return response()->json([
            "access_token" => $token,
            "token_type" => "bearer",
            "expires_in" => auth()->factory()->getTTL() * 60
        ]);
    }
}