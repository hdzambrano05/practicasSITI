<?php 
require("../libs/phpmailer/src/PHPMailer.php");
require("../libs/phpmailer/src/Exception.php");
require("../libs/phpmailer/src/SMTP.php");
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
class DbHandler { 
    private $conn;

    function __construct($db_name) {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect($db_name);
    }


    public function getUser($id_user) {
        $query = "SELECT * FROM `user` WHERE id_user='$id_user'";
        $stmt = $this->conn->prepare($query);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            $stmt->close();
            return $res;
        } else {
            $stmt->close();

            return FALSE;
        }
    }

    
}





?> 
