<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/22/16
 * Time: 12:15 PM
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
        $file = new File(file_get_contents($_FILES["submitFile"]["tmp_name"]), $_FILES["submitFile"]["name"], $fileType, $_SESSION["courseSectionId"]);
        $mysqli = new mysqli("localhost", "root", "", "p-learning");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        $statement = $mysqli->prepare("INSERT INTO Files (file, fileName, fileExt, courseSectionId) VALUES (?, ?, ?, ?)");
        $statement->bind_param("sssi", $file->file, $file->fileName, $file->fileExt, $file->courseSectionId);
        $statement->execute();
        $mysqli->close();
        unset($_SESSION["courseSectionId"]);
        header("Location: ../View/CourseHome.php");
    }
}


?>
<?php include 'Header.php';?>
<h1 id="title">Uploading a File</h1>
<h4><?php echo $course->code.' - '.$course->title; ?></h4>
<hr/>
<form method="post" enctype="multipart/form-data">
    <p>You are about to submit a file. It can be a Word document, PowerPoint presentation, PDF, etc. Make sure the file is less than 10 MB in size.</p>
    <div class="form-group">
        <label for="submitFile">File to submit</label>
        <input class="form-control" type="file" name="submitFile" id="submitFile" required>
    </div>
    <h4><?php echo $errorMessage?></h4>
    <div style="text-align: right">
        <a href="../View/CourseHome.php" class="btn btn-danger">Cancel</a>
        <button type="submit" class="btn btn-success">Submit</button>
    </div>
</form>
<?php include 'Footer.php';?>
