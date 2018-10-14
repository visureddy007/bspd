
<?php
class DbConnect {

    private $conn;

    function __construct(){ }

    function connect(){
      include_once dirname(__FILE__) . '/Config.php';
      $this->conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
      if (mysqli_connect_errno()) {
        echo "Failed to connect to Mysql: " . mysqli_connect_error();
      }	  /* Change character set to utf8*/		mysqli_set_charset($this->conn,"utf8");
      return $this->conn;
    }
}
?>