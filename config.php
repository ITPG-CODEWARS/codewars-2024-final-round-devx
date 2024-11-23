<?php
// config.php

// Database connection details
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "devx_train";

// Create a new database connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
