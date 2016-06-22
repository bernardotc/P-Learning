<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/12/16
 * Time: 5:16 PM
 */

require("../Model/User.php");
require("../Model/Course.php");
require("../Model/PLContent.php");

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
        $_SESSION["newCourse"]->saveInDatabase($_SESSION["user"]->courseAdministratorId);
        unset($_SESSION["newCourse"]);
        header("Location: ../View/Home.php");
        break;
    case "editPLContent":
        $_SESSION["plcontent"]->updateInDatabase();
        unset($_SESSION["courseSectionId"]);
        unset($_SESSION["plcontent"]);
        unset($_SESSION["previousPLC"]);
        unset($_SESSION["plcId"]);
        header("Location: ../View/CourseHome.php");
        break;
    case "newPLContent":
        $_SESSION["plcontent"]->saveInDatabase();
        unset($_SESSION["courseSectionId"]);
        unset($_SESSION["plcontent"]);
        header("Location: ../View/CourseHome.php");
        break;
    case "showCourse":
        $_SESSION["courseId"] = $_GET["courseId"];
        header("Location: ../View/CourseHome.php");
        break;
    case "makeplc":
        if (!isset($_SESSION["course"])) {
            header("Location: ../View/Home.php");
        }
        $_SESSION["courseSectionId"] = $_GET["courseSectionId"];
        unset($_SESSION["plcId"]);
        header("Location: ../View/MakePLContent.php");
        break;
    case "makeTest":
        if (!isset($_SESSION["course"])) {
            header("Location: ../View/Home.php");
        }
        $_SESSION["courseSectionId"] = $_GET["courseSectionId"];
        header("Location: ../View/NewTest.php");
        break;
    case "makeAssignment":
        if (!isset($_SESSION["course"])) {
            header("Location: ../View/Home.php");
        }
        $_SESSION["courseSectionId"] = $_GET["courseSectionId"];
        header("Location: ../View/NewAssignment.php");
        break;
    case "editplc":
        if (!isset($_SESSION["course"])) {
            header("Location: ../View/Home.php");
        }
        $_SESSION["plcId"] = $_GET["plcId"];
        header("Location: ../View/MakePLContent.php");
        break;
    case "editTest":
        if (!isset($_SESSION["course"])) {
            header("Location: ../View/Home.php");
        }
        $_SESSION["testId"] = $_GET["testId"];
        header("Location: ../View/EditTest.php");
        break;
    case "editAssignment":
        if (!isset($_SESSION["course"])) {
            header("Location: ../View/Home.php");
        }
        $_SESSION["assignmentId"] = $_GET["assignmentId"];
        header("Location: ../View/EditAssignment.php");
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