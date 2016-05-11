<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/11/16
 * Time: 1:02 PM
 */
?>
<fieldset>
    <legend><?php echo $typeOfUser?> - Personal details</legend>
    <div id="container">
        <div class="form-group">
            <label for="firstName" class="control-label col-lg-3">First Name</label>
            <div class="col-lg-9">
                <input type="text" class="form-control" id="firstName" placeholder="First Name" required>
            </div>
        </div>
        <div class="form-group">
            <label for="lastName" class="control-label col-lg-3">Last Name</label>
            <div class="col-lg-9">
                <input type="text" class="form-control" id="lastName" placeholder="Last Name" required>
            </div>
        </div>
        <div class="form-group">
            <label for="email" class="control-label col-lg-3">Email</label>
            <div class="col-lg-9">
                <input type="text" class="form-control" id="email" placeholder="Email" required>
            </div>
        </div>
        <div class="form-group">
            <label for="username" class="control-label col-lg-3">Username</label>
            <div class="col-lg-9">
                <input type="text" class="form-control" id="username" placeholder="Username" required>
            </div>
        </div>
        <div class="form-group">
            <label for="password" class="control-label col-lg-3">Password</label>
            <div class="col-lg-9">
                <input type="password" class="form-control" id="password" placeholder="Password" required>
            </div>
        </div>
        <div class="form-group">
            <label for="confirmPassword" class="control-label col-lg-3">Confirm Password</label>
            <div class="col-lg-9">
                <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm Password" required>
            </div>
        </div>
    </div>
</fieldset>
