<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/21/16
 * Time: 1:35 PM
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
    $submitFiles = 0;
    if (isset($_POST["submitFiles"])) {
        $submitFiles = 1;
    }
    $mysqli = new mysqli("localhost", "root", "", "p-learning");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
    $statement = $mysqli->prepare("UPDATE Assignments SET assignmentTitle = ?, submitFiles = ?, lastDay = ?, assignmentMessage = ? WHERE id = ?");
    $statement->bind_param("sissi", $_POST["assignmentTitle"], $submitFiles, $_POST["lastDay"], $_POST["assignmentMessage"], $_SESSION["assignmentId"]);
    $statement->execute();
    $mysqli->close();
    unset($_SESSION["assignmentId"]);
    header("Location: ../View/CourseHome.php");
}

$assignment = null;
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
$mysqli->close();

?>
<?php include 'Header.php';?>
    <h1 id="title">Editing an Assignment</h1>
    <h4><?php echo $course->code.' - '.$course->title; ?></h4>
    <hr/>
    <form method="post">
        <input class="form-control" type="text" name="assignmentTitle" placeholder="Name for the Assignment" value="<?php echo $assignment->title;?>" required><br/>
        <div class="col-lg-12">
            <div class="form-group col-lg-6">
                <label for="submitFiles">Submission of files</label>
                <div class="input-group">
                    <span class="input-group-addon">Permit submission of files</span>
                    <span class="input-group-addon"><input class="form-control" type="checkbox" name="submitFiles" id="submitFiles" value="true" <?php if ($assignment->submitFiles == 1) echo "checked";?>></span>
                </div>
            </div>
            <div class="form-group col-lg-6">
                <label for="lastDay">Last day for submission</label>
                <input class="form-control" type="date" name="lastDay" id="lastDay" value="<?php echo $assignment->lastDay;?>" required>
            </div>
        </div>
        <p>Assignment message</p>
        <div id="assignmentMessage" style="background-color: #ffffff; max-height: 400px;">
            <?php echo $assignment->message;?>
        </div>
        <div style="text-align: right">
            <a href="../View/CourseHome.php" class="btn btn-danger">Cancel</a>
            <button type="submit" class="btn btn-success">Save</button>
        </div>
    </form>
    <script src='//cdn.tinymce.com/4/tinymce.min.js'></script>
    <script>
        $("#assignmentMessage").ready(function() {
            tinymce.init({
                selector: "#assignmentMessage",
                inline: true
            });
        });
    </script>
<?php include 'Footer.php';?>