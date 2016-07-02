<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/27/16
 * Time: 12:34 PM
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
} else if (!isset($_SESSION["plcId"])) {
    header("Location: ../View/CourseHome.php");
}

$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
if (!isset($_SESSION["plc"])) {
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
            $question->questionI = $row[3];
            $question->questionIExt = $row[2];
            $question->answerAI = $row[6];
            $question->answerAIExt = $row[5];
            $question->answerBI = $row[9];
            $question->answerBIExt = $row[8];
            $question->answerCI = $row[12];
            $question->answerCIExt = $row[11];
            $question->answerDI = $row[15];
            $question->answerDIExt = $row[14];
        }
        array_push($slides, new Slide($sI, $sN, $sT, $sC, $sIm, $sIE, $question));
    }
    $plc->slides = $slides;
    $_SESSION["plc"] = $plc;
} else {
    $slide = null;
    $plc = $_SESSION["plc"];

    if($_GET["slide"] > count($plc->slides)) {
        header("Location: ../View/CourseHome.php");
    }

    $statement = $mysqli->prepare("SELECT slide FROM ProgressOfPLC WHERE plcId = ? AND studentId = ?");
    $statement->bind_param("ii", $_SESSION["plcId"], $user->studentId);
    $statement->execute();
    $result = $statement->get_result();
    while ($row = $result->fetch_row()) {
        $slide = $row[0];
    }
    if ($_GET["slide"] == 1 && $slide == null) {
        $statement->close();
        $statement = $mysqli->prepare("INSERT IGNORE INTO ProgressOfPLC (plcId, studentId, slide, lastDay) VALUES (?, ?, ?, ?)");
        $statement->bind_param("iiis", $_SESSION["plcId"], $user->studentId, $_GET["slide"], date("Y-m-d"));
        $statement->execute();
    } else {
        if ($_GET["slide"] > $slide) {
            $statement->close();
            $statement = $mysqli->prepare("UPDATE ProgressOfPLC SET slide = ?, lastDay = ? WHERE plcId = ? AND studentId = ?");
            $statement->bind_param("isii", $_GET["slide"], date("Y-m-d"), $_SESSION["plcId"], $user->studentId);
            $statement->execute();
        }
    }
}
$mysqli->close();
?>

<?php include 'Header.php';?>
<h1 id="title">Programmed Learning Content: <?php echo $plc->contentTitle; ?></h1>
<h4><?php echo $course->code.' - '.$course->title; ?></h4>
<hr/>
<?php if (!isset($_GET["slide"])) { ?>
    <div style="text-align: center">
        <a class="btn btn-primary btn-lg" href="../View/ViewPLContent.php?slide=1&question=false">Start Content</a>
    </div>
<?php } else {
    if ($_GET["question"] == "false") { ?>
        <?php if ($plc->slides[$_GET["slide"] - 1]->slideType == "text") { ?>
            <div style="background-color: #ffffff" class="col-lg-12">
                <?php echo $plc->slides[$_GET["slide"] - 1]->slideContent;?>
            </div>
        <?php } ?>
        <?php if ($plc->slides[$_GET["slide"] - 1]->slideType == "image") { ?>
            <div style="text-align: center" class="col-lg-12">
                <img src="data:image/<?php echo $plc->slides[$_GET["slide"] - 1]->slideImageExt;?>;base64,<?php echo base64_encode($plc->slides[$_GET["slide"] - 1]->slideImage);?>" width="800px"/>
            </div>
        <?php } ?>
        <?php if ($plc->slides[$_GET["slide"] - 1]->slideType == "video") { ?>
            <div style="text-align: center" class="col-lg-12">
                <iframe width="800" height="600" src="<?php echo $plc->slides[$_GET["slide"] - 1]->slideContent?>">
                </iframe>
            </div>
        <?php } ?>
    <?php } else { ?>
        <form method="post" onsubmit="return false;">
            <div id="question">
                <div class="input-group" style="width: 100%;">
                    <label class="input-group-addon" style="width:100%"><?php echo $plc->slides[$_GET["slide"] - 1]->question->question; ?></label>
                    <?php if ($plc->slides[$_GET["slide"] - 1]->question->questionIExt != null) { ?>
                        <span class="input-group-btn" style="display: inline-block;"><button type="button" class="btn btn-default" onclick="viewImage(<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>, 'questionI')">View Image</button></span>
                    <?php } ?>
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><input type="radio" name="answer<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>" value="A" id="answerA<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>" required></span>
                    <label for="answerA<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>" class="form-control"><?php echo $plc->slides[$_GET["slide"] - 1]->question->answerA; ?></label>
                    <?php if ($plc->slides[$_GET["slide"] - 1]->question->answerAIExt != null) { ?>
                        <span class="input-group-btn"><button type="button" class="btn btn-default" onclick="viewImage(<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>, 'answerAI')">View Image</button></span>
                    <?php } ?>
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><input type="radio" name="answer<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>" value="B" id="answerB<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>" required></span>
                    <label for="answerB<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>" class="form-control"><?php echo $plc->slides[$_GET["slide"] - 1]->question->answerB; ?></label>
                    <?php if ($plc->slides[$_GET["slide"] - 1]->question->answerBIExt != null) { ?>
                        <span class="input-group-btn"><button type="button" class="btn btn-default" onclick="viewImage(<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>, 'answerBI')">View Image</button></span>
                    <?php } ?>
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><input type="radio" name="answer<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>" value="C" id="answerC<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>" required></span>
                    <label for="answerC<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>" class="form-control"><?php echo $plc->slides[$_GET["slide"] - 1]->question->answerC; ?></label>
                    <?php if ($plc->slides[$_GET["slide"] - 1]->question->answerCIExt != null) { ?>
                        <span class="input-group-btn"><button type="button" class="btn btn-default" onclick="viewImage(<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>, 'answerCI')">View Image</button></span>
                    <?php } ?>
                </div>
                <div class="input-group">
                    <span class="input-group-addon"><input type="radio" name="answer<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>" value="D" id="answerD<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>" required></span>
                    <label for="answerD<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>" class="form-control"><?php echo $plc->slides[$_GET["slide"] - 1]->question->answerD; ?></label>
                    <?php if ($plc->slides[$_GET["slide"] - 1]->question->answerDIExt != null) { ?>
                        <span class="input-group-btn"><button type="button" class="btn btn-default" onclick="viewImage(<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>, 'answerDI')">View Image</button></span>
                    <?php } ?>
                </div>
                <input type="hidden" style="display: none" name="slide" id="slide" value="<?php echo $_GET["slide"]; ?>"/>
                <input type="hidden" style="display: none" name="correctAnswer" id="correctAnswer" value="<?php echo $plc->slides[$_GET["slide"] - 1]->question->correct; ?>"/>
                <br/>
            </div>
            <button type="submit" id="submitForm" style="display: none" onclick="submitAnswer('answer<?php echo $plc->slides[$_GET["slide"] - 1]->question->id; ?>')">Submit</button>
        </form>
        <h2 style="text-align: center" id="answerMessage"></h2>
        <div id="btnContainer" style="text-align: center">
            <button type="button" class="btn btn-primary btn-lg" onclick="submitForm()">Submit Answer</button>
        </div>
    <?php } ?>
    <div style="text-align: center;">
        <ul class="pagination">
            <li class="<?php if ($_GET["slide"] == 1) echo 'disabled'; ?>"><a href="<?php if ($_GET["slide"] != 1) echo '../View/ViewPLContent.php?slide='.($_GET["slide"] - 1).'&question=false'; ?>">&laquo;</a></li>
            <?php
            $x = $_GET["slide"] - 10;
            if ($x <= 0)
                $x = 1;
            $maxPagination = $x + 10;
            if ($maxPagination > count($plc->slides))
                $maxPagination = count($plc->slides);
            for ($x = 1; $x <= $maxPagination; $x++) { ?>
                <li class="<?php if ($x == $_GET["slide"]) echo 'active'; else if ($x > $slide) echo 'disabled';?>"><a href="<?php if ($x <= $slide) echo '../View/ViewPLContent.php?slide='.$x.'&question=false';?>"><?php echo $x; ?></a></li>
            <?php } ?>
            <li class="<?php if ($_GET["slide"] + 1 > count($plc->slides) && $_GET["question"] != "false") echo 'disabled'; ?>"><a href="<?php if ($_GET["slide"] + 1 <= count($plc->slides) || $_GET["question"] == "false") echo '../View/ViewPLContent.php?slide='.$_GET["slide"].'&question=true'; ?>">&raquo;</a></li>
        </ul>
    </div>
<?php } ?>
    <div class="modal fade" id="imageModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3 class="modal-title">Image</h3>
                </div>
                <div class="modal-body" style="text-align: center;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function displayModal(response) {
            //alert(response);
            $(".modal-body").html(response);
            $("#imageModal").modal();
        }

        function viewImage(questionId, imageId) {
            $.ajax({
                type : "post",
                url : "../Control/GetImage.php",
                data : {
                    "questionId": questionId,
                    "imageId": imageId
                },
                success: displayModal,
                error : function(jqXHR, textStatus, errorMessage) {
                    console.log(errorMessage);
                }
            });
        }

        function submitAnswer(radio) {
            var optionSelected = $("input[name=" + radio + "]:checked").val();
            var correct = $("#correctAnswer").val();
            var previousSlide = null;
            if (optionSelected == correct) {
                $("#answerMessage").html("Correct Answer!");
            } else {
                $("#answerMessage").html("That is not correct! Maybe you should go back to the slide before continuing.");
                previousSlide = $("<a>", {
                    text: "Go Back",
                    href: '../View/ViewPLContent.php?slide=' + (parseInt($("#slide").val())) + '&question=false'
                }).addClass("btn btn-default btn-lg").css("margin-right", "5%");
            }
            var nextSlide = $("<a>", {
                text: "Continue",
                href: '../View/ViewPLContent.php?slide=' + (parseInt($("#slide").val()) + 1) + '&question=false'
            }).addClass("btn btn-primary btn-lg");
            $("#btnContainer").html(previousSlide).append(nextSlide);
        }

        function submitForm() {
            $("#submitForm").click();
        }
    </script>
<?php include 'Footer.php';?>