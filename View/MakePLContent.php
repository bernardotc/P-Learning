<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/7/16
 * Time: 4:02 PM
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //print_r($_POST);
    $slides = array();
    $numberOfSlides = $_POST["slides"];
    for ($i = 1; $i <= $numberOfSlides; $i++) {
        $question = new Question($_POST["question".$i], $_POST["optionA".$i], $_POST["optionB".$i], $_POST["optionC".$i], $_POST["optionD".$i], $_POST["cAnswer".$i], $course->id);
        if ($_POST["typeOfSlide".$i] == "image") {
            $imageFileType = pathinfo(basename($_FILES["slideContent".$i]["name"]), PATHINFO_EXTENSION);
            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES["slideContent".$i]["tmp_name"]);
            if ($check !== false) {
                //echo "File is an image - " . $check["mime"] . ".";
                $uploadOk = 1;
                // Check file size
                if ($_FILES["slideContent" . $i]["size"] > 10000000) {
                    //$errorMessage .= "Sorry, your file is too large. ";
                    $uploadOk = 0;
                }
                // Allow certain file formats
                //echo "IMAGE TYPE = " . $imageFileType;
                if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                    //$errorMessage .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed. ";
                    $uploadOk = 0;
                }
                // Check if $uploadOk is set to 0 by an error
                if ($uploadOk == 0) {

                    //$errorMessage .= "Your file was not uploaded. ";
                    // if everything is ok, try to upload file
                } else {
                    $imageContent = file_get_contents($_FILES["slideContent".$i]["tmp_name"]);
                    array_push($slides, new Slide($i, $_POST["typeOfSlide" . $i], null, $imageContent, $imageFileType, $question));
                }
            } else {
                array_push($slides, new Slide($i, $_POST["typeOfSlide" . $i], null, null, null, $question));
            }
        } else if ($_POST["typeOfSlide".$i] == "video") {
            $videoLink = str_replace("watch?v=", "embed/",  $_POST["slideContent".$i]);
            echo $videoLink;
            array_push($slides, new Slide($i, $_POST["typeOfSlide" . $i], $videoLink, null, null, $question));
        } else {
            array_push($slides, new Slide($i, $_POST["typeOfSlide" . $i], $_POST["slideContent" . $i], null, null, $question));
        }
    }
    $plcontent = new PLContent($_POST["contentTitle"], $slides, $_SESSION["courseSectionId"]);
    //print_r($_SESSION);
    //print_r($plcontent);
    if (isset($_SESSION["plcId"])) {
        $plcontent->id = $_SESSION["plcId"];
        $previousplc = $_SESSION["previousPLC"];
        for ($i = 0; $i < count($previousplc->slides); $i++) {
            $plcontent->slides[$i]->id = $previousplc->slides[$i]->id;
            $plcontent->slides[$i]->question->id = $previousplc->slides[$i]->question->id;
            $plcontent->slides[$i]->slideImage = $previousplc->slides[$i]->slideImage;
            $plcontent->slides[$i]->slideImageExt = $previousplc->slides[$i]->slideImageExt;

        }
        //print_r($plcontent);
        $_SESSION["plcontent"] = $plcontent;
        header("Location: ../Control/MainController.php?do=editPLContent");
    } else {
        $_SESSION["plcontent"] = $plcontent;
        header("Location: ../Control/MainController.php?do=newPLContent");
    }
}

if (isset($_SESSION["plcId"])) {
    $mysqli = new mysqli("localhost", "root", "", "p-learning");
    if ($mysqli->connect_errno) {
        echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }
    $plc = $question = null;
    $slides = array();
    $statement = $mysqli->prepare("SELECT * FROM PLContents WHERE id = ?");
    $statement->bind_param("i", $_SESSION["plcId"]);
    $statement->execute();
    $result = $statement->get_result();
    while ($row = $result->fetch_row()) {
        $i = $row[0];
        $t = $row[1];
        $csI = $row[2];
        $plc = new PLContent($i, $t, null, $csI);
    }
    $statement->close();
    $statement = $mysqli->prepare("SELECT * FROM Slides WHERE plcontentId = ?");
    $statement->bind_param("i", $plc->id);
    $statement->execute();
    $result = $statement->get_result();
    while ($row = $result->fetch_row()) {
        $sI = $row[0];
        $sN = $row[1];
        $sT = $row[2];
        $sC = $row[3];
        $sIm = $row[4];
        $sIE = $row[5];
        $qI = $row[6];

        $statement2 = $mysqli->prepare("SELECT * FROM Questions WHERE id = ?");
        $statement2->bind_param("i", $qI);
        $statement2->execute();
        $result2 = $statement2->get_result();
        while ($row = $result2->fetch_row()) {
            $i = $row[0];
            $q = $row[1];
            $a = $row[4];
            $b = $row[7];
            $c = $row[10];
            $d = $row[13];
            $cA = $row[16];
            $co = $row[17];

            $question = new Question($i, $q, $a, $b, $c, $d, $cA, $co);
        }
        array_push($slides, new Slide($sI, $sN, $sT, $sC, $sIm, $sIE, $question));
    }
    $plc->slides = $slides;

    $mysqli->close();
    $_SESSION["courseSectionId"] = $plc->courseSectionId;
    $_SESSION["previousPLC"] = $plc;
    print_r($_SESSION);
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
    #plform {
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
<h1 id="title">Making Programmed Learning Content</h1>
<h4><?php echo $course->code.' - '.$course->title; ?></h4>
<hr/>
<section class="col-lg-10">
    <form id="plform" method="post" enctype="multipart/form-data">
        <div class="input-group">
            <span class="input-group-addon">Content Title</span>
            <input class="form-control" type="text" id="contentTitle" name="contentTitle" required placeholder="e.g. Force, Synonyms, Division..." value="<?php if(isset($_SESSION["plcId"])) { echo $plc->contentTitle;}?>"/>
        </div>
        <hr/>
        <div id="slidesC">
            <?php $i = 0;
            if (isset($_SESSION["plcId"])) {
            foreach ($plc->slides as $slide) {
                $i++; ?>
                <fieldset id="slide<?php echo $i;?>" style="text-align: center">
                    <h3>Slide <?php echo $i;?></h3>
                    <?php if ($slide->slideType == "text") { ?>
                        <input type="hidden" name="typeOfSlide<?php echo $i;?>" value="text">
                        <div id="slideContent<?php echo $i;?>" class="col-lg-10" style="background-color: #ffffff; max-height: 400px;">
                            <?php echo $slide->slideContent;?>
                        </div>
                    <?php } else if ($slide->slideType == "video") { ?>
                        <div class="col-lg-6">
                            <iframe width="400" height="300px" src="<?php echo $slide->slideContent?>">
                            </iframe>
                        </div>
                        <input type="hidden" name="typeOfSlide<?php echo $i;?>" value="video">
                        <div class="col-lg-4">
                            <input type="text" class="form-control" name="slideContent<?php echo $i;?>" value="<?php echo $slide->slideContent?>" />
                        </div>
                    <?php } else if ($slide->slideType == "image") { ?>
                        <div class="col-lg-6">
                            <img src="data:image/<?php echo $slide->slideImageExt;?>;base64,<?php echo base64_encode($slide->slideImage);?>" width="400px"/>
                        </div>
                        <input type="hidden" name="typeOfSlide<?php echo $i;?>" value="image">
                        <div class="col-lg-4">
                            <input type="file" class="form-control" name="slideContent<?php echo $i;?>"/>
                        </div>
                    <?php } ?>
                    <div class="col-lg-2">
                        <button id="<?php echo $i;?>" type="button" class="btn btn-danger" style="margin-bottom: 2%" onclick="deleteSlide(this.id, <?php echo $_SESSION["plcId"];?>)">Delete</button>
                    </div>
                    <br/><br/>
                    <div id="questionContainer<?php echo $i;?>">
                        <input type="text" class="form-control" id="question<?php echo $i;?>" name="question<?php echo $i;?>" placeholder="Question related to the slide." required value="<?php echo $slide->question->question;?>"/>
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Correct Answer:</span>
                            <span class="input-group-addon"><input class="form-control" type="radio" id="cAnswerA<?php echo $i;?>" name="cAnswer<?php echo $i;?>" value="A" style="width: 20px; height:18px;" required <?php if ($slide->question->correct == "A") echo "checked"?>/></span>
                            <input class="form-control" type="text" id="optionA<?php echo $i;?>" name="optionA<?php echo $i;?>" placeholder="Answer A" required value="<?php echo $slide->question->answerA;?>">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Correct Answer:</span>
                            <span class="input-group-addon"><input class="form-control" type="radio" id="cAnswerB<?php echo $i;?>" name="cAnswer<?php echo $i;?>" value="B" style="width: 20px; height:18px;" required <?php if ($slide->question->correct == "B") echo "checked"?>/></span>
                            <input class="form-control" type="text" id="optionB<?php echo $i;?>" name="optionB<?php echo $i;?>" placeholder="Answer B" required value="<?php echo $slide->question->answerB;?>">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Correct Answer:</span>
                            <span class="input-group-addon"><input class="form-control" type="radio" id="cAnswerC<?php echo $i;?>" name="cAnswer<?php echo $i;?>" value="C" style="width: 20px; height:18px;" required <?php if ($slide->question->correct == "C") echo "checked"?>/></span>
                            <input class="form-control" type="text" id="optionC<?php echo $i;?>" name="optionC<?php echo $i;?>" placeholder="Answer C" required value="<?php echo $slide->question->answerC;?>">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-addon">Correct Answer:</span>
                            <span class="input-group-addon"><input class="form-control" type="radio" id="cAnswerD<?php echo $i;?>" name="cAnswer<?php echo $i;?>" value="D" style="width: 20px; height:18px;" required <?php if ($slide->question->correct == "D") echo "checked"?>/></span>
                            <input class="form-control" type="text" id="optionD<?php echo $i;?>" name="optionD<?php echo $i;?>" placeholder="Answer D" required value="<?php echo $slide->question->answerD;?>">
                        </div>
                    </div>
                    <hr/>
                </fieldset>
            <?php }} ?>
            <input type="hidden" id="slides" name="slides" value="<?php echo $i;?>">
        </div>
        <button type="submit">Save Content</button>
    </form>
</section>
<aside class="col-lg-2">
    <br/>
    <div class="list-group">
        <a class="list-group-item" onclick="addTextSlide()">
            <h5 class="list-group-item-heading">Add Text Slide</h5>
        </a>
        <a class="list-group-item" onclick="addVideoSlide()">
            <h5 class="list-group-item-heading">Add Video Slide</h5>
        </a>
        <a class="list-group-item" onclick="addImageSlide()">
            <h5 class="list-group-item-heading">Add Image Slide</h5>
        </a>
        <a class="list-group-item" onclick="save()">
            <h5 class="list-group-item-heading">Save Content</h5>
        </a>
    </div>
</aside>

<script src='//cdn.tinymce.com/4/tinymce.min.js'></script>
<script>
    var slideNumber = 0;
    var numberOfSlideToEditContent = 0;
    var textEditor;
    var slideIdToReload;
    function addTextSlide() {
        slideNumber++;
        var fieldset = $("<fieldset/>", {
            id: "slide" + slideNumber
        }).css("text-align", "center");
        var h3 = $("<h3/>", {
           text: "Slide " + slideNumber
        });
        var typeOfSlide = $("<input>", {
            type: "hidden",
            name: "typeOfSlide" + slideNumber,
            value: "text"
        }).css("display", "none");
        var container = $("<div/>", {
            id: "slideContent" + slideNumber,
            text: "Click to edit."
        }).addClass("col-lg-10").css("background-color", "#ffffff").css("max-height", "400px");
        /*var textarea = $("<textarea/>",
        {
            id: "slide" + slideNumber,
            name: "slide" + slideNumber
        });*/
        var buttonContainer = $("<div/>").addClass("col-lg-2");
        /*var buttonEdit = $("<button/>", {
            type: "button",
            text: "Edit",
            id: slideNumber
        }).addClass("btn btn-default").css("margin-bottom", "2%").css("margin-right", "2%").on("click", function(event) {editContent(this.id);});*/
        var buttonDelete = $("<button/>", {
            type: "button",
            text: "Delete",
            id: slideNumber
        }).addClass("btn btn-danger").css("margin-bottom", "2%").on("click", function(event) {deleteSlide(this.id);});
        fieldset.append(h3);
        fieldset.append(typeOfSlide);
        fieldset.append(container);
        fieldset.append(buttonContainer);
        //container.append(textarea);
        buttonContainer.append(buttonDelete);
        fieldset.append("<br/>").append("<br/>").append(addQuestion).append($("<hr/>"));
        $('#slidesC').append(fieldset);
        tinymce.init({
            selector: "#slideContent" + slideNumber,
            inline: true
        });
    }

    function addVideoSlide() {
        slideNumber++;
        var fieldset = $("<fieldset/>", {
            id: "slide" + slideNumber
        }).css("text-align", "center");
        var h3 = $("<h3/>", {
            text: "Slide " + slideNumber
        });
        var typeOfSlide = $("<input>", {
            type: "hidden",
            name: "typeOfSlide" + slideNumber,
            value: "video"
        }).css("display", "none");
        var container = $("<div/>", {
        }).addClass("col-lg-10");
        var slide = $("<input/>", {
            type: "text",
            id: "slideContent" + slideNumber,
            name: "slideContent" + slideNumber,
            placeholder: "Insert YouTube video link here."
        }).addClass("form-control");
        var buttonContainer = $("<div/>").addClass("col-lg-2");
        var buttonDelete = $("<button/>", {
            type: "button",
            text: "Delete",
            id: slideNumber
        }).addClass("btn btn-danger").css("margin-bottom", "2%").on("click", function(event) {deleteSlide(this.id);});
        fieldset.append(h3);
        fieldset.append(typeOfSlide);
        fieldset.append(container);
        container.append(slide);
        fieldset.append(buttonContainer);
        //container.append(textarea);
        buttonContainer.append(buttonDelete);
        fieldset.append("<br/>").append("<br/>").append(addQuestion).append($("<hr/>"));
        $('#slidesC').append(fieldset);
    }

    function addImageSlide() {
        slideNumber++;
        var fieldset = $("<fieldset/>", {
            id: "slide" + slideNumber
        }).css("text-align", "center");
        var h3 = $("<h3/>", {
            text: "Slide " + slideNumber
        });
        var typeOfSlide = $("<input>", {
            type: "hidden",
            name: "typeOfSlide" + slideNumber,
            value: "image"
        }).css("display", "none");
        var container = $("<div/>", {
        }).addClass("col-lg-10");
        var slide = $("<input/>", {
            type: "file",
            id: "slideContent" + slideNumber,
            name: "slideContent" + slideNumber,
            placeholder: "Insert YouTube video link here."
        }).addClass("form-control");
        var buttonContainer = $("<div/>").addClass("col-lg-2");
        var buttonDelete = $("<button/>", {
            type: "button",
            text: "Delete",
            id: slideNumber
        }).addClass("btn btn-danger").css("margin-bottom", "2%").on("click", function(event) {deleteSlide(this.id);});
        fieldset.append(h3);
        fieldset.append(typeOfSlide);
        fieldset.append(container);
        container.append(slide);
        fieldset.append(buttonContainer);
        //container.append(textarea);
        buttonContainer.append(buttonDelete);
        fieldset.append("<br/>").append("<br/>").append(addQuestion).append($("<hr/>"));
        $('#slidesC').append(fieldset);
    }

    function addQuestion() {
        var question = $("<input/>", {
            type: "text",
            id: "question" + slideNumber,
            name: "question" + slideNumber,
            placeholder: "Question related to the slide.",
            required: true
        }).addClass("form-control");
        var optionA = createAnswers("A");
        var optionB = createAnswers("B");
        var optionC = createAnswers("C");
        var optionD = createAnswers("D");
        return $("<div/>", {id: "questionContainer" + slideNumber}).append(question).append(optionA).append(optionB).append(optionC).append(optionD);
    }

    function createAnswers(option) {
        var div = $("<div/>").addClass("input-group input-group-sm");
        var spanText = $("<span/>", {
            text: "Correct Answer:"
        }).addClass("input-group-addon");
        var span = $("<span/>").addClass("input-group-addon");
        var radioButton = $("<input/>", {
            type: "radio",
            id: "cAnswer" + option + slideNumber,
            name: "cAnswer" + slideNumber,
            value: option,
            width: "20px",
            height: "18px",
            required: true
        }).addClass("form-control");
        var input = $("<input/>", {
            type: "text",
            id: "option" + option + slideNumber,
            name: "option" + option + slideNumber,
            placeholder: "Answer " + option,
            required: true
        }).addClass("form-control");
        div.append(spanText);
        div.append(span);
        span.append(radioButton);
        div.append(input);
        return div;
    }

    function reloadPage(response) {
        //alert(response);
        if (response == "deletedAndUpdatedSlides") {
            window.location = "../View/MakePLContent.php#" + slideIdToReload;
            location.reload();
        }
    }

    function deleteSlide(numberOfSlide, plcId) {
        //alert(numberOfSlide);
        slideIdToReload = numberOfSlide;
        $.ajax({
            type : "post",
            url : "../Control/UpdateSlidesAndQuestions.php",
            data : {
                "slideNumber": numberOfSlide,
                "plcId": plcId,
                "do": "deleteAndUpdateSlideNumbers"
            },
            success : reloadPage,
            error : function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
            }
        });
        tinymce.editors[numberOfSlide - 1].destroy();
        $("#slide" + numberOfSlide).remove();
        for (var i = parseInt(numberOfSlide) + 1; i <= slideNumber; i++) {
            $("#slide" + i + " h3").html("Slide " + (i - 1));
            $("#slide" + i).attr("id", "slide" + (i - 1));
            $("#slideContent" + i).attr("id", "slideContent" + (i - 1));
            $("#questionContainer" + i).attr("id", "questionContainer" + (i - 1));
            $("#" + i).attr("id", (i - 1));
            $("#question" + i).attr("id", "question" + (i - 1)).attr("name", "question" + (i - 1));
            $("#cAnswerA" + i).attr("id", "cAnswerA" + (i - 1)).attr("name", "cAnswerA" + (i - 1));
            $("#cAnswerB" + i).attr("id", "cAnswerB" + (i - 1)).attr("name", "cAnswerB" + (i - 1));
            $("#cAnswerC" + i).attr("id", "cAnswerC" + (i - 1)).attr("name", "cAnswerC" + (i - 1));
            $("#cAnswerD" + i).attr("id", "cAnswerD" + (i - 1)).attr("name", "cAnswerD" + (i - 1));
            $("#optionA" + i).attr("id", "optionA" + (i - 1)).attr("name", "optionA" + (i - 1));
            $("#optionB" + i).attr("id", "optionB" + (i - 1)).attr("name", "optionB" + (i - 1));
            $("#optionC" + i).attr("id", "optionC" + (i - 1)).attr("name", "optionC" + (i - 1));
            $("#optionD" + i).attr("id", "optionD" + (i - 1)).attr("name", "optionD" + (i - 1));
        }
        slideNumber--;
    }

    function editContent(numberOfSlide) {
        textEditor = tinymce.init({
            selector: "#slideContent",
            height: "300px",
            inline: true
        });
        $("#slideModal").modal();
        numberOfSlideToEditContent = numberOfSlide;
    }

    function save() {
        $("#slides")[0].value = slideNumber;
        $("button[type='submit']").click();
    }

    $("#slides").ready(function() {
        slideNumber = parseInt($("#slides")[0].value);
        for (var i = 1; i <= slideNumber; i++) {
            tinymce.init({
                selector: "#slideContent" + i,
                inline: true
            });
        }
    });

    /*$(document).ready(function()
    {
        $('#plform').submit(function(event){
            if(!this.checkValidity())
            {
                event.preventDefault();
                $('#plform :input:visible[required="required"]').each(function()
                {
                    if(!this.validity.valid)
                    {
                        $(this).focus();
                        // break
                        return false;
                    }
                });
            }
        });
    });*/

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