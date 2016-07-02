<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/24/16
 * Time: 1:27 PM
 */

require ("../Model/User.php");
require ("../Model/Course.php");
require ("../Model/PLContent.php");

session_start();

$questions = $_SESSION["questionsInsideTest"];
foreach ($questions as $question) {
    if ($question->id == $_POST["questionId"]) {
        $ext = $_POST["imageId"].'Ext';
        echo '<img src="data:image/'.$question->$ext.';base64,'.base64_encode($question->$_POST["imageId"]).'"  width="500px"/>';
        break;
    }
}
?>