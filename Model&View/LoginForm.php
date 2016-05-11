<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 4/30/16
 * Time: 8:31 PM
 */
?>
<style>
    #loginForm {
        background-color: #2c68b2;
        border-color: #255897;
        color: #ffffff;
    }

    #loginForm > fieldset {
        text-align: center;
    }

    #loginForm > fieldset > legend {
        border-color: #99ddff;
        color: #99ddff;
    }

    #loginForm > fieldset > #container {
        text-align: center;
    }

   #loginForm > fieldset > #container > button {
       width: 45%;
    }
</style>
<form action="" method="post" class="form-horizontal col-lg-4" id="loginForm">
    <fieldset>
        <legend>Log in</legend>
        <div id="container">
            <div class="form-group">
                <label for="username" class="control-label col-lg-3">Username</label>
                <div class="col-lg-9">
                    <input type="text" class="form-control" id="username" placeholder="Username">
                </div>
            </div>
            <div class="form-group">
                <label for="password" class="control-label col-lg-3">Password</label>
                <div class="col-lg-9">
                    <input type="password" class="form-control" id="password" placeholder="Password">
                </div>
            </div>
            <button class="btn btn-success">Log in</button>
            <br/><br/>
        </div>
    </fieldset>
</form>