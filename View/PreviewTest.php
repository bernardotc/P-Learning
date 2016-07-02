<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/24/16
 * Time: 11:12 AM
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

$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$test = null;
$attempts = array();
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
$statement = $mysqli->prepare("SELECT * FROM AttemptsOfTest WHERE testId = ? AND studentId = ?");
$statement->bind_param("ii", $_SESSION["testId"], $user->studentId);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $tid = $row[1];
    $sid = $row[2];
    $an = $row[3];
    $score = $row[4];
    $dayOfAttempt = $row[5];
    array_push($attempts, new Attempt($tid, $sid, $score, $dayOfAttempt));
}
$mysqli->close();

?>
<style>
    #testPreview {
        color: #ffffff;
        background-color: #8dbdd9;
        border: 1px solid;
        border-color: #255897;
        padding-top: 1%;
        padding-left: 1%;
        padding-right: 1%;
        text-align: center;
        overflow: hidden;
    }

</style>
<?php include 'Header.php';?>
    <h1 id="title">Test: <?php echo $test->title ?></h1>
    <h4><?php echo $course->code.' - '.$course->title; ?></h4>
    <hr/>
    <div id="testPreview">
        <h3><i>Last date to submit test:</i> <span style="color: #000000"><?php echo $test->lastDay ?></span></h3>
        <h3><i>Number of attempts:</i> <span style="color: #000000"><?php echo $test->attempts ?></span></h3>
        <?php foreach ($attempts as $attempt) { ?>
            <h4><i>Attempt from <?php echo $attempt->dayOfAttempt ?> score:</i> <span style="color: #000000"><?php echo $attempt->score ?></span></h4>
        <?php } ?>
        <?php if (count($attempts) < $test->attempts && $test->lastDay >= date("Y-m-d")) { ?>
        <a href="DoTest.php" class="btn btn-default btn-lg">New attempt</a>
        <?php } ?>
        <br/><br/>
    </div>
<?php include 'Footer.php';?>