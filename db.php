<?php 

$host = "localhost";
$username = "root";
$password = "";
$db = "test_slip";

$conn = mysqli_connect($host, $username, $password, $db);

if(!$conn) {
    echo "Connection failed" . mysqli_connect_error();  
}

?>