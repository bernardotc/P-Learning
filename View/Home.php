<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/12/16
 * Time: 5:24 PM
 */

require ("../Model/User.php");
session_start();

$login = '';
$signin = '';
$home = 'class="active"';

$user = $_SESSION["user"];
if ($user == null) {
    header("Location: ../Control/MainController.php?do=logout");
}
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
</section>
<aside class="col-lg-4">
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
</aside>
<?php include 'Footer.php';?>