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
    public $institutionName;
    public $institutionId = 0;

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

    function __construct8($i, $f, $l, $e, $u, $p, $iN, $ii) {
        $this->id = $i;
        $this->firstName = $f;
        $this->lastName = $l;
        $this->email = $e;
        $this->userName = $u;
        $this->password = $p;
        $this->institutionName = $iN;
        $this->institutionId = $ii;
    }

    function saveUserInformationInDatabase($mysqli) {
        $statement = $mysqli->prepare("INSERT INTO UserInformation (firstName, lastName, email, username, password) VALUES (?, ?, ?, ?, ?)");
        $statement->bind_param('sssss', $this->firstName, $this->lastName, $this->email, $this->userName, $this->password);
        $statement->execute();
        $this->id = $statement->insert_id;
    }

    function saveInstitutionInDatabase($mysqli) {
        $statement = $mysqli->prepare("INSERT INTO Institutions (name) VALUES (?)");
        $statement->bind_param('s', $this->institutionName);
        $statement->execute();
        $this->institutionId = $statement->insert_id;
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

    function __construct6($f, $l, $e, $u, $p, $iN) {
        parent::__construct5($f, $l, $e, $u, $p);
        $this->institutionName = $iN;
    }

    function __construct7($i, $f, $l, $e, $u, $p, $ci) {
        parent::__construct6($i, $f, $l, $e, $u, $p);
        $this->courseAdministratorId = $ci;
    }

    function __construct9($i, $f, $l, $e, $u, $p, $ci, $iN, $ii) {
        parent::__construct8($i, $f, $l, $e, $u, $p, $iN, $ii);
        $this->courseAdministratorId = $ci;
    }

    function saveInDatabase() {
        $mysqli = new mysqli("localhost", "root", "", "p-learning");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        $mysqli->begin_transaction();
        $this->saveUserInformationInDatabase($mysqli);
        $this->saveInstitutionInDatabase($mysqli);
        if ($this->id != 0) {
            $statement = $mysqli->prepare("INSERT INTO CourseAdministrators (user, institution) VALUES (?, ?)");
            $statement->bind_param('ii', $this->id, $this->institutionId);
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

    function __construct6($f, $l, $e, $u, $p, $iN) {
        parent::__construct5($f, $l, $e, $u, $p);
        $this->institutionName = $iN;
    }

    function __construct7($i, $f, $l, $e, $u, $p, $ti) {
        parent::__construct6($i, $f, $l, $e, $u, $p);
        $this->teacherId = $ti;
    }

    function __construct9($i, $f, $l, $e, $u, $p, $ti, $iN, $ii) {
        parent::__construct8($i, $f, $l, $e, $u, $p, $iN, $ii);
        $this->teacherId = $ti;
    }

    function saveInDatabase($institutionId) {
        $this->institutionId = $institutionId;
        $mysqli = new mysqli("localhost", "root", "", "p-learning");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        $mysqli->begin_transaction();
        $this->saveUserInformationInDatabase($mysqli);
        if ($this->id != 0) {
            $statement = $mysqli->prepare("INSERT INTO Teachers (user, institution) VALUES (?, ?)");
            $statement->bind_param('ii', $this->id, $this->institutionId);
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

    function __construct6($f, $l, $e, $u, $p, $iN) {
        parent::__construct5($f, $l, $e, $u, $p);
        $this->institutionName = $iN;
    }

    function __construct7($i, $f, $l, $e, $u, $p, $si) {
        parent::__construct6($i, $f, $l, $e, $u, $p);
        $this->studentId = $si;
    }

    function __construct9($i, $f, $l, $e, $u, $p, $si, $iN, $ii) {
        parent::__construct8($i, $f, $l, $e, $u, $p, $iN, $ii);
        $this->studentId = $si;
    }

    function saveInDatabase($institutionId) {
        $this->institutionId = $institutionId;
        $mysqli = new mysqli("localhost", "root", "", "p-learning");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        $mysqli->begin_transaction();
        $this->saveUserInformationInDatabase($mysqli);
        if ($this->id != 0) {
            $statement = $mysqli->prepare("INSERT INTO Students (user, institution) VALUES (?, ?)");
            $statement->bind_param('ii', $this->id, $this->institutionId);
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