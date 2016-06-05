<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/31/16
 * Time: 6:53 PM
 */

require("../Model/User.php");

$courseId = $_POST["courseId"];
$teachersIn = array();
$teachersNotIn = array();
$studentsIn = array();
$studentsNotIn = array();

$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$statement = $mysqli->prepare("SELECT * FROM Teachers INNER JOIN UserInformation ON Teachers.user = UserInformation.id INNER JOIN Teaching ON Teachers.id = teacherId WHERE courseId = ?");
$statement->bind_param("i", $courseId);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $ti = $row[0];
    $i = $row[3];
    $f = $row[4];
    $l = $row[5];
    $e = $row[6];
    $u = $row[7];
    $p = $row[8];
    array_push($teachersIn, new Teacher($i, $f, $l, $e, $u, $p, $ti));
}
$statement->close();
$statement = $mysqli->prepare("SELECT * FROM Teachers INNER JOIN UserInformation ON UserInformation.id = user WHERE Teachers.id NOT IN (SELECT Teachers.id FROM Teachers INNER JOIN UserInformation ON Teachers.user = UserInformation.id INNER JOIN Teaching ON Teachers.id = teacherId WHERE courseId = ?)");
$statement->bind_param("i", $courseId);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $ti = $row[0];
    $i = $row[3];
    $f = $row[4];
    $l = $row[5];
    $e = $row[6];
    $u = $row[7];
    $p = $row[8];
    array_push($teachersNotIn, new Teacher($i, $f, $l, $e, $u, $p, $ti));
}
$statement->close();
$statement = $mysqli->prepare("SELECT * FROM Students INNER JOIN UserInformation ON Students.user = UserInformation.id INNER JOIN Enroll ON Students.id = studentId WHERE courseId = ?");
$statement->bind_param("i", $courseId);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $si = $row[0];
    $i = $row[3];
    $f = $row[4];
    $l = $row[5];
    $e = $row[6];
    $u = $row[7];
    $p = $row[8];
    array_push($studentsIn, new Student($i, $f, $l, $e, $u, $p, $si));
}
$statement->close();
$statement = $mysqli->prepare("SELECT * FROM Students INNER JOIN UserInformation ON Students.user = UserInformation.id WHERE Students.id NOT IN (SELECT Students.id FROM Students INNER JOIN UserInformation ON Students.user = UserInformation.id INNER JOIN Enroll ON Students.id = studentId WHERE courseId = ?)");
$statement->bind_param("i", $courseId);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $si = $row[0];
    $i = $row[3];
    $f = $row[4];
    $l = $row[5];
    $e = $row[6];
    $u = $row[7];
    $p = $row[8];
    array_push($studentsNotIn, new Student($i, $f, $l, $e, $u, $p, $si));
}
$mysqli->close();
?>
<style>
    #buttons {
        text-align: center;
    }
</style>
<script>
    function moveTeacherIn() {
        var teachersSelected = [];
        var teachers = $("#teachersNotIn")[0];
        for (var i = 0; i < teachers.length; i++) {
            if (teachers[i].selected) {
                teachersSelected.push(teachers[i]);
            }
        }
        $("#teachersIn").append(teachersSelected);
    }

    function moveTeacherOut() {
        var teachersSelected = [];
        var teachers = $("#teachersIn")[0];
        for (var i = 0; i < teachers.length; i++) {
            if (teachers[i].selected) {
                teachersSelected.push(teachers[i]);
            }
        }
        $("#teachersNotIn").append(teachersSelected);
    }

    function moveStudentIn() {
        var studentsSelected = [];
        var students = $("#studentsNotIn")[0];
        for (var i = 0; i < students.length; i++) {
            if (students[i].selected) {
                studentsSelected.push(students[i]);
            }
        }
        $("#studentsIn").append(studentsSelected);
    }

    function moveStudentOut() {
        var studentsSelected = [];
        var students = $("#studentsIn")[0];
        for (var i = 0; i < students.length; i++) {
            if (students[i].selected) {
                studentsSelected.push(students[i]);
            }
        }
        $("#studentsNotIn").append(studentsSelected);
    }
</script>
<div id="teachers">
    <div class="col-lg-5 form-group">
        <label for="teachersNotIn" class="control-label">Teachers not in course</label>
        <select class="form-control" name="teachersNotIn[]" id="teachersNotIn" multiple>
            <?php foreach ($teachersNotIn as $teacher) {
                echo '<option value="'.$teacher->teacherId.'">'.$teacher->firstName.' '.$teacher->lastName.'</option>';
            } ?>
        </select>
    </div>
    <div id="buttons" class="col-lg-2">
        <br/><br/>
        <button type="button" class="btn btn-default" onclick="moveTeacherIn()">&rarr;</button><br/>
        <button type="button" class="btn btn-default" onclick="moveTeacherOut()">&larr;</button>
    </div>
    <div class="col-lg-5 form-group">
        <label for="teachersIn" class="control-label">Teachers in course</label>
        <select class="form-control" name="teachersIn[]" id="teachersIn" multiple>
            <?php foreach ($teachersIn as $teacher) {
                echo '<option value="'.$teacher->teacherId.'">'.$teacher->firstName.' '.$teacher->lastName.'</option>';
            } ?>
        </select>
    </div>
</div>
<br/>
<div id="students">
    <div class="col-lg-5 form-group">
        <label for="studentsNotIn" class="control-label">Students not in course</label>
        <select class="form-control" name="studentsNotIn[]" id="studentsNotIn" multiple>
            <?php foreach ($studentsNotIn as $student) {
                echo '<option value="'.$student->studentId.'">'.$student->firstName.' '.$student->lastName.'</option>';
            } ?>
        </select>
    </div>
    <div id="buttons" class="col-lg-2">
        <br/><br/>
        <button type="button" class="btn btn-default" onclick="moveStudentIn()">&rarr;</button><br/>
        <button type="button" class="btn btn-default" onclick="moveStudentOut()">&larr;</button>
    </div>
    <div class="col-lg-5 form-group">
        <label for="studentsIn" class="control-label">Students in course</label>
        <select class="form-control" name="studentsIn[]" id="studentsIn" multiple>
            <?php foreach ($studentsIn as $student) {
                echo '<option value="'.$student->studentId.'">'.$student->firstName.' '.$student->lastName.'</option>';
            } ?>
        </select>
    </div>
</div>
