<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/22/16
 * Time: 1:30 PM
 */

$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$mysqli->autocommit(true);
if ($_POST["do"] == "changeFileSection") {
    $statement = $mysqli->prepare("UPDATE Files SET CourseSectionId = ? WHERE id = ?");
    $statement->bind_param('ii', $_POST["sectionId"], $_POST["fileId"]);
    $statement->execute();
    echo "updatedFile";
} else if ($_POST["do"] == "deleteFile") {
    $statement = $mysqli->prepare("DELETE FROM Files WHERE id = ?");
    $statement->bind_param('i', $_POST["fileId"]);
    $statement->execute();
    echo "deletedFile";
}

$mysqli->close();
?>