<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 6/6/16
 * Time: 12:11 PM
 */
require ("../Model/User.php");
require ("../Model/Course.php");
require ("../Model/PLContent.php");
session_start();

unset($_SESSION["questionsInTest"]);

$login = '';
$signin = '';
$home = 'class=""';
$courseActive = 'class="active"';

$user = $_SESSION["user"];
$courseId = $_SESSION["courseId"];
if ($user == null) {
    header("Location: ../Control/MainController.php?do=logout");
} else if ($courseId == null) {
    header("Location: ../View/Home.php");
}

$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
$course = null;
$sections = array();
$announcements = array();
$statement = $mysqli->prepare("SELECT * FROM Courses WHERE id = ?");
$statement->bind_param("i", $courseId);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $i = $row[0];
    $t = $row[1];
    $c = $row[2];
    $d = $row[3];
    $course = new Course($i, $t, $c, $d);
}

$statement->close();
$statement = $mysqli->prepare("SELECT * FROM Announcements WHERE course = ? AND announcementLastDay >= ?");
$statement->bind_param("is", $courseId, date('Y-m-d'));
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $i = $row[0];
    $t = $row[1];
    $b = $row[2];
    $md = $row[3];
    $ld = $row[4];
    $c = $row[5];
    array_push($announcements, new Announcement($i, $t, $b, $md, $ld, $c));
}

$statement->close();
$statement = $mysqli->prepare("SELECT * FROM CourseSections WHERE course = ?");
$statement->bind_param("i", $courseId);
$statement->execute();
$result = $statement->get_result();
while ($row = $result->fetch_row()) {
    $i = $row[0];
    $t = $row[1];
    $d = $row[2];
    $c = $row[3];
    array_push($sections, new Section($i, $t, $d, $c));
}

foreach ($sections as $section) {
    $plcontents = array();
    $statement->close();
    $statement = $mysqli->prepare("SELECT * FROM PLContents WHERE courseSectionId = ?");
    $statement->bind_param("i", $section->id);
    $statement->execute();
    $result = $statement->get_result();
    while ($row = $result->fetch_row()) {
        $i = $row[0];
        $ct = $row[1];
        $csi = $row[2];
        array_push($plcontents, new PLContent($i, $ct, null, $csi));
    }
    $section->plcontents = $plcontents;

    $tests = array();
    $statement->close();
    $statement = $mysqli->prepare("SELECT * FROM Tests WHERE courseSectionId = ? ORDER BY lastDay ASC");
    $statement->bind_param("i", $section->id);
    $statement->execute();
    $result = $statement->get_result();
    while ($row = $result->fetch_row()) {
        $i = $row[0];
        $t = $row[1];
        $a = $row[2];
        $l = $row[3];
        $cs = $row[4];
        array_push($tests, new Test($i, $t, $a, $l, $cs));
    }
    $section->tests = $tests;

    $assignments = array();
    $statement->close();
    $statement = $mysqli->prepare("SELECT * FROM Assignments WHERE courseSectionId = ? ORDER BY lastDay ASC");
    $statement->bind_param("i", $section->id);
    $statement->execute();
    $result = $statement->get_result();
    while ($row = $result->fetch_row()) {
        $i = $row[0];
        $t = $row[1];
        $s = $row[2];
        $l = $row[3];
        $m = $row[4];
        $cs = $row[5];
        array_push($assignments, new Assignment($i, $t, $s, $l, $m, $cs));
    }
    $section->assignments = $assignments;
}

$mysqli->close();

$_SESSION["course"] = $course;
?>
<style>
    aside {
        display: block;
        background-color: #2c68b2;
        border-color: #255897;
        color: #ffffff;
    }
    section {
        display: block;
    }
    li > a {
        cursor: pointer;
    }
</style>

<?php include 'Header.php';?>
<section class="col-lg-8">
    <h1 id="title"><?php echo $course->code.' - '.$course->title; ?></h1>
    <h4>Welcome <?php echo $user->firstName." ".$user->lastName?>.</h4>
    <hr/>
    <?php if(count($announcements) >= 1) { ?>
        <h2>Announcements</h2>
        <?php foreach ($announcements as $announcement) { ?>
            <div class="alert alert-dismissible alert-warning">
                <h4><strong><?php echo $announcement->announcementTitle;?></strong><span style="float: right; font-size: 12px"><?php echo $announcement->announcementMadeDay;?></span></h4>
                <button type="button" class="close" onclick="deleteAnnouncement(<?php echo $announcement->id;?>, this.parentNode)">Delete</button>
                <p><?php echo $announcement->announcementBody;?></p>
            </div>
        <?php } ?>
        <hr/>
    <?php } ?>
    <h2>Dashboard</h2>
    <?php foreach ($sections as $section) {
        //print_r($section); ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <?php if (!($user instanceof Student)) { ?>
                    <div id="section-<?php echo $section->id; ?>">
                        <h4 class="col-lg-10"><?php echo $section->sectionTitle; ?></h4>
                        <ul class="nav nav-pills">
                            <li class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false">
                                    Edit <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a onclick="editSection(this.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode, <?php echo $section->id; ?>)">Edit title and description</a></li>
                                    <li><a onclick="deleteSection(this.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode, <?php echo $section->id; ?>)">Delete</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                <?php } else { ?>
                    <h4><?php echo $section->sectionTitle; ?></h4>
                <?php }?>
            </div>
            <div class="panel-body">
                <p><?php echo $section->sectionDescription; ?></p>
                <?php if (count($section->plcontents) >= 1) { ?>
                    <ul class="nav nav-pills nav-stacked">
                        <li><h4 class="list-group-item-heading text-info">Programmed Learning Contents</h4></li>
                        <?php foreach ($section->plcontents as $plcontent) { ?>
                            <?php if (!($user instanceof Student)) { ?>
                                <li class="col-lg-8"><a class="col-lg-12" href="../Control/MainController.php?do=editplc&plcId=<?php echo $plcontent->id; ?>"><?php echo $plcontent->contentTitle; ?></a></li>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-danger" onclick="deleteplcontent(<?php echo $plcontent->id; ?>,'section-<?php echo $section->id; ?>')">Delete</button>
                                </div>
                                <?php if (count($sections) > 1) { ?>
                                    <div class="col-lg-2">
                                        <div class="btn-group">
                                            <a class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                Move to
                                                <span class="caret"></span>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <?php foreach ($sections as $s) { ?>
                                                    <li><a onclick="moveplcontent(<?php echo $plcontent->id; ?>, <?php echo $s->id; ?>, 'section-<?php echo $plcontent->courseSectionId; ?>')"><?php if($section != $s) { echo $s->sectionTitle; }?></a></li>
                                                <?php }?>
                                            </ul>
                                        </div>
                                    </div>
                        <?php }}} ?>
                    </ul>
                <?php } ?>
                <?php if (count($section->assignments) >= 1) { ?>
                    <ul class="nav nav-pills nav-stacked">
                        <li><h4 class="list-group-item-heading text-info">Assignments</h4></li>
                        <?php foreach ($section->assignments as $assignment) {
                            if (!($user instanceof Student)) {?>
                                <li class="col-lg-6"><a class="col-lg-12" href="../Control/MainController.php?do=editAssignment&assignmentId=<?php echo $assignment->id; ?>"><?php echo $assignment->title;?></a></li>
                                <?php if (date("Y-m-d") > $assignment->lastDay) { ?>
                                    <div class="col-lg-2">
                                        <span class="text-danger">CLOSED</span>
                                    </div>
                                <?php } else { ?>
                                    <div class="col-lg-2">
                                        <span class="text-info"><?php echo "Close date: ".$assignment->lastDay?></span>
                                    </div>
                                <?php } ?>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-danger" onclick="deleteAssignment(<?php echo $assignment->id; ?>,'section-<?php echo $section->id; ?>')">Delete</button>
                                </div>
                                <?php if (count($sections) > 1) { ?>
                                    <div class="col-lg-2">
                                        <div class="btn-group">
                                            <a class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                Move to
                                                <span class="caret"></span>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <?php foreach ($sections as $s) { ?>
                                                    <li><a onclick="moveAssignment(<?php echo $assignment->id; ?>, <?php echo $s->id; ?>, 'section-<?php echo $assignment->courseSectionId; ?>')"><?php if($section != $s) { echo $s->sectionTitle; }?></a></li>
                                                <?php }?>
                                            </ul>
                                        </div>
                                    </div>
                                <?php }}} ?>
                    </ul>
                <?php } ?>
                <?php if (count($section->tests) >= 1) { ?>
                    <ul class="nav nav-pills nav-stacked">
                        <li><h4 class="list-group-item-heading text-info">Tests</h4></li>
                        <?php foreach ($section->tests as $test) {
                            if (!($user instanceof Student)) {?>
                                <li class="col-lg-6"><a href="../Control/MainController.php?do=editTest&testId=<?php echo $test->id; ?>" class="col-lg-12"><?php echo $test->title;?></a></li>
                                <?php if (date("Y-m-d") > $test->lastDay) { ?>
                                    <div class="col-lg-2">
                                        <span class="text-danger">CLOSED</span>
                                    </div>
                                <?php } else { ?>
                                    <div class="col-lg-2">
                                        <span class="text-info"><?php echo "Close date: ".$test->lastDay?></span>
                                    </div>
                                <?php } ?>
                                <div class="col-lg-2">
                                    <button type="button" class="btn btn-danger" onclick="deleteTest(<?php echo $test->id; ?>,'section-<?php echo $section->id; ?>')">Delete</button>
                                </div>
                                <?php if (count($sections) > 1) { ?>
                                    <div class="col-lg-2">
                                        <div class="btn-group">
                                            <a class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                Move to
                                                <span class="caret"></span>
                                            </a>
                                            <ul class="dropdown-menu">
                                                <?php foreach ($sections as $s) { ?>
                                                    <li><a onclick="moveTest(<?php echo $test->id; ?>, <?php echo $s->id; ?>, 'section-<?php echo $test->courseSectionId; ?>')"><?php if($section != $s) { echo $s->sectionTitle; }?></a></li>
                                                <?php }?>
                                            </ul>
                                        </div>
                                    </div>
                            <?php }}} ?>
                    </ul>
                <?php } ?>
                <br/>
                <div class="btn-group">
                    <a class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                        Add content
                        <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="../Control/MainController.php?do=makeplc&courseSectionId=<?php echo $section->id; ?>">Programmed Learning content</a></li>
                        <li><a href="../Control/MainController.php?do=makeTest&courseSectionId=<?php echo $section->id; ?>">Test of Multiple Choice Questions</a></li>
                        <li><a href="../Control/MainController.php?do=makeAssignment&courseSectionId=<?php echo $section->id; ?>">Assignment</a></li>
                    </ul>
                </div>
            </div>
        </div>
    <?php } ?>
</section>
<aside class="col-lg-4">
    <?php if (!($user instanceof Student)) { ?>
        <h4>Create Sections for content</h4>
        <ul>
            <li><a data-toggle="modal" data-target="#sectionModal">Create Section</a></li>
        </ul>
        <h4>Create announcements</h4>
        <ul>
            <li><a data-toggle="modal" data-target="#announcementModal">Add Announcement</a></li>
        </ul>
        <h4>Multiple Choice Questions and Tests</h4>
        <ul>
            <li><a href="NewMCQ.php">Create MCQ</a></li>
            <li><a href="EditMCQs.php">Edit MCQs</a></li>
        </ul>
    <?php } ?>
</aside>

<?php if (!($user instanceof Student)) { ?>
<div class="modal fade" id="sectionModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title">New Section</h3>
            </div>
            <div class="modal-body">
                <form onsubmit="saveSection()">
                    <div class="form-group">
                        <label for="sectionTitle" class="control-label">Section Title</label>
                        <input class="form-control" type="text" id="sectionTitle" name="sectionTitle" placeholder="e.g. 'Week 1', 'Multiplication', etc." required/>
                    </div>
                    <div class="form-group">
                        <label for="sectionDescription" class="control-label">Section Description</label>
                        <textarea class="form-control" id="sectionDescription" name="sectionDescription" placeholder="Brief description of section" maxlength="500"></textarea>
                    </div>
                    <input id="courseId" type="hidden" value="<?php echo $course->id; ?>"/>
                    <button type="submit" id="sectionFormButton" style="display: none"></button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="clickDoSection()">Save changes</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="announcementModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h3 class="modal-title">New Announcement</h3>
            </div>
            <div class="modal-body">
                <form onsubmit="doAnnouncement()">
                    <div class="form-group">
                        <label for="announcementTitle" class="control-label">Announcement Title</label>
                        <input class="form-control" type="text" id="announcementTitle" name="announcementTitle" placeholder="e.g. 'New homework', 'Do test!', etc." required/>
                    </div>
                    <div class="form-group">
                        <label for="announcementBody" class="control-label">Announcement Body</label>
                        <textarea class="form-control" id="announcementBody" name="announcementBody" rows="10" placeholder="Place the message here..." maxlength="1000" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="announcementLastDay" class="control-label">Last day for displaying announcement</label>
                        <input type="date" class="form-control" id="announcementLastDay" name="announcementLastDay" value="<?php echo date('Y-m-d'); ?>" required/>
                    </div>
                    <input id="courseId" type="hidden" value="<?php echo $course->id; ?>"/>
                    <button type="submit" id="announcementFormButton" style="display: none"></button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="clickDoAnnouncement()">Do Announcement</button>
            </div>
        </div>
    </div>
</div>

<script>
    var sectionReload = null;
    function reloadPage(response) {
        //alert(response);
        if (response == "saved") {
            location.reload();
        } else if (response == "edited") {
            $("#sectionModal h3")[0].innerHTML = "New Section";
            $("#sectionModal button")[2].onclick = function() {saveSection();};
        } else if (response == "updatedPLC" || response == "deletedPLC" || response == "updatedTest" || response == "deletedTest" || response == "updatedAssignment" || response == "deletedAssignment") {
            window.location = "../View/CourseHome.php#" + sectionReload;
            location.reload();
        }
    }

    function clickDoSection() {
        $("#sectionFormButton").click();
    }

    function saveSection() {
        var sectionTitle = $("#sectionTitle")[0].value;
        var sectionDescription = $("#sectionDescription")[0].value;
        var courseId = $("#courseId")[0].value;
        $.ajax({
            type : "post",
            url : "../Control/SaveSectionOfCourse.php",
            data : {
                "sectionTitle" : sectionTitle,
                "sectionDescription": sectionDescription,
                "courseId": courseId,
                "do": "save"
            },
            success : reloadPage,
            error : function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
            }
        });

        event.preventDefault();
    }

    function saveEditSection(obj, id) {
        $("#sectionModal").modal('toggle');
        var title = $("#sectionTitle")[0];
        var description = $("#sectionDescription")[0];
        var sectionTitle = title.value;
        var sectionDescription = description.value;
        var courseId = $("#courseId")[0].value;

        obj.childNodes[1].childNodes[1].childNodes[1].innerHTML = sectionTitle;
        obj.childNodes[3].childNodes[1].innerHTML = sectionDescription;

        title.value = "";
        description.value = "";
        $.ajax({
            type : "post",
            url : "../Control/SaveSectionOfCourse.php",
            data : {
                "sectionTitle" : sectionTitle,
                "sectionDescription": sectionDescription,
                "courseId": courseId,
                "sectionId": id,
                "do": "edit"
            },
            success : reloadPage,
            error : function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
            }
        });

        event.preventDefault();
    }

    function editSection(obj, id) {
        var title = obj.childNodes[1].childNodes[1].childNodes[1].innerHTML;
        var description = obj.childNodes[3].childNodes[1].innerHTML;
        $("#sectionModal h3")[0].innerHTML = "Edit Section";
        $("#sectionTitle")[0].value = title;
        $("#sectionDescription")[0].value = description;
        $("#sectionModal form")[0].onsubmit = function() {saveEditSection(obj, id);};
        $("#sectionModal").modal();
    }

    function deleteSection(obj, id) {
        $.ajax({
            type : "post",
            url : "../Control/SaveSectionOfCourse.php",
            data : {
                "sectionId": id,
                "do": "delete"
            },
            success : reloadPage,
            error : function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
            }
        });

        obj.remove();
    }

    function moveplcontent(plcId, newSectionId, currentSectionId) {
        sectionReload = currentSectionId;
        $.ajax({
            type : "post",
            url : "../Control/UpdatePLC.php",
            data : {
                "plcId": plcId,
                "sectionId": newSectionId,
                "do": "changePLCSection"
            },
            success : reloadPage,
            error : function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
            }
        });
    }

    function deleteplcontent(plcId, sectionId) {
        sectionReload = sectionId;
        $.ajax({
            type : "post",
            url : "../Control/UpdatePLC.php",
            data : {
                "plcId": plcId,
                "do": "deletePLC"
            },
            success : reloadPage,
            error : function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
            }
        });
    }

    function moveTest(testId, newSectionId, currentSectionId) {
        sectionReload = currentSectionId;
        $.ajax({
            type : "post",
            url : "../Control/UpdateTest.php",
            data : {
                "testId": testId,
                "sectionId": newSectionId,
                "do": "changeTestSection"
            },
            success : reloadPage,
            error : function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
            }
        });
    }

    function deleteTest(testId, sectionId) {
        sectionReload = sectionId;
        $.ajax({
            type : "post",
            url : "../Control/UpdateTest.php",
            data : {
                "testId": testId,
                "do": "deleteTest"
            },
            success : reloadPage,
            error : function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
            }
        });
    }

    function moveAssignment(assignmentId, newSectionId, currentSectionId) {
        sectionReload = currentSectionId;
        $.ajax({
            type : "post",
            url : "../Control/UpdateAssignment.php",
            data : {
                "assignmentId": assignmentId,
                "sectionId": newSectionId,
                "do": "changeAssignmentSection"
            },
            success : reloadPage,
            error : function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
            }
        });
    }

    function deleteAssignment(assignmentId, sectionId) {
        sectionReload = sectionId;
        $.ajax({
            type : "post",
            url : "../Control/UpdateAssignment.php",
            data : {
                "assignmentId": assignmentId,
                "do": "deleteAssignment"
            },
            success : reloadPage,
            error : function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
            }
        });
    }

    function clickDoAnnouncement() {
        $("#announcementFormButton").click();
    }

    function doAnnouncement() {
        var announcementTitle = $("#announcementTitle")[0].value;
        var announcementBody = $("#announcementBody")[0].value;
        var announcementLastDay = $("#announcementLastDay")[0].value;
        var courseId = $("#courseId")[0].value;
        $.ajax({
            type : "post",
            url : "../Control/AnnouncementsInDatabase.php",
            data : {
                "announcementTitle" : announcementTitle,
                "announcementBody": announcementBody,
                "announcementLastDay": announcementLastDay,
                "courseId": courseId,
                "do": "save"
            },
            success : reloadPage,
            error : function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
            }
        });

        event.preventDefault();
    }

    function deleteAnnouncement(id, obj) {
        $.ajax({
            type : "post",
            url : "../Control/AnnouncementsInDatabase.php",
            data : {
                "announcementId": id,
                "do": "delete"
            },
            success : reloadPage,
            error : function(jqXHR, textStatus, errorMessage) {
                console.log(errorMessage);
            }
        });

        obj.remove();
    }
</script>
<?php } ?>

<?php include 'Footer.php';?>

