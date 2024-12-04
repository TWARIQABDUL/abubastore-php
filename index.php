<?php
// header('Content-Type: application/json');
define('BASE_PATH', __DIR__);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
// Load the API routes
require_once BASE_PATH . '/routes/api.php';
