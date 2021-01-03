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
        $usersInfo = $this->pdo->prepare("SELECT id, username, avatarUrl FROM users");
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
                    {title:"Option two}
                ]
            }
        */
        foreach((array) $questionData["options"] as $option){
            $this->pdo->prepare("INSERT INTO optionsForQuestions (questionId, title) VALUES(?, ?)")->execute(array($questionId, $option["title"]));
        }
        $this->pdo->commit();
        return array("status"=>"success");
    }
}