<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/21/16
 * Time: 1:25 PM
 */

$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$mysqli->autocommit(true);
if ($_POST["do"] == "changeAssignmentSection") {
    $statement = $mysqli->prepare("UPDATE Assignments SET CourseSectionId = ? WHERE id = ?");
    $statement->bind_param('ii', $_POST["sectionId"], $_POST["assignmentId"]);
    $statement->execute();
    echo "updatedAssignment";
} else if ($_POST["do"] == "deleteAssignment") {
    $statement = $mysqli->prepare("DELETE FROM Assignments WHERE id = ?");
    $statement->bind_param('i', $_POST["assignmentId"]);
    $statement->execute();
    echo "deletedAssignment";
}

$mysqli->close();
?>