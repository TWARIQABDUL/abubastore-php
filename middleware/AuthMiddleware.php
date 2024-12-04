<?php
// define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH .'/helpers/JwtHelper.php';

// class AuthMiddleware {
//     public function handle() {
//         // Get Authorization header
//         $headers = $this->getAuthorizationHeader();

//         if (!$headers || !isset($headers['Authorization'])) {
//             http_response_code(401);
//             echo json_encode(["error" => "Authorization header missing."]);
//             exit;
//         }

//         $authHeader = $headers['Authorization'];
//         $token = str_replace("Bearer ", "", $authHeader);

//         try {
//             // Decode and validate the token
//             $decoded = JwtHelper::decode($token);
//             return $decoded; // Return user data
//         } catch (Exception $e) {
//             http_response_code(401);
//             echo json_encode(["error" => $e->getMessage()]);
//             exit;
//         }
//     }

//     private function getAuthorizationHeader() {
//         if (isset($_SERVER['Authorization'])) {
//             return ['Authorization' => $_SERVER['Authorization']];
//         } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
//             return ['Authorization' => $_SERVER['HTTP_AUTHORIZATION']];
//         } elseif (function_exists('apache_request_headers')) {
//             $apacheHeaders = apache_request_headers();
//             if (isset($apacheHeaders['Authorization'])) {
//                 return ['Authorization' => $apacheHeaders['Authorization']];
//             }
//         }
//         return null;
//     }
// }

// class AuthMiddleware {
//     private $secretKey = "your_secret_key"; // Replace this with environment variables or a config file

//     // Validate the token
//     public function validateToken($token) {
//         try {
//             // Token structure should be "header.payload.signature"
//             $parts = explode('.', $token);
//             if (count($parts) !== 3) {
//                 throw new Exception('Invalid token structure');
//             }

//             $payload = base64_decode($parts[1]);
//             if ($payload === false) {
//                 throw new Exception('Failed to decode payload');
//             }

//             // Decode payload
//             $data = json_decode($payload, true);
//             if (json_last_error() !== JSON_ERROR_NONE) {
//                 throw new Exception('Invalid JSON in payload');
//             }

//             // Check for expiration
//             if ($data['exp'] < time()) {
//                 throw new Exception('Token has expired');
//             }
//             // print_r($data);
//             return $data; // Return the user data from the payload

//         } catch (Exception $e) {
//             http_response_code(401);
//             echo json_encode(["error" => $e->getMessage()]);
//             exit;
//         }
//     }

//     // Middleware function to handle requests
//     public function handle() {
//         // Get the headers
//         $headers = apache_request_headers();

//         // Check if the 'Authorization' header exists
//         if (!isset($headers['Authorization'])) {
//             http_response_code(401);
//             echo json_encode(["error" => "Unauthorized access. Missing Authorization header."]);
//             exit;
//         }

//         // Extract the token from the 'Authorization' header
//         $token = str_replace('Bearer ', '', $headers['Authorization']);
//         if (empty($token)) {
//             http_response_code(401);
//             echo json_encode(["error" => "Unauthorized access. Invalid token format."]);
//             exit;
//         }

//         // Validate the token and return user data if valid
//         return $this->validateToken($token);
//     }
// }

class AuthMiddleware {
    private $secretKey;
    private bool $isValid = false;
    public function __construct() {
        $this->secretKey = "Hp12Hl24Kn123!"; // Retrieve the secret key from environment variables
    }

    public function validateToken($token) {
        try {
            $parts = explode('.', $token);
            // print_r($parts);
            if (count($parts) !== 3) {
                throw new Exception('Invalid token structure');
            }

            $payload = base64_decode($parts[1]);
            if ($payload === false) {
                throw new Exception('Failed to decode payload');
            }

            $data = json_decode($payload, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON in payload');
            }

            if ($data['exp'] < time()) {
                throw new Exception('Token has expired');
            }

            // Validate signature
            // echo "secret = ".$this->secretKey;
            $expectedSignature = hash_hmac('sha256', "$parts[0].$parts[1]", $this->secretKey, true);
            $signature = self::base64UrlDecode($parts[2]);
            if (!hash_equals($expectedSignature, $signature)) {
                throw new Exception('Invalid token signature');
            }

            return !$this->isValid; // Return user data
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(["error" => $e->getMessage()]);
            exit;
        }
    }

    public function handle() {
        $headers = apache_request_headers();

        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(["error" => "Unauthorized access. Missing Authorization header."]);
            exit;
        }
        // echo $headers['Authorization'];
        $token = str_replace('Bearer ', '', $headers['Authorization']);
        // echo $token;
        if (empty($token)) {
            http_response_code(401);
            echo json_encode(["error" => "Unauthorized access. Invalid token format."]);
            exit;
        }

        return $this->validateToken($token);
    }
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}

