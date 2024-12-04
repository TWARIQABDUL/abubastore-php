<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH .'/controllers/ProductController.php';
require_once BASE_PATH .'/config/databaseconnection.php';
require_once BASE_PATH .'/controllers/AuthController.php';


$productController = new ProductController($con);
$authController = new AuthController();

if ($authController->authValidityCheck()) {
    $productController->createProduct();
}else{
    echo "fuck";
}

