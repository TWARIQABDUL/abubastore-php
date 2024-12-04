<?php


define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH .'/controllers/CartController.php';
require_once BASE_PATH .'/config/databaseconnection.php';


$cartController = new CartController($con);
$data = json_decode(file_get_contents('php://input'), true);

print_r($cartController->getCartItems($data['user_id']));