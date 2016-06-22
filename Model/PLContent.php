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
            if($slide->id != 0) {
                $slide->updateInDatabase($mysqli, $this->id);
            } else {
                $slide->saveInDatabase($mysqli, $this->id);
            }
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
    public $slideType = "text";
    public $slideContent;
    public $slideImage = null;
    public $slideImageExt = null;
    public $question;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    function __construct6($sN, $sT, $sC, $sI, $sIE, $q) {
        $this->slideNumber = $sN;
        $this->slideType = $sT;
        $this->slideContent = $sC;
        $this->slideImage = $sI;
        $this->slideImageExt = $sIE;
        $this->question = $q;
    }

    function __construct7($i, $sN, $sT, $sC, $sI, $sIE, $q) {
        $this->id = $i;
        $this->slideNumber = $sN;
        $this->slideType = $sT;
        $this->slideContent = $sC;
        $this->slideImage = $sI;
        $this->slideImageExt = $sIE;
        $this->question = $q;
    }

    function saveInDatabase($mysqli, $plcontentId) {
        $this->question->saveTextInDatabase($mysqli);
        if ($this->question->id == 0) {
            return;
        } else {
            $statement = $mysqli->prepare("INSERT INTO Slides (slideNumber, slideType, slideContent, slideImage, slideImageExt, questionId, plcontentId) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $statement->bind_param('issssii', $this->slideNumber, $this->slideType, $this->slideContent, $this->slideImage, $this->slideImageExt, $this->question->id, $plcontentId);
            $statement->execute();
            $this->id = $statement->insert_id;
        }
    }

    function updateInDatabase($mysqli, $plcontentId) {
        $this->question->updateInDatabase($mysqli);
        if ($this->question->id == 0) {
            return;
        } else {
            $statement = $mysqli->prepare("UPDATE Slides SET slideNumber = ?, slideType = ?, slideContent  = ?, slideImage = ?, slideImageExt = ?, questionId  = ?, plcontentId  = ? WHERE id = ?");
            $statement->bind_param('issssiii', $this->slideNumber, $this->slideType, $this->slideContent, $this->slideImage, $this->slideImageExt, $this->question->id, $plcontentId, $this->id);
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
    public $questionI = null;
    public $answerAI = null;
    public $answerBI = null;
    public $answerCI = null;
    public $answerDI = null;
    public $questionIExt = null;
    public $answerAIExt = null;
    public $answerBIExt = null;
    public $answerCIExt = null;
    public $answerDIExt = null;
    public $course = 0;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    function __construct7($q, $a, $b, $c, $d, $correct, $course) {
        $this->question = $q;
        $this->answerA = $a;
        $this->answerB = $b;
        $this->answerC = $c;
        $this->answerD = $d;
        $this->correct = $correct;
        $this->course = $course;
    }

    function __construct8($i, $q, $a, $b, $c, $d, $correct, $course) {
        $this->id = $i;
        $this->question = $q;
        $this->answerA = $a;
        $this->answerB = $b;
        $this->answerC = $c;
        $this->answerD = $d;
        $this->correct = $correct;
        $this->course = $course;
    }

    function saveTextInDatabase($mysqli) {
        $statement = $mysqli->prepare("INSERT INTO Questions (question, answerA, answerB, answerC, answerD, correct, course) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $statement->bind_param('ssssssi', $this->question, $this->answerA, $this->answerB, $this->answerC, $this->answerD, $this->correct, $this->course);
        $statement->execute();
        $this->id = $statement->insert_id;
    }

    function saveCompleteInDatabase($mysqli) {
        $statement = $mysqli->prepare("INSERT INTO Questions (question, questionImageExt, questionImage, answerA, answerAImageExt, answerAImage, answerB, answerBImageExt, answerBImage, answerC, answerCImageExt, answerCImage, answerD, answerDImageExt, answerDImage, correct, course) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $statement->bind_param('ssssssssssssssssi', $this->question, $this->questionIExt, $this->questionI, $this->answerA, $this->answerAIExt, $this->answerAI, $this->answerB, $this->answerBIExt, $this->answerBI, $this->answerC, $this->answerCIExt, $this->answerCI, $this->answerD, $this->answerDIExt, $this->answerDI, $this->correct, $this->course);
        $statement->execute();
        //print_r($statement);
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

    function updateCompleteInDatabase($mysqli) {
        $statement = $mysqli->prepare("UPDATE Questions SET question = ?, questionImageExt = ?, questionImage = ?, answerA = ?, answerAImageExt = ?, answerAImage = ?, answerB = ?, answerBImageExt = ?, answerBImage = ?, answerC = ?, answerCImageExt = ?, answerCImage = ?, answerD = ?, answerDImageExt = ?, answerDImage = ?, correct = ? WHERE id = ?");
        $statement->bind_param('ssssssssssssssssi', $this->question, $this->questionIExt, $this->questionI, $this->answerA, $this->answerAIExt, $this->answerAI, $this->answerB, $this->answerBIExt, $this->answerBI, $this->answerC, $this->answerCIExt, $this->answerCI, $this->answerD, $this->answerDIExt, $this->answerDI, $this->correct, $this->id);
        $statement->execute();
    }
}

class Test {
    public $id = 0;
    public $title;
    public $attempts;
    public $lastDay;
    public $courseSectionId;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    function __construct5($i, $t, $a, $l, $cs) {
        $this->id = $i;
        $this->title = $t;
        $this->attempts = $a;
        $this->lastDay = $l;
        $this->courseSectionId = $cs;
    }
}