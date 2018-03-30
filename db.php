<?php
    
    class DB {

     private $servername = "localhost";
     private $username = "root";
     private $password = "123";
     private $dbname = "unanth_file_server";

// Create connection

     public function conn(){
        $conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
     }

    public function insert($table,$data){
        $conn = $this->conn();
        $keys = array_keys($data);
        $fields = implode(", ",$keys);
        $values = array_values($data);
        $value = implode(',', array_map(function($x){ return "'".$x."'";}, $values));
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$value})";
        
        if ($conn->query($sql) === TRUE) {
            return ['success'=>true,'message'=>'File stored for processing.'];
        } else {
            return ['error'=>true,'message'=> $conn->error];
        }

     }
}


?>