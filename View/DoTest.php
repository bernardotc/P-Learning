<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/24/16
 * Time: 11:37 AM
 */
require ("../Model/User.php");
require ("../Model/Course.php");
require ("../Model/PLContent.php");

session_start();

$login = '';
$signin = '';
$home = 'class=""';
$courseActive = 'class=""';

$user = $_SESSION["user"];
$course = $_SESSION["course"];
if ($user == null) {
    header("Location: ../Control/MainController.php?do=logout");
} else if ($course == null) {
    header("Location: ../View/Home.php");
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION["testDone"])) {
        unset($_SESSION["testDone"]);
        header ("Location: ../Control/MainController.php?do=previewTest&testId=".$_SESSION["testId"]);
    } else {
        $test = $_SESSION["test"];
        $questions = $_SESSION["questionsInsideTest"];
        $correctQuestions = 0;
        $attempts = $attempt = 0;
        foreach ($questions as $question) {
            $question->optionSelected = $_POST["answer" . $question->id];
            if ($question->optionSelected == $question->correct)
                $correctQuestions++;
        }
        $mysqli = new mysqli("localhost", "root", "", "p-learning");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        $statement = $mysqli->prepare("SELECT COUNT(studentId) FROM AttemptsOfTest WHERE testId = ? AND studentId = ?");
        $statement->bind_param("ii", $_SESSION["testId"], $user->studentId);
        $statement->execute();
        $result = $statement->get_result();
        while ($row = $result->fetch_row()) {
            $attempt = $row[0];
        }
        $attempts = $attempt + 1;

        $statement->close();
        $statement = $mysqli->prepare("INSERT INTO AttemptsOfTest (testId, studentId, attemptNumber, score, dayOfAttempt) VALUES (?, ?, ?, ?, ?)");
        $score = ($correctQuestions / count($questions) * 100);
        $statement->bind_param("iiids", $_SESSION["testId"], $user->studentId, $attempts, $score, date("Y-m-d"));
        $statement->execute();
        $mysqli->close();
        $_SESSION["testDone"] = true;
    }
} else {
    unset($_SESSION["testDone"]);
    $mysqli = new mysqli("localhost", "root", "", "p-learning");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
    $attempts = 0;
    $test = null;
    $questions = array();
    $statement = $mysqli->prepare("SELECT * FROM Tests WHERE id = ?");
    $statement->bind_param("i", $_SESSION["testId"]);
    $statement->execute();
    $result = $statement->get_result();
    while ($row = $result->fetch_row()) {
        $i = $row[0];
        $t = $row[1];
        $a = $row[2];
        $l = $row[3];
        $cs = $row[4];
        $test = new Test($i, $t, $a, $l, $cs);
    }
    $statement->close();
    $statement = $mysqli->prepare("SELECT * FROM Questions, QuestionsInTests WHERE testId = ? AND Questions.id = questionId");
    $statement->bind_param("i", $_SESSION["testId"]);
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
        $question->questionI = $row[3];
        $question->questionIExt = $row[2];
        $question->answerAI = $row[6];
        $question->answerAIExt = $row[5];
        $question->answerBI = $row[9];
        $question->answerBIExt = $row[8];
        $question->answerCI = $row[12];
        $question->answerCIExt = $row[11];
        $question->answerDI = $row[15];
        $question->answerDIExt = $row[14];

        array_push($questions, $question);
    }

    $statement->close();
    $statement = $mysqli->prepare("SELECT COUNT(studentId) FROM AttemptsOfTest WHERE testId = ? AND studentId = ?");
    $statement->bind_param("ii", $_SESSION["testId"], $user->studentId);
    $statement->execute();
    $result = $statement->get_result();
    while ($row = $result->fetch_row()) {
        $attempts = $row[0];
    }

    $mysqli->close();

    if ($attempts >= $test->attempts || $test->lastDay < date("Y-m-d")) {
        header ("Location: ../Control/MainController.php?do=previewTest&testId=".$test->id);
    }

    shuffle($questions);
    $_SESSION["questionsInsideTest"] = $questions;
    $_SESSION["test"] = $test;
}
?>
<style>
    #question {
        background-color: #8dbdd9;
        border: 1px solid;
        border-color: #255897;
        padding-top: 1%;
        padding-left: 1%;
        padding-right: 1%;
        text-align: left;
        overflow: hidden;
    }
</style>
<?php include 'Header.php';?>
<h1 id="title">Test: <?php echo $test->title ?></h1>
<h4><?php echo $course->code.' - '.$course->title; ?></h4>
<p>Attempt <?php echo $attempts+1;?></p>
<hr/>
<form method="post">
    <?php if($_SERVER["REQUEST_METHOD"] == "POST") {
        echo '<h3 class="text-info" style="text-align: center">Your score is: '.$score."</h3>";
        foreach($questions as $question) { ?>
            <div id="question">
                <div class="input-group" style="width: 100%;">
                    <label class="input-group-addon" style="width:100%"><?php echo $question->question; ?></label>
                    <?php if ($question->questionIExt != null) { ?>
                        <span class="input-group-btn" style="display: inline-block;"><button type="button" class="btn btn-default" onclick="viewImage(<?php echo $question->id; ?>, 'questionI')">View Image</button></span>
                    <?php } ?>
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><input type="radio" name="answer<?php echo $question->id; ?>" value="A" id="answerA<?php echo $question->id; ?>" <?php if ($question->optionSelected == "A") echo "checked"?> disabled></span>
                    <label for="answerA<?php echo $question->id; ?>" class="form-control"><?php echo $question->answerA; ?></label>
                    <?php if ($question->optionSelected == "A") { if ($question->correct == "A") { ?>
                        <span class="input-group-addon" style="background-color: green; color: #ffffff;">Correct</span>
                    <?php } else { ?>
                        <span class="input-group-addon" style="background-color: Red; color: #ffffff;">Incorrect</span>
                    <?php }} ?>
                    <?php if ($question->answerAIExt != null) { ?>
                        <span class="input-group-btn"><button type="button" class="btn btn-default" onclick="viewImage(<?php echo $question->id; ?>, 'answerAI')">View Image</button></span>
                    <?php } ?>
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><input type="radio" name="answer<?php echo $question->id; ?>" value="B" id="answerB<?php echo $question->id; ?>" <?php if ($question->optionSelected == "B") echo "checked"?> disabled></span>
                    <label for="answerB<?php echo $question->id; ?>" class="form-control"><?php echo $question->answerB; ?></label>

                    <?php if ($question->optionSelected == "B") { if ($question->correct == "B") { ?>
                        <span class="input-group-addon" style="background-color: green; color: #ffffff;">Correct</span>
                    <?php } else { ?>
                        <span class="input-group-addon" style="background-color: Red; color: #ffffff;">Incorrect</span>
                    <?php }} ?>
                    <?php if ($question->answerBIExt != null) { ?>
                        <span class="input-group-btn"><button type="button" class="btn btn-default" onclick="viewImage(<?php echo $question->id; ?>, 'answerBI')">View Image</button></span>
                    <?php } ?>
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><input type="radio" name="answer<?php echo $question->id; ?>" value="C" id="answerC<?php echo $question->id; ?>" <?php if ($question->optionSelected == "C") echo "checked"?> disabled></span>
                    <label for="answerC<?php echo $question->id; ?>" class="form-control"><?php echo $question->answerC; ?></label>
                    <?php if ($question->optionSelected == "C") { if ($question->correct == "C") { ?>
                        <span class="input-group-addon" style="background-color: green; color: #ffffff;">Correct</span>
                    <?php } else { ?>
                        <span class="input-group-addon" style="background-color: Red; color: #ffffff;">Incorrect</span>
                    <?php }} ?>
                    <?php if ($question->answerCIExt != null) { ?>
                        <span class="input-group-btn"><button type="button" class="btn btn-default" onclick="viewImage(<?php echo $question->id; ?>, 'answerCI')">View Image</button></span>
                    <?php } ?>
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><input type="radio" name="answer<?php echo $question->id; ?>" value="D" id="answerD<?php echo $question->id; ?>" <?php if ($question->optionSelected == "D") echo "checked"?> disabled></span>
                    <label for="answerD<?php echo $question->id; ?>" class="form-control"><?php echo $question->answerD; ?></label>
                    <?php if ($question->optionSelected == "D") { if ($question->correct == "D") { ?>
                        <span class="input-group-addon" style="background-color: green; color: #ffffff;">Correct</span>
                    <?php } else { ?>
                        <span class="input-group-addon" style="background-color: Red; color: #ffffff;">Incorrect</span>
                    <?php }} ?>
                    <?php if ($question->answerDIExt != null) { ?>
                        <span class="input-group-btn"><button type="button" class="btn btn-default" onclick="viewImage(<?php echo $question->id; ?>, 'answerDI')">View Image</button></span>
                    <?php } ?>
                </div>
                <br/>
            </div>
        <?php } ?>
        <br/>
        <div style="text-align: right;">
            <a class="btn btn-primary btn-lg" href="../Control/MainController.php?do=previewTest&testId=<?php echo $test->id; ?>">Finish</a>
        </div>
    <?php } else {
        foreach($questions as $question) { ?>
            <div id="question">
                <div class="input-group" style="width: 100%;">
                    <label class="input-group-addon" style="width:100%"><?php echo $question->question; ?></label>
                    <?php if ($question->questionIExt != null) { ?>
                        <span class="input-group-btn" style="display: inline-block;"><button type="button" class="btn btn-default" onclick="viewImage(<?php echo $question->id; ?>, 'questionI')">View Image</button></span>
                    <?php } ?>
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><input type="radio" name="answer<?php echo $question->id; ?>" value="A" id="answerA<?php echo $question->id; ?>" required></span>
                    <label for="answerA<?php echo $question->id; ?>" class="form-control"><?php echo $question->answerA; ?></label>
                <?php if ($question->answerAIExt != null) { ?>
                    <span class="input-group-btn"><button type="button" class="btn btn-default" onclick="viewImage(<?php echo $question->id; ?>, 'answerAI')">View Image</button></span>
                <?php } ?>
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><input type="radio" name="answer<?php echo $question->id; ?>" value="B" id="answerB<?php echo $question->id; ?>" required></span>
                    <label for="answerB<?php echo $question->id; ?>" class="form-control"><?php echo $question->answerB; ?></label>
                <?php if ($question->answerBIExt != null) { ?>
                    <span class="input-group-btn"><button type="button" class="btn btn-default" onclick="viewImage(<?php echo $question->id; ?>, 'answerBI')">View Image</button></span>
                <?php } ?>
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><input type="radio" name="answer<?php echo $question->id; ?>" value="C" id="answerC<?php echo $question->id; ?>" required></span>
                    <label for="answerC<?php echo $question->id; ?>" class="form-control"><?php echo $question->answerC; ?></label>
                <?php if ($question->answerCIExt != null) { ?>
                    <span class="input-group-btn"><button type="button" class="btn btn-default" onclick="viewImage(<?php echo $question->id; ?>, 'answerCI')">View Image</button></span>
                <?php } ?>
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><input type="radio" name="answer<?php echo $question->id; ?>" value="D" id="answerD<?php echo $question->id; ?>" required></span>
                    <label for="answerD<?php echo $question->id; ?>" class="form-control"><?php echo $question->answerD; ?></label>
                <?php if ($question->answerDIExt != null) { ?>
                    <span class="input-group-btn"><button type="button" class="btn btn-default" onclick="viewImage(<?php echo $question->id; ?>, 'answerDI')">View Image</button></span>
                <?php } ?>
                </div>
                <br/>
            </div>
        <?php } ?>
        <br/>
        <div style="text-align: right;">
            <button class="btn btn-success" type="submit">Submit Answers</button>
        </div>
    <?php } ?>

</form>
<div class="modal fade" id="imageModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title">Image</h3>
            </div>
            <div class="modal-body" style="text-align: center;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<script>
    function displayModal(response) {
        //alert(response);
        $(".modal-body").html(response);
        $("#imageModal").modal();
    }

    function viewImage(questionId, imageId) {
        $.ajax({
            type : "post",
            url : "../Control/GetImage.php",
            data : {
                "questionId": questionId,
                "imageId": imageId
            },
            success: displayModal,
            error : function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
            }
        });
    }

</script>
<?php include 'Footer.php';?>
