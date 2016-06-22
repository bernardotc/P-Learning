<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/17/16
 * Time: 11:59 AM
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

$image0 = $image1 = $image2 = $image3 = $image4 = null;
$image0ext = $image1ext = $image2ext = $image3ext = $image4ext = null;
$errorMessage = "";
if($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["delete"])) {
        $mysqli = new mysqli("localhost", "root", "", "p-learning");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        $statement = $mysqli->prepare("DELETE FROM Questions WHERE id = ?");
        $statement->bind_param("i", $_GET["id"]);
        $statement->execute();
        header("Location: ../View/EditMCQs.php");
    }
    $question = new Question($_POST["question"], $_POST["optionA"], $_POST["optionB"], $_POST["optionC"], $_POST["optionD"], $_POST["cAnswer"], $course->id);

    // REFERENCE: http://www.w3schools.com/php/php_file_upload.asp
    $uploadOk = 1;
    for ($i = 0; $i < 5; $i++) {
        $imageFileType = pathinfo(basename($_FILES["image" . $i]["name"]), PATHINFO_EXTENSION);
        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["image" . $i]["tmp_name"]);
        if ($check !== false) {
            //echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
            // Check file size
            if ($_FILES["image" . $i]["size"] > 10000000) {
                $errorMessage .= "Sorry, your file is too large. ";
                $uploadOk = 0;
            }
            // Allow certain file formats
            echo "IMAGE TYPE = " . $imageFileType;
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                $errorMessage .= "Sorry, only JPG, JPEG, PNG & GIF files are allowed. ";
                $uploadOk = 0;
            }
            // Check if $uploadOk is set to 0 by an error
            if ($uploadOk == 0) {
                $errorMessage .= "Your file was not uploaded. ";
                // if everything is ok, try to upload file
            } else {
                if ($i == 0) {
                    $question->questionI = file_get_contents($_FILES["image" . $i]["tmp_name"]);
                    $question->questionIExt = $imageFileType;
                } else if ($i == 1) {
                    $question->answerAI = file_get_contents($_FILES["image" . $i]["tmp_name"]);
                    $question->answerAIExt = $imageFileType;
                } else if ($i == 2) {
                    $question->answerBI = file_get_contents($_FILES["image" . $i]["tmp_name"]);
                    $question->answerBIExt = $imageFileType;
                } else if ($i == 3) {
                    $question->answerCI = file_get_contents($_FILES["image" . $i]["tmp_name"]);
                    $question->answerCIExt = $imageFileType;
                } else if ($i == 4) {
                    $question->answerDI = file_get_contents($_FILES["image" . $i]["tmp_name"]);
                    $question->answerDIExt = $imageFileType;
                }
            }
        }
    }

    if($uploadOk == 1) {
        $question->id = $_GET["id"];
        //echo '<img src="data:image/'.$question->questionIExt.';base64,'.base64_encode($question->questionI).'"/>';
        $mysqli = new mysqli("localhost", "root", "", "p-learning");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        $question->updateCompleteInDatabase($mysqli);
        $mysqli->close();
        header("Location: ../View/EditMCQs.php");
    }
}

$question = null;
$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$statement = $mysqli->prepare("SELECT * FROM Questions WHERE id = ?");
$statement->bind_param("i", $_GET["id"]);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
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
        min-height: 25%;
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
    <h1 id="title">Edit a Multiple Choice Question</h1>
    <h4><?php echo $course->code.' - '.$course->title; ?></h4>
    <hr/>
    <section class="col-lg-10">
        <form id="questionForm" enctype="multipart/form-data" method="post">
            <p style="color: #ffffff">In this space you can modify a question by changing its text and images.</p>
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" id="question" name="question" placeholder="Question related to the slide." value="<?php echo $question->question;?>" required/>
                <?php if($question->questionIExt != null) { ?>
                    <span class="input-group-btn"><button data-toggle="modal" data-target="#questionModal" type="button" class="btn btn-default">View Image</button></span>
                <?php } ?>
                <span class="input-group-addon"><input type="file" name="image0" id="image"></span>
            </div>
            <div class="input-group input-group-sm">
                <span class="input-group-addon">Correct Answer:</span>
                <span class="input-group-addon"><input class="form-control" type="radio" id="cAnswerA" name="cAnswer" value="A" style="width: 20px; height:18px;" <?php if ($question->correct == "A") echo "checked"?> required/></span>
                <input class="form-control" type="text" id="optionA" name="optionA" placeholder="Answer A" value="<?php echo $question->answerA;?>" required>
                <?php if($question->answerAIExt != null) { ?>
                    <span class="input-group-btn"><button data-toggle="modal" data-target="#answerAModal" type="button" class="btn btn-default">View Image</button></span>
                <?php } ?>
                <span class="input-group-addon"><input type="file" name="image1" id="image"></span>
            </div>
            <div class="input-group input-group-sm">
                <span class="input-group-addon">Correct Answer:</span>
                <span class="input-group-addon"><input class="form-control" type="radio" id="cAnswerB" name="cAnswer" value="B" style="width: 20px; height:18px;" <?php if ($question->correct == "B") echo "checked"?> required></span>
                <input class="form-control" type="text" id="optionB" name="optionB" placeholder="Answer B" value="<?php echo $question->answerB;?>" required>
                <?php if($question->answerBIExt != null) { ?>
                    <span class="input-group-btn"><button data-toggle="modal" data-target="#answerBModal" type="button" class="btn btn-default">View Image</button></span>
                <?php } ?>
                <span class="input-group-addon"><input type="file" name="image2" id="image"></span>
            </div>
            <div class="input-group input-group-sm">
                <span class="input-group-addon">Correct Answer:</span>
                <span class="input-group-addon"><input class="form-control" type="radio" id="cAnswerC" name="cAnswer" value="C" style="width: 20px; height:18px;" <?php if ($question->correct == "C") echo "checked"?> required/></span>
                <input class="form-control" type="text" id="optionC" name="optionC" placeholder="Answer C" value="<?php echo $question->answerC;?>" required>
                <?php if($question->answerCIExt != null) { ?>
                    <span class="input-group-btn"><button data-toggle="modal" data-target="#answerCModal" type="button" class="btn btn-default">View Image</button></span>
                <?php } ?>
                <span class="input-group-addon"><input type="file" name="image3" id="image"></span>
            </div>
            <div class="input-group input-group-sm">
                <span class="input-group-addon">Correct Answer:</span>
                <span class="input-group-addon"><input class="form-control" type="radio" id="cAnswerD" name="cAnswer" value="D" style="width: 20px; height:18px;" <?php if ($question->correct == "D") echo "checked"?> required/></span>
                <input class="form-control" type="text" id="optionD" name="optionD" placeholder="Answer D" value="<?php echo $question->answerD;?>" required>
                <?php if($question->answerDIExt != null) { ?>
                    <span class="input-group-btn"><button data-toggle="modal" data-target="#answerDModal" type="button" class="btn btn-default">View Image</button></span>
                <?php } ?>
                <span class="input-group-addon"><input type="file" name="image4" id="image"></span>
            </div>
            <div style="text-align: right">
                <span style="color: #ffffff">Note: Image size should be less than 10 MB.</span>
            </div>
            <h4><?php echo $errorMessage?></h4>
            <button id="submitForm" type="submit" style="display: none">Save</button>
        </form>
    </section>
    <aside class="col-lg-2">
        <br/>
        <div class="list-group">
            <a class="list-group-item" onclick="save()">
                <h5 class="list-group-item-heading">Save Question</h5>
            </a>
            <a class="list-group-item" onclick="deleteQuestion()">
                <h5 class="list-group-item-heading">Delete Question</h5>
            </a>
        </div>
    </aside>
    <div class="modal fade" id="questionModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3 class="modal-title">Question Image</h3>
                </div>
                <div class="modal-body" style="text-align: center;">
                    <img src="data:image/<?php echo $question->questionIExt; ?>;base64,<?php echo base64_encode($question->questionI);?>" width="500px"/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="answerAModal" style="text-align: center;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3 class="modal-title">Answer A Image</h3>
                </div>
                <div class="modal-body">
                    <img src="data:image/<?php echo $question->answerAIExt; ?>;base64,<?php echo base64_encode($question->answerAI);?>"  width="500px"/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="answerBModal" style="text-align: center;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3 class="modal-title">Answer B Image</h3>
                </div>
                <div class="modal-body">
                    <img src="data:image/<?php echo $question->answerBIExt; ?>;base64,<?php echo base64_encode($question->answerBI);?>"  width="500px"/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="answerCModal" style="text-align: center;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3 class="modal-title">Answer C Image</h3>
                </div>
                <div class="modal-body">
                    <img src="data:image/<?php echo $question->answerCIExt; ?>;base64,<?php echo base64_encode($question->answerCI);?>"  width="500px"/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="answerDModal" style="text-align: center;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h3 class="modal-title">Answer D Image</h3>
                </div>
                <div class="modal-body">
                    <img src="data:image/<?php echo $question->answerDIExt; ?>;base64,<?php echo base64_encode($question->answerDI);?>"  width="500px"/>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        function save() {
            $("#submitForm").click();
        }

        function deleteQuestion() {
            $("#questionForm").append($("<input>", {type: "hidden", name: "delete", value: "true"})).submit();
        }

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