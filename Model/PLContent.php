<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/10/16
 * Time: 12:26 PM
 */

class PLContent {
    public $id = 0;
    public $contentTitle;
    public $slides = array();
    public $courseSectionId;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    function __construct3($t, $s, $cS) {
        $this->contentTitle = $t;
        $this->slides = $s;
        $this->courseSectionId = $cS;
    }

    function __construct4($i, $t, $s, $cS) {
        $this->id = $i;
        $this->contentTitle = $t;
        $this->slides = $s;
        $this->courseSectionId = $cS;
    }

    function saveInDatabase() {
        $mysqli = new mysqli("localhost", "root", "", "p-learning");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        $mysqli->begin_transaction();
        $statement = $mysqli->prepare("INSERT INTO PLContents (contentTitle, courseSectionId) VALUES (?, ?)");
        $statement->bind_param('si', $this->contentTitle, $this->courseSectionId);
        $statement->execute();
        $this->id = $statement->insert_id;
        if ($this->id != 0) {
            foreach ($this->slides as $slide) {
                $slide->saveInDatabase($mysqli, $this->id);
                if ($slide->id == 0) {
                    $mysqli->rollback();
                    $mysqli->close();
                    return false;
                }
            }
            $mysqli->commit();
            $mysqli->close();
            return true;
        } else {
            $mysqli->rollback();
            $mysqli->close();
            return false;
        }
    }

    function updateInDatabase() {
        $mysqli = new mysqli("localhost", "root", "", "p-learning");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        $mysqli->begin_transaction();
        $statement = $mysqli->prepare("UPDATE PLContents SET contentTitle = ? WHERE id = ?");
        $statement->bind_param('si', $this->contentTitle, $this->id);
        $statement->execute();
        foreach ($this->slides as $slide) {
            $slide->updateInDatabase($mysqli, $this->id);
        }
        $mysqli->commit();
        $mysqli->close();
    }

    function deleteContentInDatabase() {
        $mysqli = new mysqli("localhost", "root", "", "p-learning");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        foreach ($this->slides as $slide) {
            $slide->deleteInDatabase($mysqli);
        }
    }
}

class Slide {
    public $id = 0;
    public $slideNumber;
    public $slideContent;
    public $question;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    function __construct3($sN, $sC, $q) {
        $this->slideNumber = $sN;
        $this->slideContent = $sC;
        $this->question = $q;
    }

    function __construct4($i, $sN, $sC, $q) {
        $this->id = $i;
        $this->slideNumber = $sN;
        $this->slideContent = $sC;
        $this->question = $q;
    }

    function saveInDatabase($mysqli, $plcontentId) {
        $this->question->saveInDatabase($mysqli);
        if ($this->question->id == 0) {
            return;
        } else {
            $statement = $mysqli->prepare("INSERT INTO Slides (slideNumber, slideContent, questionId, plcontentId) VALUES (?, ?, ?, ?)");
            $statement->bind_param('isii', $this->slideNumber, $this->slideContent, $this->question->id, $plcontentId);
            $statement->execute();
            $this->id = $statement->insert_id;
        }
    }

    function updateInDatabase($mysqli, $plcontentId) {
        $this->question->updateInDatabase($mysqli);
        if ($this->question->id == 0) {
            return;
        } else {
            $statement = $mysqli->prepare("UPDATE Slides SET slideNumber = ?, slideContent  = ?, questionId  = ?, plcontentId  = ? WHERE id = ?");
            $statement->bind_param('isiii', $this->slideNumber, $this->slideContent, $this->question->id, $plcontentId, $this->id);
            $statement->execute();
        }
    }

    function deleteInDatabase($mysqli) {
        $this->question->deleteInDatabase($mysqli);
        $statement = $mysqli->prepare("DELETE FROM Slides WHERE id = ?");
        $statement->bind_param('i', $this->id);
        $statement->execute();
    }
}

class Question {
    public $id = 0;
    public $question;
    public $answerA;
    public $answerB;
    public $answerC;
    public $answerD;
    public $correct;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    function __construct6($q, $a, $b, $c, $d, $correct) {
        $this->question = $q;
        $this->answerA = $a;
        $this->answerB = $b;
        $this->answerC = $c;
        $this->answerD = $d;
        $this->correct = $correct;
    }

    function __construct7($i, $q, $a, $b, $c, $d, $correct) {
        $this->id = $i;
        $this->question = $q;
        $this->answerA = $a;
        $this->answerB = $b;
        $this->answerC = $c;
        $this->answerD = $d;
        $this->correct = $correct;
    }

    function saveInDatabase($mysqli) {
        $statement = $mysqli->prepare("INSERT INTO Questions (question, answerA, answerB, answerC, answerD, correct) VALUES (?, ?, ?, ?, ?, ?)");
        $statement->bind_param('ssssss', $this->question, $this->answerA, $this->answerB, $this->answerC, $this->answerD, $this->correct);
        $statement->execute();
        $this->id = $statement->insert_id;
    }

    function deleteInDatabase($mysqli) {
        $statement = $mysqli->prepare("DELETE FROM Questions WHERE id = ?");
        $statement->bind_param('i', $this->id);
        $statement->execute();
    }

    function updateInDatabase($mysqli) {
        $statement = $mysqli->prepare("UPDATE Questions SET question = ?, answerA = ?, answerB = ?, answerC = ?, answerD = ?, correct = ? WHERE id = ?");
        $statement->bind_param('ssssssi', $this->question, $this->answerA, $this->answerB, $this->answerC, $this->answerD, $this->correct, $this->id);
        $statement->execute();
    }
}