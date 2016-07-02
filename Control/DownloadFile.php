<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/22/16
 * Time: 12:57 PM
 */
require ("../Model/Course.php");

// REFERENCE: http://www.php-mysql-tutorial.com/wikis/mysql-tutorials/uploading-files-to-mysql-database.aspx
if(isset($_GET['id']))
{
    // if id is set then get the file with the id from database
    $file = null;
    $mysqli = new mysqli("localhost", "root", "", "p-learning");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
    $statement = $mysqli->prepare("SELECT * FROM Files WHERE id = ?");
    $statement->bind_param("i", $_GET['id']);
    $statement->execute();
    $result = $statement->get_result();
    while ($row = $result->fetch_row()) {
        $i = $row[0];
        $f = $row[1];
        $fN = $row[2];
        $fE = $row[3];
        $cs = $row[4];
        $file = new File($i, $f, $fN, $fE, $cs);
    }

    header("Content-length: ".strlen($file->file));
    header("Content-type: ".$file->fileExt);
    header("Content-Disposition: attachment; filename=".$file->fileName);
    echo $file->file;

    exit;
} else if (isset($_GET['studentId'])) {
    $file = null;
    $mysqli = new mysqli("localhost", "root", "", "p-learning");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
    $statement = $mysqli->prepare("SELECT submissionFile, submissionFileName, submissionFileExt FROM AssignmentSubmissions WHERE studentId = ? AND assignmentId = ?");
    $statement->bind_param("ii", $_GET['studentId'], $_GET["assignmentId"]);
    $statement->execute();
    $result = $statement->get_result();
    while ($row = $result->fetch_row()) {
        $f = $row[0];
        $fN = $row[1];
        $fE = $row[2];
        $file = new File(null, $f, $fN, $fE, null);
    }

    header("Content-length: ".strlen($file->file));
    header("Content-type: ".$file->fileExt);
    header("Content-Disposition: attachment; filename=".$file->fileName);
    echo $file->file;

    exit;
}

?>