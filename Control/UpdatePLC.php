<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/14/16
 * Time: 12:15 PM
 */

$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$mysqli->autocommit(true);
if ($_POST["do"] == "changePLCSection") {
    $statement = $mysqli->prepare("UPDATE PLContents SET CourseSectionId = ? WHERE id = ?");
    $statement->bind_param('ii', $_POST["sectionId"], $_POST["plcId"]);
    $statement->execute();
    echo "updatedPLC";
} else if ($_POST["do"] == "deletePLC") {
    $statement = $mysqli->prepare("DELETE FROM PLContents WHERE id = ?");
    $statement->bind_param('i', $_POST["plcId"]);
    $statement->execute();
    echo "deletedPLC";
}

$mysqli->close();
?>