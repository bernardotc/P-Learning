<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/4/16
 * Time: 3:53 PM
 */

session_start();

$teachersId = $_SESSION["newTeachers"];
$studentsId = $_SESSION["newStudents"];
$courseId = $_SESSION["courseId"];

$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$mysqli->begin_transaction();
$statement = $mysqli->prepare("DELETE FROM Enroll WHERE courseId = ?");
$statement->bind_param('i', $courseId);
$statement->execute();
if ($statement->errno != 0) {
    $mysqli->rollback();
} else {
    $statement->close();
    $statement = $mysqli->prepare("DELETE FROM Teaching WHERE courseId = ?");
    $statement->bind_param('i', $courseId);
    $statement->execute();
    if ($statement->errno != 0) {
        $mysqli->rollback();
    } else {
        $statement->close();
        for ($i = 0; $i < count($studentsId); $i++) {
            $statement = $mysqli->prepare("INSERT INTO Enroll (studentId, courseId) VALUES (?, ?)");
            $statement->bind_param('ii', $studentsId[$i], $courseId);
            $statement->execute();
            if ($statement->errno != 0) {
                break;
            }
            $statement->close();
        }
        if ($statement->errno != 0) {
            $mysqli->rollback();
            $statement->close();
        } else {
            for ($i = 0; $i < count($teachersId); $i++) {
                $statement = $mysqli->prepare("INSERT INTO Teaching (teacherId, courseId) VALUES (?, ?)");
                $statement->bind_param('ii', $teachersId[$i], $courseId);
                $statement->execute();
                if ($statement->errno != 0) {
                    break;
                }
                $statement->close();
            }
            if ($statement->errno != 0) {
                $mysqli->rollback();
            } else {
                $mysqli->commit();
            }
        }
    }
}

$mysqli->close();
unset($_SESSION["newTeachers"]);
unset($_SESSION["newStudents"]);
unset($_SESSION["courseId"]);
header("Location: ../View/Home.php");
?>