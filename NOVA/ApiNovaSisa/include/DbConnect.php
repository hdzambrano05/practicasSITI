<?php

/**
 * Handling database connection
 *
 * @author Ravi Tamada
 * @link URL Tutorial link
 */
class DbConnect {

    private $conn;

    function __construct() {        
    }

    /**
     * Establishing database connection
     * @return database connection handler
     */
    function connect($db_name) {
        include_once dirname(__FILE__) 	. '/Config.php';

        // Connecting to mysql database
        $port = '5432';
 
        $db_host = DB_HOST;

        $db_username = DB_USERNAME;

        $db_password = DB_PASSWORD;

        $db_name = $db_name;


        // Conectando y seleccionado la base de datos  
		$this->conn = pg_connect("host=$db_host dbname=$db_name user=$db_username password=$db_password")
  		  or die('No se ha podido conectar: ' . pg_last_error());

  		//$query = 'SELECT * FROM mapas.core_mapabase';
		//$result = pg_query($query) or die('La consulta fallo: ' . pg_last_error());



		//mysqli_set_charset($this->conn, "utf8");

        // Check for database connection error
        //if (mysqli_connect_errno()) {
         //   echo "Failed to connect to MySQL: " . mysqli_connect_error();
        //}

        // returing connection resource
        return $this->conn;
    }

}

?>
