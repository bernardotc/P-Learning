<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/14/16
 * Time: 12:57 PM
 */
$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$mysqli->autocommit(true);
if ($_POST["do"] == "save") {
    $statement = $mysqli->prepare("INSERT INTO Announcements (announcementTitle, announcementBody, announcementMadeDay, announcementLastDay, course) VALUES (?, ?, ?, ?, ?)");
    $statement->bind_param('ssssi', $_POST["announcementTitle"], $_POST["announcementBody"], date('Y-m-d'), $_POST["announcementLastDay"],$_POST["courseId"]);
    $statement->execute();
    echo "saved";
} else if ($_POST["do"] == "delete") {
    $statement = $mysqli->prepare("DELETE FROM Announcements WHERE id = ?");
    $statement->bind_param('i', $_POST["announcementId"]);
    $statement->execute();
    echo "deleted";
}
$mysqli->close();
?>