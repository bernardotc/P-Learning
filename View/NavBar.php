<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 3/9/16
 * Time: 9:03 PM
 */
?>
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-2">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="Login.php">P-Learning</a>
        </div>

        <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-2">
            <ul class="nav navbar-nav">
                <?php if ($home == '') { ?>
                    <li <?php echo $login ?>><a href="Login.php">Log In<span class="sr-only">(current)</span></a></li>
                    <li <?php echo $signin ?>><a href="SignIn.php">Sign In<span class="sr-only">(current)</span></a></li>
            </ul>
                <?php } else { ?>
                    <li <?php echo $home ?>><a href="Home.php">Home<span class="sr-only">(current)</span></a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a href="../Control/MainController.php?do=logout">Logout<span class="sr-only">(current)</span></a></li>
            </ul>
            <?php } ?>
        </div>
    </div>
</nav>