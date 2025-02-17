<?php
// conn.php

$host = 'localhost';
$user = 'root';
$password = 'gDhZZC02vfOd5O15';
$dbname = 'console';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
?>

