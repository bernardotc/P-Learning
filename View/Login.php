<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 3/9/16
 * Time: 9:04 PM
 */

require("../Model/User.php");

session_start();

if (!empty($_SESSION["user"]) && $_SESSION["user"] != null) {
    header("Location: ./Home.php");
}

$login = 'class="active"';
$signin = '';
$home = '';
?>
<style>
    #title {
        text-align: center;
    }

    #rightSideDiv {
        text-align: center;
    }
</style>
<?php include 'Header.php';?>
<h1 id="title">P-Learning</h1>
<hr/>
<?php include 'LoginForm.php';?>
<div class="col-lg-8" id="rightSideDiv">
    <p>Want to be part of this amazing platform? Create a new account!</p>
    <a href="SignIn.php" class="btn btn-lg btn-primary">Sign in</a>
</div>
<?php include 'Footer.php';?>