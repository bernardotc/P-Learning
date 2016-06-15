<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/15/16
 * Time: 12:58 PM
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
<style>
    aside {
        display: block;
        background-color: #2c68b2;
        border-color: #255897;
    }
    .list-group {
        text-align: center;
    }
    section {
        display: block;
    }
    #questionForm {
        background-color: #8dbdd9;
        border: 1px solid;
        border-color: #255897;
        min-height: 45%;
        padding-top: 1%;
        padding-left: 1%;
        padding-right: 1%;
        text-align: left;
        overflow: hidden;
    }
    .list-group > a {
        cursor: pointer;
    }
    form > button {
        float: right;
        display:none;
        height: 0;
    }
</style>
<?php include 'Header.php';?>
<h1 id="title">Creating a Multiple Choice Question</h1>
<h4><?php echo $course->code.' - '.$course->title; ?></h4>
<hr/>
<section class="col-lg-10">
    <form id="questionForm">
        <input type="text" class="form-control" id="question" name="question" placeholder="Question related to the slide." required/>
        <div class="input-group input-group-sm">
            <span class="input-group-addon">Correct Answer:</span>
            <span class="input-group-addon"><input class="form-control" type="radio" id="cAnswerA" name="cAnswer" value="A" style="width: 20px; height:18px;" required/></span>
            <input class="form-control" type="text" id="optionA" name="optionA" placeholder="Answer A" required>
        </div>
        <div class="input-group input-group-sm">
            <span class="input-group-addon">Correct Answer:</span>
            <span class="input-group-addon"><input class="form-control" type="radio" id="cAnswerB" name="cAnswer" value="B" style="width: 20px; height:18px;" required/></span>
            <input class="form-control" type="text" id="optionB" name="optionB" placeholder="Answer B" required>
        </div>
        <div class="input-group input-group-sm">
            <span class="input-group-addon">Correct Answer:</span>
            <span class="input-group-addon"><input class="form-control" type="radio" id="cAnswerC" name="cAnswer" value="C" style="width: 20px; height:18px;" required/></span>
            <input class="form-control" type="text" id="optionC" name="optionC" placeholder="Answer C" required>
        </div>
        <div class="input-group input-group-sm">
            <span class="input-group-addon">Correct Answer:</span>
            <span class="input-group-addon"><input class="form-control" type="radio" id="cAnswerD" name="cAnswer" value="D" style="width: 20px; height:18px;" required/></span>
            <input class="form-control" type="text" id="optionD" name="optionD" placeholder="Answer D" required>
        </div>
    </form>
</section>
<aside class="col-lg-2">
    <br/>
    <div class="list-group">
        <a class="list-group-item" onclick="addSlide()">
            <h5 class="list-group-item-heading">Add Text MCQ</h5>
        </a>
        <a class="list-group-item" onclick="addSlide()">
            <h5 class="list-group-item-heading">Add Image MCQ</h5>
        </a>
        <a class="list-group-item" onclick="save()">
            <h5 class="list-group-item-heading">Save Question</h5>
        </a>
    </div>
</aside>
<script>
    // REFERENCE: http://stackoverflow.com/questions/20327505/navbar-stick-to-top-of-screen-when-scrolling-past
    $(function() {
        // grab the initial top offset of the navigation
        var sticky_navigation_offset_top = $('aside').offset().top;

        // our function that decides weather the navigation bar should have "fixed" css position or not.
        var sticky_navigation = function(){
            var scroll_top = $(window).scrollTop(); // our current vertical position from the top

            // if we've scrolled more than the navigation, change its position to fixed to stick to top, otherwise change it back to relative
            if (scroll_top > sticky_navigation_offset_top) {
                $('aside').css({ 'position': 'fixed', 'top':0, 'right':0 });
            } else {
                $('aside').css({ 'position': 'relative' });
            }
        };

        // run our function on load
        sticky_navigation();

        // and run it again every time you scroll
        $(window).scroll(function() {
            sticky_navigation();
        });
    });
</script>
<?php include 'Footer.php';?>