<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 4/30/16
 * Time: 8:31 PM
 */

$usernameErr = $passwordErr = "";
$username = $password = "";

function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["username"])) {
        $usernameErr = "Username is required. ";
    } else {
        $username = test_input($_POST["username"]);
    }

    if (empty($_POST["password"])) {
        $passwordErr = "Password is required. ";
    } else {
        $password = test_input($_POST["password"]);
    }

    if ($usernameErr.$passwordErr == "") {
        $i = $f = $l = $e = $u = $p = $specialId = $institutionId = $institutionName = $user = null;
        $mysqli = new mysqli("localhost", "root", "", "p-learning");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        $mysqli->begin_transaction();
        $statement = $mysqli->prepare("SELECT * FROM UserInformation WHERE username = ? AND password = ?");
        $statement->bind_param("ss", $username, $password);
        $statement->execute();
        $result = $statement->get_result();
        while ($row = $result->fetch_row()) {
            //print_r($row);
            $i = $row[0];
            $f = $row[1];
            $l = $row[2];
            $e = $row[3];
            $u = $row[4];
            $p = $row[5];
        }
        if ($i == null) {
            $mysqli->rollback();
            $passwordErr .= "Incorrect username or password.";
        } else {
            $statement->close();
            $statement = $mysqli->prepare("SELECT id, \"CourseAdministrator\", institution FROM CourseAdministrators WHERE user = ?\n"
                . "UNION\n"
                . "SELECT id, \"SystemAdministrator\", institution FROM SystemAdministrators WHERE user = ?\n"
                . "UNION\n"
                . "SELECT id, \"Teacher\", institution FROM Teachers WHERE user = ?\n"
                . "UNION\n"
                . "SELECT id, \"Student\", institution FROM Students WHERE user = ?");
            $statement->bind_param("iiii", $i, $i, $i, $i);
            $statement->execute();
            $result = $statement->get_result();
            while ($row = $result->fetch_row()) {
                $specialId = $row[0];
                $userClass = $row[1];
                $institutionId = $row[2];
            }
            if ($specialId == null) {
                $mysqli->rollback();
                $passwordErr .= "Incorrect username or password.";
            } else {
                $statement->close();
                $statement = $mysqli->prepare("SELECT name FROM Institutions WHERE id = ?");
                $statement->bind_param("i", $institutionId);
                $statement->execute();
                $result = $statement->get_result();
                while ($row = $result->fetch_row()) {
                    $institutionName = $row[0];
                }
                $mysqli->commit();
                $user = new $userClass($i, $f, $l, $e, $u, $p, $specialId, $institutionName, $institutionId);
            }
        }
        $mysqli->close();
        if ($user != null) {
            $_SESSION["user"] = $user;
            header("Location: ../Control/MainController.php?do=login");
        }
    }
}
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
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="POST" class="form-horizontal col-lg-4" id="loginForm">
    <fieldset>
        <legend>Log in</legend>
        <div id="container">
            <div class="form-group <?php if($usernameErr != "" || $passwordErr == "Incorrect username or password.") { echo "has-error"; }?>">
                <label for="username" class="control-label col-lg-3">Username</label>
                <div class="col-lg-9">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?php echo $username?>">
                    <span class="text-danger"><?php echo $usernameErr ?></span>
                </div>
            </div>
            <div class="form-group <?php if($passwordErr != "") { echo "has-error"; }?>">
                <label for="password" class="control-label col-lg-3">Password</label>
                <div class="col-lg-9">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                    <span class="text-danger"><?php echo $passwordErr ?></span>
                </div>
            </div>
            <button class="btn btn-success">Log in</button>
            <br/><br/>
        </div>
    </fieldset>
</form>