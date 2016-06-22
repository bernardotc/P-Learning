<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/20/16
 * Time: 4:30 PM
 */

$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$mysqli->autocommit(true);
if ($_POST["do"] == "changeTestSection") {
    $statement = $mysqli->prepare("UPDATE Tests SET CourseSectionId = ? WHERE id = ?");
    $statement->bind_param('ii', $_POST["sectionId"], $_POST["testId"]);
    $statement->execute();
    echo "updatedTest";
} else if ($_POST["do"] == "deleteTest") {
    $statement = $mysqli->prepare("DELETE FROM Tests WHERE id = ?");
    $statement->bind_param('i', $_POST["testId"]);
    $statement->execute();
    echo "deletedTest";
}

$mysqli->close();
?>