<?php
// conn.php

$host = getenv('MYSQLHOST') ?: 'centerbeam.proxy.rlwy.net';
$port = getenv('MYSQLPORT') ?: '43017';
$user = getenv('MYSQLUSER') ?: 'root';
$password = getenv('MYSQLPASSWORD') ?: 'ErPFKJvjviZNjjkbfxvkDfpPHBgUFdmp';
$dbname = getenv('MYSQLDATABASE') ?: 'railway';

$conn = new mysqli($host, $user, $password, $dbname, $port);

if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    die('Database connection failed: ' . $conn->connect_error);
}

error_log("Connection successful!");
?>
