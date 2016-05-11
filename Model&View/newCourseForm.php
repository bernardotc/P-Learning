<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/11/16
 * Time: 1:15 PM
 */
?>
<fieldset>
    <legend>Course Information</legend>
    <div id="container">
        <div class="form-group">
            <label for="title" class="control-label col-lg-3">Course Title</label>
            <div class="col-lg-9">
                <input type="text" class="form-control" id="title" placeholder="e.g. Physics 1, Linear Equations..." required>
            </div>
        </div>
        <div class="form-group">
            <label for="code" class="control-label col-lg-3">Course Code</label>
            <div class="col-lg-9">
                <input type="text" class="form-control" id="code" placeholder="e.g. CE832-7-AU, SP234..." required>
            </div>
        </div>
        <div class="form-group">
            <label for="description" class="control-label col-lg-3">Description</label>
            <div class="col-lg-9">
                <textarea rows="6" class="form-control" id="description" placeholder="Describe the contents of the course."></textarea>
            </div>
        </div>
    </div>
</fieldset>