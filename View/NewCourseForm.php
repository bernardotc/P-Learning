<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/11/16
 * Time: 1:15 PM
 */

require("../Model/Course.php");

session_start();

$titleErr = $codeErr = $descriptionErr = "";
$title = $code = $description = "";

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["title"])) {
        $titleErr = "Course Title is required. ";
    } else if (strlen($_POST["title"]) > 50) {
        $titleErr .= "Course Title is too long.";
    } else {
        $title = test_input($_POST["title"]);
    }

    if (empty($_POST["code"])) {
        $codeErr = "Course Code is required. ";
    } else if (strlen($_POST["code"]) > 20) {
        $codeErr .= "Course Code is too long.";
    } else {
        $code = test_input($_POST["code"]);
    }

    if (strlen($_POST["description"]) > 500) {
        $descriptionErr .= "Course Description is too long.";
    } else {
        $description = test_input($_POST["description"]);
    }

    if ($titleErr.$codeErr.$descriptionErr == "") {
        $course = new Course($title, $code, $description);
        $_SESSION["course"] = $course;
        // Redirect
        header("Location: ../Control/MainController.php?do=newCourse");
    }
}
?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
    <fieldset>
        <legend>Course Information</legend>
        <div id="container">
            <div class="form-group <?php if($titleErr != "") { echo "has-error"; }?>">
                <label for="title" class="control-label col-lg-3">Course Title</label>
                <div class="col-lg-9">
                    <input type="text" class="form-control" id="title" name="title" placeholder="e.g. Physics 1, Linear Equations..." value="<?php echo $title ?>" required>
                    <span class="text-danger"><?php echo $titleErr ?></span>
                </div>
            </div>
            <div class="form-group <?php if($codeErr != "") { echo "has-error"; }?>">
                <label for="code" class="control-label col-lg-3">Course Code</label>
                <div class="col-lg-9">
                    <input type="text" class="form-control" id="code" name="code" placeholder="e.g. CE832-7-AU, SP234..." value="<?php echo $code ?>" required>
                    <span class="text-danger"><?php echo $codeErr ?></span>
                </div>
            </div>
            <div class="form-group <?php if($descriptionErr != "") { echo "has-error"; }?>">
                <label for="description" class="control-label col-lg-3">Description</label>
                <div class="col-lg-9">
                    <textarea rows="6" class="form-control" id="description" name="description" placeholder="Describe the contents of the course." ><?php echo $description ?></textarea>
                    <span class="text-danger"><?php echo $descriptionErr ?></span>
                </div>
            </div>
        </div>
    </fieldset>
    <br/>
    <button type="submit" class="btn btn-lg btn-success">Create course</button>
    <br/><br/><br/>
</form>