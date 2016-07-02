<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 7/2/16
 * Time: 11:40 AM
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
        $statement = $mysqli->prepare("UPDATE AssignmentSubmissions SET score = ? WHERE assignmentId = ? AND studentId = ?");
        $statement->bind_param("dii", doubleval($value), $_SESSION["assignmentId"], $key);
        $statement->execute();
        $statement->close();
    }

    $mysqli->close();
}

$assignment = null;
$assignmentSubmissions = array();
$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$statement = $mysqli->prepare("SELECT * FROM Assignments WHERE id = ?");
$statement->bind_param("i", $_SESSION["assignmentId"]);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $i = $row[0];
    $t = $row[1];
    $s = $row[2];
    $l = $row[3];
    $m = $row[4];
    $cs = $row[5];
    $assignment = new Assignment($i, $t, $s, $l, $m, $cs);
}

$statement->close();
$statement = $mysqli->prepare("SELECT studentId FROM Assignments, Students INNER JOIN Enroll ON Students.id = Enroll.studentId WHERE Students.id NOT IN (SELECT studentId FROM AssignmentSubmissions WHERE assignmentId = ?) AND Enroll.courseId = ? AND lastDay < CURRENT_DATE() AND Assignments.id = ?");
$statement->bind_param("iii", $assignment->id, $course->id, $assignment->id);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $si = $row[0];

    $statement2 = $mysqli->prepare("INSERT IGNORE INTO AssignmentSubmissions (assignmentId, studentId) VALUES (?, ?)");
    $statement2->bind_param("ii", $assignment->id, $si);
    $statement2->execute();
    $statement2->close();
}

$statement->close();
$statement = $mysqli->prepare("SELECT Enroll.studentId, firstName, lastName, score, submissionDay, submissionFileName, submissionMessage FROM Enroll LEFT JOIN Students ON Enroll.studentId = Students.id LEFT JOIN UserInformation ON Students.user = UserInformation.id LEFT JOIN AssignmentSubmissions ON AssignmentSubmissions.studentId = Students.id WHERE assignmentId = ? AND courseId = ?");
$statement->bind_param("ii", $assignment->id, $course->id);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $studentId = $row[0];
    $fn = $row[1];
    $ln = $row[2];
    $sc = $row[3];
    $sd = $row[4];
    $sfn = $row[5];
    $sm = $row[6];
    array_push($assignmentSubmissions, new AssignmentSubmission($studentId, $fn, $ln, $sc, $sd, $sfn, $sm));
}

$mysqli->close();
?>
<?php include 'Header.php';?>
<h1 id="title">Grades for Assignment: <?php echo $assignment->title; ?></h1>
<h4><?php echo $course->code.' - '.$course->title; ?></h4>
<hr/>
<p>If you want to edit the assignment details <a href="../Control/MainController.php?do=editAssignment&assignmentId=<?php echo $assignment->id; ?>">click here</a>.</p>
<?php if(count($assignmentSubmissions) == 0) { ?><h2>No submissions found.</h2>
    <?php if($assignment->lastDay >= date("Y-m-d")) { ?><h4 class="text-info">Last day to submit the assignment: <?php echo $assignment->lastDay;?></h4><?php } ?>
<?php } else { ?>
<form method="post">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Student</th>
                <th>Submission Day</th>
                <th>File</th>
                <th>Message</th>
                <th>Score</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($assignmentSubmissions as $submission) { ?>
                <tr>
                    <td><?php echo $submission->firstName." ".$submission->lastName;?></td>
                    <td><?php echo $submission->submissionDay;?></td>
                    <td><a href="../Control/DownloadFile.php?studentId=<?php echo $submission->studentId;?>&assignmentId=<?php echo $assignment->id;?>"><?php echo $submission->submissionFileName; ?></a></td>
                    <td><?php echo $submission->submissionMessage;?></td>
                    <td><input type="number" min="0" max="100" name="<?php echo $submission->studentId;?>" value="<?php echo $submission->score;?>"></td>
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