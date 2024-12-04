<?php
// define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH .'/middleware/AuthMiddleware.php';


class AuthController{
    private $midlleWare;
    private $isValid;
    private $headers;
    public function __construct(){
        $this->headers = getallheaders();
        $this->midlleWare = new AuthMiddleware();
    }
    public function authValidityCheck(): bool{
        // echo $this->headers['Authorization'];
        
        if (!isset($this->headers['Authorization'])) {
            echo json_encode(["message" => "Authorization header not found."]);
        }
        $this->isValid = $this->midlleWare->handle();
        return $this->isValid;
    }
    
}

$test = new AuthController();

echo $test->authValidityCheck();
// $middleWare = new AuthMiddleware();
// if (isset($headers['Authorization'])) {
//     $authHeader = $headers['Authorization'];
//     $isvalid = $middleWare->handle();
//     print_r($isvalid);
//     // echo "Authorization Header: " . $isvalid;
// } else {
//     echo "Authorization header not found.";
// }
// Access a specific header


