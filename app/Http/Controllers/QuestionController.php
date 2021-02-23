<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Validator;

class QuestionController extends Controller {
     
    function __construct(){
        $this->pdo = DB::connection()->getPdo();
        $this->middleware("auth:api");
    }

    public function createQuestionWithOptions(Request $request){

      $validator = Validator::make(["optionPicture1"=>$request->file("optionPicture1"), "optionPicture2"=>$request->file("optionPicture2")],["optionPicture1"=>["required", "mimes:png"],"optionPicture2"=>["required", "mimes:png"]]);

      if($validator->fails()){
        return response()->json(["error"=>"Please ensure that both option pictures are present."]);
      }

      $this->pdo->beginTransaction();
      
      $this->pdo->prepare("INSERT INTO questions (authorId, timeStamp) VALUES (?, NOW())")->execute(array($request->user()->id));
      $questionId = $this->pdo->lastInsertId();
      /* 
          {
              authorId:1,
              status:"Hello world",
              optionTitle1: "Jump",
              optionTitle2: "Run",
              optionPicture1: File(),
              optionPicture2: File()
          }
      */

      $this->pdo->prepare("INSERT INTO optionsForQuestions (questionId, title) VALUES(?, ?)")->execute(array($questionId, $request->optionTitle1));
      $fileFormat1 = $request->file("optionPicture1")->extension();
      $request->file("optionPicture1")->storeAs("optionPictures", "{$this->pdo->lastInsertId()}.{$fileFormat1}");
          
      $this->pdo->prepare("INSERT INTO optionsForQuestions (questionId, title) VALUES(?, ?)")->execute(array($questionId, $request->optionTitle2));
      $fileFormat2 = $request->file("optionPicture2")->extension();
      $request->file("optionPicture2")->storeAs("optionPictures", "{$this->pdo->lastInsertId()}.{$fileFormat2}");
      
      $this->pdo->commit();
      return response()->json(array("status"=>"success"));
    }

    private function structureQuestionsData($databaseResults){
      $dataForJsonResults = array();

      foreach((array) $databaseResults as $row){
        if(!array_key_exists($row["mQuestionId"], $dataForJsonResults)){
          $dataForJsonResults[$row["mQuestionId"]] = array("authorName"=>$row["authorName"], "authorAvatarUrl"=>$row["authorAvatarUrl"]);
          $dataForJsonResults[$row["mQuestionId"]]["options"] = array();
          $dataForJsonResults[$row["mQuestionId"]]["options"][$row["optionId"]] = array("title"=>$row["optionTitle"], "chosen"=>$row["chosen"]);
        }else{
          $dataForJsonResults[$row["mQuestionId"]]["options"][$row["optionId"]] = array("title"=>$row["optionTitle"], "chosen"=>$row["chosen"]);
        }
      }
      return $dataForJsonResults;
    }

    public function getUnansweredQuestions(){
       $filteredUnansweredQuestions = $this->pdo->prepare(
          "SELECT (SELECT FALSE) AS chosen,
          optionsForQuestions.title AS optionTitle,
          optionsForQuestions.id AS optionId,
          users.userName AS authorName,
          users.avatarUrl AS authorAvatarUrl,
          questions.id AS mQuestionId
          FROM questions 
          LEFT JOIN optionsForQuestions ON 
          questions.id = optionsForQuestions.questionId 
          LEFT JOIN users ON 
          questions.authorId = users.id
          WHERE NOT EXISTS (SELECT userId FROM userOptionChoice WHERE userId = ? AND questionId = questions.id)"
          );
        $filteredUnansweredQuestions->execute(array(1));
        $databaseResults = $filteredUnansweredQuestions->fetchAll(\PDO::FETCH_ASSOC);

        return response()->json($this->structureQuestionsData($databaseResults));
    }


    public function getAnsweredQuestions(){  
      $getUserSelectedAndOtherOptionsWithSelectionIdAndQuestionAndAuthor = $this->pdo->prepare(
        "SELECT userOptionChoice.userId as chooserUserId,
        questions.id AS mQuestionId,
        IF(userOptionChoice.optionId = optionsForQuestions.id, TRUE, FALSE) AS chosen, 
        optionsForQuestions.title AS optionTitle,
        optionsForQuestions.id AS optionId,
        users.userName AS authorName,
        users.avatarUrl AS authorAvatarUrl
        FROM `userOptionChoice`
        LEFT JOIN optionsForQuestions ON 
        userOptionChoice.questionId = optionsForQuestions.questionId 
        LEFT JOIN questions ON 
        questions.id = optionsForQuestions.questionId 
        LEFT JOIN users ON 
        questions.authorId = users.id 
        WHERE userOptionChoice.userId = ?"
        );

      $getUserSelectedAndOtherOptionsWithSelectionIdAndQuestionAndAuthor->execute(array(1));
      $databaseResults = $getUserSelectedAndOtherOptionsWithSelectionIdAndQuestionAndAuthor->fetchAll(\PDO::FETCH_ASSOC);

      return response()->json($this->structureQuestionsData($databaseResults));
    }

    public function editQuestion(Request $request, $id){
      foreach((array) $request->options as $option){
        $editQuestionQuery = $this->pdo->prepare("UPDATE optionsForQuestions SET title = ? WHERE questionId = ? AND id = ?");
        $editQuestionQuery->execute(array($option["title"], $id, $option['id'] ));
      }
      return response()->json(array("status"=>true));
    }

    public function deleteQuestion(Request $request, $id){
      $deleteQuestionQuery = $this->pdo->prepare("DELETE FROM questions WHERE id=?");
      return response()->json(array("status"=>$deleteQuestionQuery->execute(array($id))));
    }
}