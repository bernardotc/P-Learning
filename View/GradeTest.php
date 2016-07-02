<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 7/2/16
 * Time: 1:59 PM
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
    $mysqli = new mysqli("localhost", "root", "", "p-learning");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
    foreach($_POST as $key => $value) {
        $keys = explode("-", $key);
        $statement = $mysqli->prepare("UPDATE AttemptsOfTest SET score = ? WHERE testId = ? AND studentId = ? AND attemptNumber = ?");
        $statement->bind_param("diii", doubleval($value), $_SESSION["testId"], intval($keys[0]), intval($keys[1]));
        $statement->execute();
        $statement->close();
    }
    $mysqli->close();
}

$test = null;
$testAttempts = array();
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
$statement = $mysqli->prepare("SELECT studentId FROM Tests, Students INNER JOIN Enroll ON Students.id = Enroll.studentId WHERE Students.id NOT IN (SELECT studentId FROM AttemptsOfTest WHERE testId = ?) AND Enroll.courseId = ? AND lastDay < CURRENT_DATE() AND Tests.id = ?");
$statement->bind_param("iii", $test->id, $course->id, $test->id);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $si = $row[0];

    $statement2 = $mysqli->prepare("INSERT IGNORE INTO AttemptsOfTest (testId, studentId, attemptNumber) VALUES (?, ?, 1)");
    $statement2->bind_param("ii", $test->id, $si);
    $statement2->execute();
    $statement2->close();
}

$statement->close();
$statement = $mysqli->prepare("SELECT Enroll.studentId, firstName, lastName, score, dayOfAttempt, attemptNumber FROM Enroll LEFT JOIN Students ON Enroll.studentId = Students.id LEFT JOIN UserInformation ON Students.user = UserInformation.id LEFT JOIN AttemptsOfTest ON AttemptsOfTest.studentId = Students.id WHERE testId = ? AND courseId = ? ORDER BY Enroll.studentId ASC, attemptNumber ASC");
$statement->bind_param("ii", $test->id, $course->id);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $studentId = $row[0];
    $fn = $row[1];
    $ln = $row[2];
    $sc = $row[3];
    $dA = $row[4];
    $an = $row[5];
    $aux = new Attempt($test->id, $studentId, $an, $sc, $dA);
    $aux->firstName = $fn;
    $aux->lastName = $ln;
    array_push($testAttempts, $aux);
}

$mysqli->close();
?>
<?php include 'Header.php';?>
    <h1 id="title">Grades for Test: <?php echo $test->title; ?></h1>
    <h4><?php echo $course->code.' - '.$course->title; ?></h4>
    <hr/>
    <p>If you want to edit the assignment details <a href="../Control/MainController.php?do=editTest&testId=<?php echo $test->id; ?>">click here</a>.</p>
<?php if(count($testAttempts) == 0) { ?><h2>No attempts found.</h2>
    <?php if($test->lastDay >= date("Y-m-d")) { ?><h4 class="text-info">Last day to submit attempts: <?php echo $test->lastDay;?></h4><?php } ?>
<?php } else { ?>
    <form method="post">
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>Student</th>
                <th>Attemt Number</th>
                <th>Attempt Day</th>
                <th>Score</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($testAttempts as $attempt) { ?>
                <tr>
                    <td><?php echo $attempt->firstName." ".$attempt->lastName;?></td>
                    <td><?php echo $attempt->attemptNumber;?></td>
                    <td><?php echo $attempt->dayOfAttempt;?></td>
                    <td><input type="number" min="0" max="100" name="<?php echo $attempt->studentId."-".$attempt->attemptNumber;?>" value="<?php echo $attempt->score;?>"></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <div style="text-align: right">
            <a href="../View/CourseHome.php" class="btn btn-danger">Cancel</a>
            <button type="submit" class="btn btn-success">Save</button>
        </div>
    </form>
<?php } ?>
<?php include 'Footer.php';?>