<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/11/16
 * Time: 12:54 PM
 */
$login = '';
$home = '';
$signin = 'class="active"';
$typeOfUser = "Course Administrator";
?>
<style>
    form {
        width: 70%;
        background-color: #8dbdd9;
        border: 1px solid;
        border-color: #255897;
        min-height: 45%;
        padding-top: 1%;
        padding-left: 7%;
        padding-right: 7%;
        text-align: left;
        overflow: hidden;
    }

    form > button {
        float: right;
        min-width: 20%;
    }

    #userForm {
        display: block;
        text-align: -webkit-center;
    }

</style>
<?php include 'Header.php';?>
<h2 id="title">Create a new Course Administrator account</h2>
<hr/>
<p>Please fill the next form. You will create a Course Administrator account. One of your main responsibilities is adding teachers and students that can access the content of courses you will administrate.</p>
<div id="userForm">
    <?php include 'NewUserForm.php';?>
</div>
<?php include 'Footer.php';?>