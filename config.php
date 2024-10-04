<?php
$servername = "localhost";
$username = "tenderso_connect";
$password = "tenderso_2014";
$dbname = "tenderso_tendersoko";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to close the connection
function close_db_connection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>
