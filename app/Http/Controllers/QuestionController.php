<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller {
     
    function __construct(){
        $this->pdo = DB::connection()->getPdo();
    }

    public function createQuestionWithOptions(Request $request){
      $this->pdo->beginTransaction();
      
      $this->pdo->prepare("INSERT INTO questions (authorId, timeStamp) VALUES (?, NOW())")->execute(array($request->authorId));
      $questionId = $this->pdo->lastInsertId();
      /* 
          {
              "authorId": 1,
              "optionss":[
                  {"title":"Option one"},
                  {"title":"Option two"}
              ]
          }
      */
      foreach((array) $request->input('options') as $option){
        $this->pdo->prepare("INSERT INTO optionsForQuestions (questionId, title) VALUES(?, ?)")->execute(array($questionId, $option["title"]));
      }
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

  // question structure 
  /*
    [
      {title:"Option one"}, // 0
      {title:"Option two"} // 1
    ]
  */
  
 // $editQuestionQuery = $this->pdo->prepare("UPDATE")
 $selectQuestionOptionsQuery = $this->pdo->prepare("SELECT id FROM optionsForQuestions WHERE questionId = :questionId ");
  /*
      JSON example of results of above query (options of questions ids so we know whichn one to edit)
      [
        {id: 32},
        {id: 33}
      ] 
  */
  $selectQuestionOptionsQuery->execute(array($id));
  $questionOptionsIds = $selectQuestionOptionsQuery->fetchAll();
  foreach((array) $questionOptionsIds as $key=>$answerRow){
    /*
      $answerRow = array("id"=>32)
      $key = 0
    */
    $editQuestionQuery = $this->pdo->prepare("UPDATE optionsForQuestions SET title = :title WHERE :optionId");
    $editQuestionQuery->execute(array($request->options[$key]["title"], $answerRow["id"] ));
  }
}

    public function deleteQuestion(Request $request, $id){
      $deleteQuestionQuery = $this->pdo->prepare("DELETE FROM questions WHERE id=?");
      return response()->json(array("status"=>$deleteQuestionQuery->execute(array($id))));
    }

}