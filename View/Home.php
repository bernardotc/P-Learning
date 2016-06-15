<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/12/16
 * Time: 5:24 PM
 */

require ("../Model/User.php");
require ("../Model/Course.php");
session_start();

$login = '';
$signin = '';
$home = 'class="active"';
$course = null;

$user = $_SESSION["user"];
if ($user == null) {
    header("Location: ../Control/MainController.php?do=logout");
}

$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$courses = array();
if ($user instanceof CourseAdministrator) {
    $statement = $mysqli->prepare("SELECT * FROM Courses, CoursesCreated WHERE Courses.id = courseId AND courseAdministratorId = ?");
    $statement->bind_param("i", $_SESSION["user"]->courseAdministratorId);
} else if ($user instanceof Teacher) {
    $statement = $mysqli->prepare("SELECT * FROM Courses, Teaching WHERE Courses.id = courseId AND teacherId = ?");
    $statement->bind_param("i", $_SESSION["user"]->teacherId);
} else {
    $statement = $mysqli->prepare("SELECT * FROM Courses, Enroll WHERE Courses.id = courseId AND StudentId = ?");
    $statement->bind_param("i", $_SESSION["user"]->studentId);
}
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
    aside {
        display: block;
        background-color: #2c68b2;
        border-color: #255897;
        color: #ffffff;
    }
    section {
        display: block;
    }
</style>

<?php include 'Header.php';?>
<section class="col-lg-8">
    <h1 id="title">P-Learning</h1>
    <h4>Welcome <?php echo $user->firstName." ".$user->lastName?>.</h4>
    <hr/>
    <h2>Dashboard</h2>
    <ul class="list-group">
        <?php foreach ($courses as $course) {
            echo '<a href = "../Control/MainController.php?do=showCourse&courseId='.$course->id.'" class="list-group-item">';
            echo '<h4 class="list-group-item-heading">'.$course->code.' - '.$course->title.'</h4>';
            echo '</a>';
        }?>
    </ul>
</section>
<aside class="col-lg-4">
    <?php if ($user instanceof CourseAdministrator) { ?>
    <h4>Create and edit users</h4>
    <ul>
    <li><a href="NewTeacher.php">Create Teacher</a></li>
    <li><a href="NewStudent.php">Create Student</a></li>
    </ul>
    <h4>Create and edit courses</h4>
    <ul>
        <li><a href="NewCourse.php">Create Course</a></li>
    </ul>
    <h4>Assign users to courses</h4>
    <ul>
        <li><a href="AssignUsersToCourses.php">Assign it</a></li>
    </ul>
    <?php } ?>
</aside>
<?php include 'Footer.php';?>