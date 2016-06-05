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

$do = $_GET["do"];

switch ($do) {
    case "newCourseAdministrator":
        // Save user in database
        $_SESSION["user"]->saveInDatabase();
        header("Location: ../View/Home.php");
        break;
    case "newStudent":
        // Save user in database
        $_SESSION["newUser"]->saveInDatabase($_SESSION["user"]->institutionId);
        unset($_SESSION["newUser"]);
        header("Location: ../View/Home.php");
        break;
    case "newTeacher":
        // Save user in database
        $_SESSION["newUser"]->saveInDatabase($_SESSION["user"]->institutionId);
        unset($_SESSION["newUser"]);
        header("Location: ../View/Home.php");
        break;
    case "newCourse":
        $_SESSION["course"]->saveInDatabase($_SESSION["user"]->courseAdministratorId);
        unset($_SESSION["course"]);
        header("Location: ../View/Home.php");
        break;
    case "enrollment":
        header("Location: ../Control/EnrollTeachersAndStudents.php");
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