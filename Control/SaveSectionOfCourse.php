<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/6/16
 * Time: 12:56 PM
 */

$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$mysqli->autocommit(true);
if ($_POST["do"] == "save") {
    $statement = $mysqli->prepare("INSERT INTO CourseSections (sectionTitle, sectionDescription, course) VALUES (?, ?, ?)");
    $statement->bind_param('ssi', $_POST["sectionTitle"], $_POST["sectionDescription"], $_POST["courseId"]);
    $statement->execute();
    echo "saved";
} else if ($_POST["do"] == "edit") {
    $statement = $mysqli->prepare("UPDATE CourseSections SET sectionTitle = ?, sectionDescription = ? WHERE id = ?");
    $statement->bind_param('ssi', $_POST["sectionTitle"], $_POST["sectionDescription"], $_POST["sectionId"]);
    $statement->execute();
    echo "edited";
} else if ($_POST["do"] == "delete") {
    $statement = $mysqli->prepare("DELETE FROM CourseSections WHERE id = ?");
    $statement->bind_param('i', $_POST["sectionId"]);
    $statement->execute();
    echo "delited";
}
$mysqli->close();
?>