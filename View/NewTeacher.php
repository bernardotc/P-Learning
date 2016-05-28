<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/28/16
 * Time: 10:50 AM
 */
$login = '';
$home = 'class="active"';
$signin = '';
$typeOfUser = "Teacher";
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
<h2 id="title">Create a new Teacher</h2>
<hr/>
<p>Please fill the next form. You will create a Teacher.</p>
<div id="userForm">
    <?php include 'NewUserForm.php';?>
</div>
<?php include 'Footer.php';?>
