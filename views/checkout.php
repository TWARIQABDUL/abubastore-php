<?php

// Apply Coupon Endpoint

define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/controllers/CartController.php';
require_once BASE_PATH . '/config/databaseconnection.php';

$cartController = new CartController($con);
$data = json_decode(file_get_contents('php://input'), true);

$cartController = new CartController($con);
$cartController->checkout($data['user_id']);
