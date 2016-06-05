<?php
/**
 * Created by PhpStorm.
 * User: bernardot
 * Date: 5/28/16
 * Time: 11:50 AM
 */

class Course {
    public $id = 0;
    public $title;
    public $code;
    public $description;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    function __construct3($t, $c, $d) {
        $this->title = $t;
        $this->code = $c;
        $this->description = $d;
    }

    function __construct4($i, $t, $c, $d) {
        $this->id = $i;
        $this->title = $t;
        $this->code = $c;
        $this->description = $d;
    }

    function saveInDatabase($courseAdministratorId) {
        $mysqli = new mysqli("localhost", "root", "", "p-learning");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }
        $mysqli->begin_transaction();
        $statement = $mysqli->prepare("INSERT INTO Courses (title, code, description) VALUES (?, ?, ?)");
        $statement->bind_param('sss', $this->title, $this->code, $this->description);
        $statement->execute();
        $this->id = $statement->insert_id;
        if ($this->id != 0) {
            $statement->close();
            $statement = $mysqli->prepare("INSERT INTO CoursesCreated (courseAdministratorId, courseId) VALUES (?, ?)");
            $statement->bind_param('ii', $courseAdministratorId, $this->id);
            $statement->execute();
            if ($statement->errno == 0) {
                $mysqli->commit();
                $mysqli->close();
                return true;
            } else {
                $mysqli->rollback();
                $mysqli->close();
                return false;
            }
        } else {
            $mysqli->rollback();
            $mysqli->close();
            return false;
        }
    }
}