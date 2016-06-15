<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/28/16
 * Time: 1:26 PM
 */
require("../Model/Course.php");
require("../Model/User.php");

session_start();

$login = '';
$home = 'class="active"';
$signin = '';

$user = $_SESSION["user"];
if ($user == null ) {
    header("Location: ../Control/MainController.php?do=logout");
} else if (!($user instanceof CourseAdministrator)) {
    header("Location: ../View/Home.php");
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newTeachers = $_POST["teachersIn"];
    $newStudents = $_POST["studentsIn"];
    $courseId = $_POST["course"];
    $_SESSION["newTeachers"] = $newTeachers;
    $_SESSION["newStudents"] = $newStudents;
    $_SESSION["courseId"] = $courseId;
    header("Location: ../Control/MainController.php?do=enrollment");
}

$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$courses = array();
$statement = $mysqli->prepare("SELECT * FROM Courses, CoursesCreated WHERE Courses.id = courseId AND courseAdministratorId = ?");
$statement->bind_param("i", $_SESSION["user"]->courseAdministratorId);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $i = $row[0];
    $t = $row[1];
    $c = $row[2];
    $d = $row[3];
    array_push($courses, new Course($i, $t, $c, $d));
}
$mysqli->close();

?>
    <style>
        form {
            background-color: #8dbdd9;
            border: 1px solid;
            border-color: #255897;
            min-height: 20%;
            padding-top: 1%;
            padding-left: 7%;
            padding-right: 7%;
            padding-bottom: 1%;
            text-align: left;
            overflow: hidden;
        }

        form > button {
            float: right;
            min-width: 20%;
            margin-bottom: 20%;
        }

        #selectForm {
            display: block;
            text-align: -webkit-center;
        }

    </style>
<?php include 'Header.php';?>
    <h2 id="title">Edit users in courses</h2>
    <hr/>
    <div id="selectForm">
        <?php if (count($courses) == 0) { ?>
            <h3>There are no courses in the database.</h3>
        <?php } else { ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
            <p>Please choose the course you will like to edit.</p>
            <select id="course" name="course" class="form-control">
                <?php foreach ($courses as $course) {
                    echo '<option value="'.$course->id.'">'.$course->code."  ::  ".$course->title."</option>";
                } ?>
            </select>
            <br/>
            <div id="users"></div>
            <button class="btn btn-success" type="submit" onclick="selectAll()">Save</button>
        </form>
        <?php } ?>
    </div>
    <script>
        function addUsers(response) {
            //alert(response);
            $("#users").html(response);
        }

        function getUsers() {
            var course = $("#course")[0].value;
            $.ajax({
                type : "post",
                url : "../Control/GetUsersInCourse.php",
                data : {
                    "courseId" : course
                },
                success : addUsers,
                error : function(jqXHR, textStatus, errorMessage) {
                    console.log(errorMessage);
                }
            })
        }

        function selectAll() {
            var teachers = $("#teachersIn")[0];
            for (var i = 0; i < teachers.length; i++) {
                if (!teachers[i].selected) {
                    teachers[i].selected = true;
                }
            }

            var students = $("#studentsIn")[0];
            for (i = 0; i < students.length; i++) {
                if (!students[i].selected) {
                    students[i].selected = true;
                }
            }
        }

        window.onready = getUsers();
    </script>
<?php include 'Footer.php';?>