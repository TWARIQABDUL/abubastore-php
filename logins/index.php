<?php
define('BASE_PATH', dirname(__DIR__));
header('Content-Type: application/json');
require_once BASE_PATH . '/controllers/UserController.php';
require_once BASE_PATH . '/config/databaseconnection.php';


$userController = new UserController($con);
// echo $_SERVER['REQUEST_METHOD'];
// echo $_SERVER['REQUEST_URI'];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['REQUEST_URI'], '/abuba-ecommerce-backend/login/') !== false) {
    // echo json_encode(["message" => "well here"]);
    $data = json_decode(file_get_contents('php://input'), true);
    // echo $data;
    $userController->login($data['email'], $data['password']);
    // $userData = $authMiddleware->handle();

} else {
    http_response_code(404);
    echo json_encode(["message" => "Not Founddd"]);
}