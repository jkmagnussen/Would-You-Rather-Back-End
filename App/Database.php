<?php 

namespace App;
use Symfony\Component\Dotenv\Dotenv;
$dotenv=new Dotenv();
$dotenv->load(__DIR__."/../.env");

class Database{
public $pdo;
    public function connect(){
        if ($this->pdo == null ) {
            $this->pdo = new \PDO("mysql:dbname=".$_ENV["DB_NAME"].";host=".$_ENV["DB_HOST"], $_ENV["DB_USER"], $_ENV["DB_PASSWORD"]);
        }
        $this->pdo->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING );
        return $this;
    }

    public function getAllUsers(){
        $usersInfo = $this->pdo->prepare("SELECT id, username, password,  avatarUrl FROM users");
        $usersInfo->execute();
        return $usersInfo->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getQuestionsWithOptions(){
        $getQuestionsQuery = $this->pdo->prepare("SELECT questions.id AS questionId, questions.authorId, users.userName AS authorName, users.avatarUrl AS authorAvatarUrl FROM questions INNER JOIN users ON questions.authorId = users.id");
        $getQuestionsQuery->execute();
        $questionsData = $getQuestionsQuery->fetchAll(\PDO::FETCH_ASSOC);

        foreach($questionsData as $key => $value){
            $optionsForQuestion = $this->pdo->prepare("SELECT * FROM optionsForQuestions WHERE questionId = ?");
            $optionsForQuestion->execute(array($value['questionId']));
            $questionsData[$key]["options"] = $optionsForQuestion->fetchAll(\PDO::FETCH_ASSOC);
        }
        
        return $questionsData;
    }

    public function createQuestionWithOptions($questionData){
        $this->pdo->beginTransaction();
        $this->pdo->prepare("INSERT INTO questions (authorId, timeStamp) VALUES (?, GETDATE())")->execute(array($questionData["authorId"]));
        /* 
            {
                author_id:'blah',
                options:[
                    {title:"Option one},
                    {title:"Option two}s
                ]
            }
        */
        foreach((array) $questionData["options"] as $option){
            $this->pdo->prepare("INSERT INTO optionsForQuestions (questionId, title) VALUES(?, ?)")->execute(array($questionId, $option["title"]));
        }
        $this->pdo->commit();
        return array("status"=>"success");
    }

    public function getQuestionsForUsersWithAnsweredStatus($answeredStatus){
        $databaseResults = array();
        if($answeredStatus){

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

        }else{
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
        }
        
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

    public function toggleVoteForQuestionById($questionId,$optionId,$userId){
        $checkIfUserOptionChoiceExistsQuery = $this->pdo->prepare("SELECT id FROM userOptionChoice WHERE userId=:userId AND questionId=:questionId AND OptionId=:optionId");
        $checkIfUserOptionChoiceExistsQuery->execute(array(":questionId"=>$questionId, ":optionId"=>$optionId, ":userId"=>$userId));
        if($checkIfUserOptionChoiceExistsQuery->rowCount() > 0){
            $deleteOptionVoteQuery = $this->pdo->prepare("DELETE FROM userOptionChoice WHERE userId=? AND questionId=?");
            $deleteOptionVoteQuery->execute(array($userId, $questionId));
            return array("status"=>"success", "chosen"=>false);
        } else{
            $createAnswerForQuestionQuery = $this->pdo->prepare("INSERT INTO userOptionChoice (userId, OptionId, questionId) VALUES (:userId, :optionId, :questionId)");
            $queryResult = $createAnswerForQuestionQuery->execute(array(":userId"=>$userId, ":optionId"=>$optionId, ":questionId"=>$questionId));
            if(!$queryResult){
                return array("status"=>"failed", "message"=>$this->pdo->errorInfo());
            }else{
                return array("status"=>"success", "chosen" => true);
            }
        }
    }
}

/*
Redundant codee

SELECT * FROM `userOptionChoice` LEFT JOIN optionsForQuestions ON userOptionChoice.questionId = optionsForQuestions.questionId 

SELECT questions.id AS 'questionId', questions.authorId, optionsForQuestions.id AS 'optionId', optionsForQuestions.title FROM optionsForQuestions INNER JOIN questions ON questions.id = optionsForQuestions.questionId



SELECT questions.id AS 'questionId', questions.authorId, optionsForQuestions.id AS 'optionId', optionsForQuestions.title FROM optionsForQuestions INNER JOIN questions ON questions.id = optionsForQuestions.questionId 
/\ authorId, questionId, optionTitle


*/

// questions/answered

// mock data 


/*{
    1):{
        options:[{title:"blah", id:1}, {"blah2", id:2}],
        author:{username:"Tom", avatar:"saoijdoiqjwd.png"}
    }
    2:{
        options:[{title:"blah", id:1}, {"blah2", id:2}],
        author:{username:"Tom", avatar:"saoijdoiqjwd.png"}
    }
}*/