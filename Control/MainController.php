<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/12/16
 * Time: 5:16 PM
 */

require("../Model/User.php");
require("../Model/Course.php");

session_start();

$user = $_SESSION["user"];
$newUser = $_SESSION["newUser"];
$course = $_SESSION["course"];
$do = $_GET["do"];

switch ($do) {
    case "newCourseAdministrator":
        // Save user in database
        //$user->saveInDatabase();
        header("Location: ../View/Home.php");
        break;
    case "newStudent":
        // Save user in database
        //$newUser->saveInDatabase();
        header("Location: ../View/Home.php");
        break;
    case "newTeacher":
        // Save user in database
        //$newUser->saveInDatabase();
        header("Location: ../View/Home.php");
        break;
    case "newCourse":
        // Save user in database
        //$course->saveInDatabase();
        header("Location: ../View/Home.php");
        break;
    case "login":
        header("Location: ../View/Home.php");
        break;
    case "logout":
        session_destroy();
        header("Location: ../View/Login.php");
        break;
}
?>