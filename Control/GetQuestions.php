<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/17/16
 * Time: 11:38 AM
 */

require("../Model/Course.php");
require("../Model/PLContent.php");

session_start();

if (isset($_SESSION["questionsInTest"])) {
    $questionsInTest = $_SESSION["questionsInTest"];
} else {
    $questionsInTest = array();
}
$questions = array();
$question = null;
if ($_POST["do"] == "removeQuestionInTest") {
    foreach ($questionsInTest as $q) {
        if ($q->id == $_POST["questionId"]) {
            $key = array_search($q, $questionsInTest, true);
            unset($questionsInTest[$key]);
            $_SESSION["questionsInTest"] = $questionsInTest;
            break;
        }
    }
} else {
    $mysqli = new mysqli("localhost", "root", "", "p-learning");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
    if ($_POST["do"] == "questionSummary" || $_POST["do"] == "addQuestionToTest") {
        $statement = $mysqli->prepare("SELECT * FROM Questions WHERE id = ?");
        $statement->bind_param("i", $_POST["questionId"]);
        $statement->execute();
        $result = $statement->get_result();
        while ($row = $result->fetch_row()) {
            $i = $row[0];
            $q = $row[1];
            $a = $row[4];
            $b = $row[7];
            $c = $row[10];
            $d = $row[13];
            $cA = $row[16];
            $co = $row[17];

            $question = new Question($i, $q, $a, $b, $c, $d, $cA, $co);
            if ($_POST["do"] == "addQuestionToTest") {
                array_push($questionsInTest, $question);
                $_SESSION["questionsInTest"] = $questionsInTest;
            } else {
                $question->questionIExt = $row[2];
                $question->answerAIExt = $row[5];
                $question->answerBIExt = $row[8];
                $question->answerCIExt = $row[11];
                $question->answerDIExt = $row[14];
            }
        }
    } else {
        if ($_POST["question"] == "") {
            $statement = $mysqli->prepare("SELECT * FROM Questions WHERE course = ?");
            $statement->bind_param("i", $_SESSION["course"]->id);
            $statement->execute();
            $result = $statement->get_result();
            while ($row = $result->fetch_row()) {
                $i = $row[0];
                $q = $row[1];
                $a = $row[4];
                $b = $row[7];
                $c = $row[10];
                $d = $row[13];
                $cA = $row[16];
                $co = $row[17];

                array_push($questions, new Question($i, $q, $a, $b, $c, $d, $cA, $co));
            }
        } else {
            $statement = $mysqli->prepare("SELECT * FROM Questions WHERE question LIKE ? AND course = ?");
            $questionT = "%" . $_POST["question"] . "%";
            $statement->bind_param("si", $questionT, $_SESSION["course"]->id);
            $statement->execute();
            $result = $statement->get_result();
            while ($row = $result->fetch_row()) {
                $i = $row[0];
                $q = $row[1];
                $a = $row[4];
                $b = $row[7];
                $c = $row[10];
                $d = $row[13];
                $cA = $row[16];
                $co = $row[17];

                array_push($questions, new Question($i, $q, $a, $b, $c, $d, $cA, $co));
            }
        }
    }

    $mysqli->close();

if ($_POST["do"] == "test") {
    foreach ($questions as $question) { ?>
        <a id="<?php echo $question->id; ?>" onclick="getQuestionSummary(this.id)" class="list-group-item">
            <h5 class="list-group-item-heading"><?php echo $question->question; ?></h5>
        </a>
<?php }} else if ($_POST["do"] == "getQuestions") {
    foreach ($questions as $question) { ?>
        <a href="../View/EditMCQ.php?id=<?php echo $question->id; ?>" class="list-group-item">
            <h5 class="list-group-item-heading"><?php echo $question->question; ?></h5>
        </a>
<?php }} else if ($_POST["do"] == "questionSummary") { ?>
    <h3>Question Summary</h3>
    <hr/>
    <p><strong>Question:</strong> <?php echo $question->question; ?></p>
    <p <?php if($question->correct == "A") echo 'class="text-success" style="background-color: lightgreen"'; else echo 'class="text-danger"'?>><strong>Answer A:</strong> <?php echo $question->answerA; ?></p>
    <p <?php if($question->correct == "B") echo 'class="text-success" style="background-color: lightgreen"'; else echo 'class="text-danger"'?>><strong>Answer B:</strong> <?php echo $question->answerB; ?></p>
    <p <?php if($question->correct == "C") echo 'class="text-success" style="background-color: lightgreen"'; else echo 'class="text-danger"'?>><strong>Answer C:</strong> <?php echo $question->answerC; ?></p>
    <p <?php if($question->correct == "D") echo 'class="text-success" style="background-color: lightgreen"'; else echo 'class="text-danger"'?>><strong>Answer D:</strong> <?php echo $question->answerD; ?></p>
    <p><strong>Contains Images:</strong> <?php if($question->questionIExt.$question->answerAIExt.$question->answerBIExt.$question->answerCIExt.$question->answerDIExt != null) echo "YES"; else echo "NO"; ?></p>
    <?php if (!in_array($question, $questionsInTest)) { ?><div style="text-align: right"><button type="button" class="btn btn-primary" onclick="addQuestionToTest(<?php echo $question->id; ?>)">Add Question to Test</button></div><br/>
<?php }} else { ?>
    <div id="q-<?php echo $question->id; ?>">
        <div class="col-lg-10">
            <a onclick="getQuestionSummary(this.id)" class="list-group-item">
            <h5 class="list-group-item-heading"><?php echo $question->question; ?></h5>
                <input type="hidden" name="questions[]" value="<?php echo $question->id; ?>">
            </a>
        </div>
        <div class="col-lg-2">
            <button type="button" class="btn btn-primary" aria-hidden="true" onclick="removeQuestion(<?php echo $question->id; ?>)">Remove Question</button>
        </div>
    </div>
<?php }} ?>

