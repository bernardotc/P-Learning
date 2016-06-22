<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/20/16
 * Time: 6:06 PM
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $questions = $_POST["questions"];
    $mysqli = new mysqli("localhost", "root", "", "p-learning");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
    $mysqli->begin_transaction();
    $statement = $mysqli->prepare("UPDATE Tests SET testTitle = ?, attempts = ?, lastDay = ? WHERE id = ?");
    $statement->bind_param("sisi", $_POST["testTitle"], $_POST["testAttempts"], $_POST["lastDay"], $_SESSION["testId"]);
    $statement->execute();
    $statement->close();
    $statement = $mysqli->prepare("DELETE FROM QuestionsInTests WHERE testId = ?");
    $statement->bind_param("i", $_SESSION["testId"]);
    $statement->execute();
    foreach ($questions as $q) {
        $statement->close();
        $statement = $mysqli->prepare("INSERT INTO QuestionsInTests (testId, questionId) VALUES (?, ?)");
        $statement->bind_param("ii", $_SESSION["testId"], $q);
        $statement->execute();
    }
    $mysqli->commit();
    $mysqli->close();
    unset($_SESSION["questionsInTest"]);
    unset($_SESSION["testId"]);
    header("Location: ../View/CourseHome.php");
}

$questions = array();
$test = null;
$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
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

    array_push($questions, new Question($i, $q, $a, $b, $c, $d, $cA, $co));
}
$_SESSION["questionsInTest"] = $questions;

$mysqli->close();
?>
<?php include 'Header.php';?>
    <h1 id="title">Editing a Test with Multiple Choice Questions</h1>
    <h4><?php echo $course->code.' - '.$course->title; ?></h4>
    <hr/>
    <form method="post">
        <input class="form-control" type="text" name="testTitle" placeholder="Name for the test" value="<?php echo $test->title;?>" required><br/>
        <div class="form-group col-lg-6">
            <label for="testAttempts">Number of attempts</label>
            <input class="form-control" type="number" name="testAttempts" id="testAttempts" value="<?php echo $test->attempts;?>" required>
        </div>
        <div class="form-group col-lg-6">
            <label for="lastDay">Last day for taking test</label>
            <input class="form-control" type="date" name="lastDay" id="lastDay" value="<?php echo $test->lastDay;?>" required>
        </div>
        <p>Select one question from the following list:</p>
        <div class="col-lg-6">
            <input id="questionFilter" type="text" class="form-control" placeholder="Filter by question" onkeydown="getQuestions()"/>
            <div class="list-group" id="questions" style="overflow:scroll; height:270px;"></div>
        </div>
        <div class="col-lg-6" style="background-color: #ffffff" id="questionSummary">

        </div>
        <div class="col-lg-12">
            <h3>Questions in Test</h3>
            <p>The questions below will appear in a random order each time a student takes the test.</p>
            <hr/>
            <div class="list-group" id="questionsInTest">
                <?php foreach ($questions as $question) { ?>
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
                <?php } ?>
            </div>
        </div>
        <div class="col-lg-12" style="text-align: right;">
            <hr/>
            <a href="../View/CourseHome.php" type="reset" class="btn btn-danger">Cancel</a>
            <button type="submit" class="btn btn-success">Save Test</button>
        </div>
    </form>
    <script>
        function insertQuestions(response) {
            $("#questions").html(response);
        }

        function insertQuestionSummary(response) {
            $("#questionSummary").html(response);
        }

        function insertQuestionToTest(response) {
            $("#questionsInTest").append(response);
            $("#questionSummary button").remove();
        }

        function doNothing(response) {
            //alert(response);
        }

        function getQuestions() {
            var val = $("#questionFilter")[0].value;
            $.ajax({
                type : "post",
                url : "../Control/GetQuestions.php",
                data : {
                    "question": val,
                    "do": "test"
                },
                success : insertQuestions,
                error : function(jqXHR, textStatus, errorMessage) {
                    console.log(errorMessage);
                }
            });
        }

        function getQuestionSummary(id) {
            $.ajax({
                type : "post",
                url : "../Control/GetQuestions.php",
                data : {
                    "questionId": id,
                    "do": "questionSummary"
                },
                success : insertQuestionSummary,
                error : function(jqXHR, textStatus, errorMessage) {
                    console.log(errorMessage);
                }
            });
        }

        function addQuestionToTest(id) {
            $.ajax({
                type : "post",
                url : "../Control/GetQuestions.php",
                data : {
                    "questionId": id,
                    "do": "addQuestionToTest"
                },
                success: insertQuestionToTest,
                error : function(jqXHR, textStatus, errorMessage) {
                    console.log(errorMessage);
                }
            });
        }

        function removeQuestion(id) {
            $.ajax({
                type : "post",
                url : "../Control/GetQuestions.php",
                data : {
                    "questionId": id,
                    "do": "removeQuestionInTest"
                },
                success: doNothing,
                error : function(jqXHR, textStatus, errorMessage) {
                    console.log(errorMessage);
                }
            });
            $("#q-" + id).remove();
        }

        $("#questions").ready(getQuestions());
    </script>
<?php include 'Footer.php';?>