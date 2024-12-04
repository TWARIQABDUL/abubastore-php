<?php
$host = 'localhost'; 
$dbname = 'ecomerce';
$username = 'root';  // Replace with your database username
$password = '';  // Replace with your password

$con = new mysqli($host,$username,$password,$dbname);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}
