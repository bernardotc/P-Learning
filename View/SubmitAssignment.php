<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/28/16
 * Time: 1:05 PM
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

$errorMessage = "";

$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$assignment = null;
$otherSubmission = 0;
$statement = $mysqli->prepare("SELECT * FROM Assignments WHERE id = ?");
$statement->bind_param("i", $_SESSION["assignmentId"]);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $i = $row[0];
    $t = $row[1];
    $sF = $row[2];
    $l = $row[3];
    $am = $row[4];
    $cs = $row[5];
    $assignment = new Assignment($i, $t, $sF, $l, $am, $cs);
}
$statement->close();
$statement = $mysqli->prepare("SELECT COUNT(submissionDay) FROM AssignmentSubmissions WHERE assignmentId = ? AND studentId = ?");
$statement->bind_param("ii", $_SESSION["assignmentId"], $user->studentId);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $otherSubmission = $row[0];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $uploadOk = 1;
    $fileType = pathinfo(basename($_FILES["submitFile"]["name"]), PATHINFO_EXTENSION);
    // Check file size
    if ($_FILES["submitFile"]["size"] > 10000000) {
        $errorMessage .= "Sorry, your file is too large. ";
        $uploadOk = 0;
    }
    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        $errorMessage .= "Your file was not uploaded. ";
        // if everything is ok, try to upload file
    } else {
        if ($fileType == null) {
            $file = new File(null, null, null, null);
        } else {
            $file = new File(file_get_contents($_FILES["submitFile"]["tmp_name"]), $_FILES["submitFile"]["name"], $fileType, null);
        }
        if ($otherSubmission == 0) {
            $statement = $mysqli->prepare("INSERT INTO AssignmentSubmissions (assignmentId, studentId, submissionFile, submissionFileName, submissionFileExt, submissionMessage, submissionDay) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $statement->bind_param("iisssss", $_SESSION["assignmentId"], $user->studentId, $file->file, $file->fileName, $file->fileExt, $_POST["assignmentMessage"], date("Y-m-d"));
            $statement->execute();
        } else {
            $statement = $mysqli->prepare("UPDATE AssignmentSubmissions SET submissionFile = ?, submissionFileName = ?, submissionFileExt = ?, submissionMessage = ?, submissionDay = ? WHERE assignmentId = ? AND studentId = ?");
            $statement->bind_param("sssssii", $file->file, $file->fileName, $file->fileExt, $_POST["assignmentMessage"], date("Y-m-d"), $_SESSION["assignmentId"], $user->studentId);
            $statement->execute();
        }
        $mysqli->close();
        unset($_SESSION["assignmentId"]);
        header("Location: ../View/CourseHome.php");
    }
}

if ($assignment->lastDay < date("Y-m-d")) {
    header ("Location: ../View/CourseHome.php");
}

$mysqli->close();
?>
<?php include 'Header.php';?>
<h1 id="title">Assignment: <?php echo $assignment->title ?></h1>
<h4><?php echo $course->code.' - '.$course->title; ?></h4>
<hr/>
<?php if ($otherSubmission == 1) { ?>
    <h3 style="text-align: center;" class="text-primary">YOU ALREADY DID ONE SUBMISSION!</h3>
<?php } ?>
<h4 class="text-warning">Last day to submit something: <?php echo $assignment->lastDay ?></h4>
<p>Assignment message:</p>
<div class="col-lg-12" style="background-color: #ffffff;"><?php echo $assignment->message ?></div>
<br/><br/>
<form method="post" enctype="multipart/form-data">
    <?php if($assignment->submitFiles == 1) { ?>
        <p class="text-info">You are about to submit a file. It can be a Word document, PowerPoint presentation, PDF, etc. Make sure the file is less than 10 MB in size.</p>
        <div class="form-group">
            <label for="submitFile">File to submit</label>
            <input class="form-control" type="file" name="submitFile" id="submitFile" required>
        </div>
        <h4><?php echo $errorMessage?></h4>
    <?php } ?>
    <p><strong>Include a message</strong></p>
    <div id="assignmentMessage" style="max-height: 400px;">
    </div>
    <br/><br/>
    <div style="text-align: right">
        <a href="../View/CourseHome.php" class="btn btn-danger">Cancel</a>
        <button type="submit" class="btn btn-success">Submit</button>
    </div>
</form>
<script src='//cdn.tinymce.com/4/tinymce.min.js'></script>
<script>
    $("#assignmentMessage").ready(function() {
        tinymce.init({
            selector: "#assignmentMessage"
        });
    });
</script>
<?php include 'Footer.php';?>