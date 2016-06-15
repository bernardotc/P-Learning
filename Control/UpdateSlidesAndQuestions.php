<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/15/16
 * Time: 11:41 AM
 */
$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$mysqli->autocommit(true);
if ($_POST["do"] == "deleteAndUpdateSlideNumbers") {
    $maxSlideNumber = 0;
    $statement = $mysqli->prepare("DELETE FROM Questions WHERE id IN (SELECT questionId FROM Slides WHERE slideNumber = ? AND plcontentId = ?)");
    //echo $_POST["slideNumber"]."/".$_POST["plcId"];
    $statement->bind_param('ii', $_POST["slideNumber"], $_POST["plcId"]);
    $statement->execute();
    $statement->close();

    $statement = $mysqli->prepare("DELETE FROM Slides WHERE slideNumber = ? AND plcontentId = ?");
    $statement->bind_param('ii', $_POST["slideNumber"], $_POST["plcId"]);
    $statement->execute();
    $statement->close();

    $statement = $mysqli->prepare("SELECT MAX(slideNumber) FROM Slides WHERE plcontentId = ?");
    $statement->bind_param('i', $_POST["plcId"]);
    $statement->execute();
    $result = $statement->get_result();
    while ($row = $result->fetch_row()) {
        $maxSlideNumber = $row[0];
    }
    for ($i = $_POST["slideNumber"] + 1; $i <= $maxSlideNumber; $i++) {
        $statement->close();
        $statement = $mysqli->prepare("UPDATE Slides SET slideNumber = ? WHERE slideNumber = ?");
        $iBefore = $i - 1;
        $statement->bind_param('ii', $iBefore, $i);
        $statement->execute();
    }
    echo "deletedAndUpdatedSlides";
}

$mysqli->close();
?>