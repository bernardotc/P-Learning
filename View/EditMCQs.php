<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/17/16
 * Time: 11:31 AM
 */
require ("../Model/User.php");
require ("../Model/Course.php");
require ("../Model/PLContent.php");

session_start();

$login = '';
$signin = '';
$home = 'class=""';
$courseActive = 'class=""';

$user = $_SESSION["user"];
$course = $_SESSION["course"];
if ($user == null) {
    header("Location: ../Control/MainController.php?do=logout");
} else if ($course == null) {
    header("Location: ../View/Home.php");
}
?>
<?php include 'Header.php';?>
<h1 id="title">Edit a Multiple Choice Question</h1>
<h4><?php echo $course->code.' - '.$course->title; ?></h4>
<hr/>
<p>Select one question from the following list:</p>
<input id="questionFilter" type="text" class="form-control" placeholder="Filter by question" onkeydown="getQuestions()"/>
<div class="list-group" id="questions"></div>
<script>
    function insertQuestions(response) {
        $("#questions").html(response);
    }

    function getQuestions() {
        var val = $("#questionFilter")[0].value;
        $.ajax({
            type : "post",
            url : "../Control/GetQuestions.php",
            data : {
                "question": val,
                "do": "getQuestions"
            },
            success : insertQuestions,
            error : function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
            }
        });
    }

    $("#questions").ready(getQuestions());
</script>
<?php include 'Footer.php';?>