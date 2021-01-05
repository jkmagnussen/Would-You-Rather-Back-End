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
        $getUserSelectedAndOtherOptionsWithSelectionIdAndQuestionAndAuthor = $this->pdo->prepare("SELECT userOptionChoice.userId as chooserUserId,
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
            WHERE userOptionChoice.userId = ?");

        $getUserSelectedAndOtherOptionsWithSelectionIdAndQuestionAndAuthor->execute(array(1));

        $databaseResults = $getUserSelectedAndOtherOptionsWithSelectionIdAndQuestionAndAuthor->fetchAll(\PDO::FETCH_ASSOC);

        $dataForJsonResults = array();
        $dataForJsonResults["authorName"] = $databaseResults[0]["authorName"];
        $dataForJsonResults["authorAvatarUrl"] = $databaseResults[0]["authorAvatarUrl"];
        $dataForJsonResults["options"] = array();


        foreach((array) $databaseResults as $resultRow){
            $dataForJsonResults["options"][$resultRow["optionId"]] = array("title" => $resultRow["optionTitle"], "chosen" => $resultRow["chosen"]);
        }

        return $dataForJsonResults;
    }

    public function createVoteForQuestionById($questionId,$optionId,$userId){
        $createAnswerForQuestionQuery = $this->pdo->prepare("INSERT INTO userOptionChoice (userId, OptionId, questionId) VALUES (:userId, :optionId, :questionId)");
        $createAnswerForQuestionQuery->execute(array("userId"=>$userId, "optionId"=>$optionId, "questionId"=>$questionId));
        return array("status"=>"success");
    }
}

/*

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