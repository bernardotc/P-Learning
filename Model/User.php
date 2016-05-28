<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/11/16
 * Time: 4:37 PM
 */

class User {
    public $id = 0;
    public $firstName;
    public $lastName;
    public $email;
    public $userName;
    public $password;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    function __construct5($f, $l, $e, $u, $p) {
        $this->firstName = $f;
        $this->lastName = $l;
        $this->email = $e;
        $this->userName = $u;
        $this->password = $p;
    }

    function __construct6($i, $f, $l, $e, $u, $p) {
        $this->id = $i;
        $this->firstName = $f;
        $this->lastName = $l;
        $this->email = $e;
        $this->userName = $u;
        $this->password = $p;
    }

    function saveUserInformationInDatabase($mysqli) {
        $statement = $mysqli->prepare("INSERT INTO UserInformation (firstName, lastName, email, username, password) VALUES (?, ?, ?, ?, ?)");
        $statement->bind_param('sssss', $this->firstName, $this->lastName, $this->email, $this->userName, $this->password);
        $statement->execute();
        $this->id = $statement->insert_id;
    }
}

class CourseAdministrator extends User {
    public $courseAdministratorId = 0;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    function __construct5($f, $l, $e, $u, $p) {
        parent::__construct5($f, $l, $e, $u, $p);
    }

    function __construct7($i, $f, $l, $e, $u, $p, $ci) {
        parent::__construct6($i, $f, $l, $e, $u, $p);
        $this->courseAdministratorId = $ci;
    }

    function saveInDatabase() {
        $mysqli = new mysqli("localhost", "root", "", "p-learning");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        $mysqli->begin_transaction();
        $this->saveUserInformationInDatabase($mysqli);
        if ($this->id != 0) {
            $statement = $mysqli->prepare("INSERT INTO CourseAdministrators (user) VALUES (?)");
            $statement->bind_param('i', $this->id);
            $statement->execute();
            $this->courseAdministratorId = $statement->insert_id;
            if ($this->courseAdministratorId != 0) {
                $mysqli->commit();
                $mysqli->close();
                return true;
            } else {
                $mysqli ->rollback();
                $mysqli->close();
                return false;
            }
        } else {
            $mysqli ->rollback();
            $mysqli->close();
            return false;
        }
    }
}

class Teacher extends User {
    public $teacherId = 0;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    function __construct5($f, $l, $e, $u, $p) {
        parent::__construct5($f, $l, $e, $u, $p);
    }

    function __construct7($i, $f, $l, $e, $u, $p, $ti) {
        parent::__construct6($i, $f, $l, $e, $u, $p);
        $this->teacherId = $ti;
    }

    function saveInDatabase() {
        $mysqli = new mysqli("localhost", "root", "", "p-learning");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        $mysqli->begin_transaction();
        $this->saveUserInformationInDatabase($mysqli);
        if ($this->id != 0) {
            $statement = $mysqli->prepare("INSERT INTO Teachers (user) VALUES (?)");
            $statement->bind_param('i', $this->id);
            $statement->execute();
            $this->teacherId = $statement->insert_id;
            if ($this->teacherId != 0) {
                $mysqli->commit();
                $mysqli->close();
                return true;
            } else {
                $mysqli ->rollback();
                $mysqli->close();
                return false;
            }
        } else {
            $mysqli ->rollback();
            $mysqli->close();
            return false;
        }
    }
}

class Student extends User {
    public $studentId = 0;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    function __construct5($f, $l, $e, $u, $p) {
        parent::__construct5($f, $l, $e, $u, $p);
    }

    function __construct7($i, $f, $l, $e, $u, $p, $si) {
        parent::__construct6($i, $f, $l, $e, $u, $p);
        $this->studentId = $si;
    }

    function saveInDatabase() {
        $mysqli = new mysqli("localhost", "root", "", "p-learning");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        $mysqli->begin_transaction();
        $this->saveUserInformationInDatabase($mysqli);
        if ($this->id != 0) {
            $statement = $mysqli->prepare("INSERT INTO Students (user) VALUES (?)");
            $statement->bind_param('i', $this->id);
            $statement->execute();
            $this->studentId = $statement->insert_id;
            if ($this->studentId != 0) {
                $mysqli->commit();
                $mysqli->close();
                return true;
            } else {
                $mysqli ->rollback();
                $mysqli->close();
                return false;
            }
        } else {
            $mysqli ->rollback();
            $mysqli->close();
            return false;
        }
    }
}