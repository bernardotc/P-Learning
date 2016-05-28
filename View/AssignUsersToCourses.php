<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/28/16
 * Time: 1:26 PM
 */
$login = '';
$home = 'class="active"';
$signin = '';
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

        #assignForm {
            display: block;
            text-align: -webkit-center;
        }

    </style>
<?php include 'Header.php';?>
    <h2 id="title">Edit users in courses</h2>
    <hr/>
    <p>Please choose the course you will like to edit.</p>
    <div id="assignForm">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
            
        </form>
    </div>
<?php include 'Footer.php';?>