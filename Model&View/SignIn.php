<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/11/16
 * Time: 12:54 PM
 */

$typeOfUser = "Course Administrator";
?>
<style>
    #leftSideDiv, #rightSideDiv {
        background-color: #8dbdd9;
        border: 1px solid;
        border-color: #255897;
        min-height: 40%;
        padding-top: 1%;
        border-bottom: none;
    }

    form > button {
        margin-top: 1%;
        margin-left: 80%;
        width: 20%;
    }
</style>
<?php include 'Header.php';?>
<h2 id="title">Create a new Course</h2>
<hr/>
<p>Please fill the next form. You will be treated as the Course Administrator for the course you are creating. One of your main responsibilities is adding teachers and students that can access the content of this module.</p>
<form action="" method="post">
    <div class="col-lg-6" id="leftSideDiv">
        <?php include 'newUserForm.php';?>
    </div>
    <div class="col-lg-6" id="rightSideDiv">
        <?php include 'newCourseForm.php';?>
    </div>
    <button type="submit" class="btn btn-lg btn-success">Create Course</button>
</form>
<?php include 'Footer.php';?>