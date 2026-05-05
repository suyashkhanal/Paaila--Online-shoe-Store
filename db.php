<?php
$conn = new mysqli("localhost", "root", "", "paaila");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>