<?php
// define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH .'/helpers/JwtHelper.php';

// class UserController {
//     public function login($email, $password) {
//         // Simulate database lookup
//         $user = $this->getUserByEmail($email);

//         if ($user && password_verify($password, $user['password'])) {
//             // Create JWT token
//             $payload = [
//                 "id" => $user['id'],
//                 "username" => $user['username'],
//                 "email" => $user['email'],
//                 "iat" => time(),
//                 "exp" => time() + (60 * 60) // Token valid for 1 hour
//             ];

//             $jwt = JwtHelper::encode($payload);

//             echo json_encode(["token" => $jwt]);
//         } else {
//             http_response_code(401);
//             echo json_encode(["error" => "Invalid credentials."]);
//         }
//     }

//     private function getUserByEmail($email) {
//         // Simulated database result
//         $users = [
//             "user@example.com" => [
//                 "id" => 1,
//                 "username" => "example_user",
//                 "password" => password_hash("password123", PASSWORD_BCRYPT)
//             ]
//         ];

//         return $users[$email] ?? null;
//     }
// }
// class UserController {
//     private $conn;

//     public function __construct($con) {
//         $this->conn = $con;
//     }

//     public function login($email, $password) {
//         if ($this->conn){
//             echo "nice con";
//         }
//         $query = "SELECT * FROM users WHERE email = ?";
//         $stmt = $this->conn->prepare($query);
//         // $stmt->bind_param("s", $email);
//         // $stmt->execute();
//         // $result = $stmt->get_result();
//         // $user = $result->fetch_assoc();

//         // if ($user && password_verify($password, $user['password'])) {
//         //     $token = $this->generateToken($user['id'], $user['username']);
//         //     echo json_encode(["token" => $token]);
//         // } else {
//         //     // http_response_code(401);
//         //     echo json_encode(["error" => "Invalid email or password"]);
//         // }
//     }

//     private function generateToken($userId, $username) {
//         $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
//         $payload = base64_encode(json_encode([
//             'id' => $userId,
//             'username' => $username,
//             'exp' => time() + (60 * 60) // Token expires in 1 hour
//         ]));
//         $signature = hash_hmac('sha256', "$header.$payload", "your_secret_key", true);
//         return "$header.$payload." . base64_encode($signature);
//     }
// }
class UserController {
    private $jwthelper;
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->jwthelper = new JwtHelper();
    }

    
    public function login($email, $password) {
        // Query to select the user based on email
        $query = "SELECT passwords,user_id FROM users WHERE email = ?";
        
        // Prepare the statement
        if ($stmt = $this->conn->prepare($query)) {
            // Bind the input parameter (email)
            $stmt->bind_param("s", $email); // "s" is for string (email)
            
            // Execute the query
            $stmt->execute();
            
            // Get the result of the query
            $result = $stmt->get_result();
            
            // Check if any user is found with the provided email
            if ($row = $result->fetch_assoc()) {
                // Check if the password is correct
                if ($password == $row['passwords']) {
                    $userData = [
                        'id' => $row['user_id'],            // Example user ID
                        'email' => $email,    // Email
                        'exp' => time() + 3600  // Token expiration (1 hour)
                    ];
                    // If password is correct, return a success message (or generate token if you're using JWT)
                    $jwt = $this->jwthelper->encode($userData);
                    echo json_encode(["message" => "Token generated","token"=> 'Bearer ' . $jwt]);
                } else {
                    echo json_encode(["error" => "Invalid password"]);
                }
            } else {
                echo json_encode(["error" => "No user found with that email"]);
            }
            
            // Close the statement
            $stmt->close();
        } else {
            echo json_encode(["error" => "Failed to prepare query"]);
        }
    }
    
}
?>
