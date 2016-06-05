<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/11/16
 * Time: 1:02 PM
 */

require("../Model/User.php");

session_start();

$mysqli = new mysqli("localhost", "root", "", "p-learning");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

$firstNameErr = $lastNameErr = $emailErr = $usernameErr = $passwordErr = $confirmPasswordErr = $institutionErr = "";
$firstName = $lastName = $email = $username = $password = $confirmPassword = $institution =  "";

if (isset($_SESSION["user"]) && $_SESSION["user"]->institutionName != "") {
    $institution = $_SESSION["user"]->institutionName;
}

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["firstName"])) {
        $firstNameErr = "First name is required.";
    } else if (strlen($_POST["firstName"]) > 40) {
        $firstNameErr .= "First name is too long.";
    } else {
        $firstName = test_input($_POST["firstName"]);
    }

    if (empty($_POST["lastName"])) {
        $lastNameErr = "Last name is required.";
    } else if (strlen($_POST["lastName"]) > 40) {
        $lastNameErr .= "Last name is too long.";
    } else {
        $lastName = test_input($_POST["lastName"]);
    }

    if (empty($_POST["email"])) {
        $emailErr = "Email is required. ";
    } else if (strlen($_POST["email"]) > 50) {
        $emailErr .= "Email is too long.";
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr .= "Invalid email address.";
        }
    }

    if (empty($_POST["username"])) {
        $usernameErr = "Username is required. ";
    } else if (strlen($_POST["username"]) > 20) {
        $userNameErr .= "Username is too long.";
    } else {
        $username = test_input($_POST["username"]);
        $statement = $mysqli->prepare("SELECT COUNT(username) FROM UserInformation WHERE username = ?");
        $statement->bind_param('i', $username);
        $statement->execute();
        while ($row = $statement->fetch()) {
            if ($row[0] > 0) {
                $usernameErr .= "There is another person with this username. Please change it.";
            }
        }
        $mysqli->close();
    }

    if (empty($_POST["password"])) {
        $passwordErr = "Password is required. ";
    } else if (strlen($_POST["password"]) > 40) {
        $passwordErr .= "Password is too long. ";
    } else {
        $password = test_input($_POST["password"]);
    }

    if (empty($_POST["confirmPassword"])) {
        $confirmPasswordErr = "Confirmation of the password is required. ";
    } else if (strlen($_POST["confirmPassword"]) > 40) {
        $passwordErr .= "Password is too long. ";
    } else {
        $confirmPassword = test_input($_POST["confirmPassword"]);
    }

    if ($password != $confirmPassword) {
        $confirmPasswordErr .= "Both passwords were not the same.";
    }

    if (empty($_POST["institution"]) && $institution == "") {
        $institutionErr = "Institution is required.";
    } else if (strlen($_POST["institution"]) > 50) {
        $institutionErr .= "Institution is too long.";
    } else {
        if ($institution == "") {
            $institution = test_input($_POST["institution"]);
        }
    }

    if ($firstNameErr.$lastNameErr.$emailErr.$usernameErr.$passwordErr.$confirmPasswordErr.$institutionErr == "") {
        $userClass = str_replace(" ", "",$typeOfUser);
        $user = new $userClass($firstName, $lastName, $email, $username, $password, $institution);
        //print_r($user);
        //print_r($_SESSION["user"]);
        if ($userClass == "CourseAdministrator") {
            $_SESSION["user"] = $user;
            //print_r($user);
        } else {
            $_SESSION["newUser"] = $user;
        }
        // Redirect
        header("Location: ../Control/MainController.php?do=new" . $userClass);
    }
}

?>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST">
    <fieldset>
        <legend><?php echo $typeOfUser?> - Personal details</legend>
        <div id="container">
            <div class="form-group <?php if($firstNameErr != "") { echo "has-error"; }?>">
                <label for="firstName" class="control-label col-lg-3">First Name</label>
                <div class="col-lg-9">
                    <input type="text" class="form-control" id="firstName" name="firstName" placeholder="First Name" value="<?php echo $firstName ?>" required>
                    <span class="text-danger"><?php echo $firstNameErr ?></span>
                </div>
            </div>
            <div class="form-group <?php if($lastNameErr != "") { echo "has-error"; }?>">
                <label for="lastName" class="control-label col-lg-3">Last Name</label>
                <div class="col-lg-9">
                    <input type="text" class="form-control" id="lastName" name="lastName" placeholder="Last Name" value="<?php echo $lastName ?>" required>
                    <span class="text-danger"><?php echo $lastNameErr ?></span>
                </div>
            </div>
            <div class="form-group <?php if($emailErr != "") { echo "has-error"; }?>">
                <label for="email" class="control-label col-lg-3">Email</label>
                <div class="col-lg-9">
                    <input type="text" class="form-control" id="email" name="email" placeholder="Email" value="<?php echo $email ?>" required>
                    <span class="text-danger"><?php echo $emailErr ?></span>
                </div>
            </div>
            <div class="form-group <?php if($usernameErr != "") { echo "has-error"; }?>">
                <label for="username" class="control-label col-lg-3">Username</label>
                <div class="col-lg-9">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?php echo $username ?>" required>
                    <span class="text-danger"><?php echo $usernameErr ?></span>
                </div>
            </div>
            <div class="form-group <?php if($passwordErr != "") { echo "has-error"; }?>">
                <label for="password" class="control-label col-lg-3">Password</label>
                <div class="col-lg-9">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <span class="text-danger"><?php echo $passwordErr ?></span>
                </div>
            </div>
            <div class="form-group <?php if($confirmPasswordErr != "") { echo "has-error"; }?>">
                <label for="confirmPassword" class="control-label col-lg-3">Confirm Password</label>
                <div class="col-lg-9">
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
                    <span class="text-danger"><?php echo $confirmPasswordErr ?></span>
                </div>
            </div>
            <div class="form-group <?php if($institutionErr != "") { echo "has-error"; }?>">
                <label for="institution" class="control-label col-lg-3">Institution</label>
                <div class="col-lg-9">
                    <input type="text" class="form-control" <?php if($institution != "") {echo "disabled";} ?> id="institution" name="institution" placeholder="University, Organization, etc." value="<?php echo $institution ?>" required>
                    <span class="text-danger"><?php echo $institutionErr ?></span>
                </div>
            </div>
        </div>
    </fieldset>
    <br/>
    <button type="submit" class="btn btn-lg btn-success">Create <?php echo $typeOfUser?> account</button>
    <br/><br/><br/>
</form>
