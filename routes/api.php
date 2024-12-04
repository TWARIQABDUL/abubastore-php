<?php
require_once BASE_PATH . '/controllers/ProductController.php';
require_once BASE_PATH . '/config/databaseconnection.php';
require_once BASE_PATH . '/controllers/UserController.php';
require_once BASE_PATH . '/middleware/AuthMiddleware.php';
require_once BASE_PATH . '/controllers/CartController.php';

$productController = new ProductController($con);
$userController = new UserController($con);
$cartController = new CartController($con);

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$method = $_SERVER['REQUEST_METHOD'];
echo $uri;
switch ($uri) {
    case 'abuba-ecommerce-backend/product':
        if ($method === 'GET') {
            if (isset($_GET['id'])) {
                $productId = intval($_GET['id']); // Sanitize the input
                $productController->getProductById($productId);
            } else {
                $productController->getAllProducts();
            }
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;

    case 'abuba-ecommerce-backend/login':
        if ($method === 'POST') {
            // echo json_encode(["message" => "well here"]);
            $data = json_decode(file_get_contents('php://input'), true);
            // echo $data;
            $userController->login($data['email'], $data['password']);
            // $userData = $authMiddleware->handle();

        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;
    case 'abuba-ecommerce-backend/add-product':
        if ($method === 'POST') {
            if ($authController->authValidityCheck()) {
                $productController->createProduct();
            }
        } else {
            http_response_code(405); // Method Not Allowed

            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;
    case 'abuba-ecommerce-backend/check-access':
        require_once BASE_PATH . '/controllers/AuthController.php';

        $authController = new AuthController();

        if ($method === 'POST') {
            $midleWare = new AuthMiddleware();

            $status = $midleWare->handle();

            echo $status;
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;
    case 'abuba-ecommerce-backend/add-to-cart':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);

            $cartController->addToCart($data['user_id'], $data['product_id'], $data['quantity']);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;
    case 'abuba-ecommerce-backend/mycart':
        if ($method === 'GET') {
            $data = json_decode(file_get_contents('php://input'), true);
            $cart = $cartController->getCartItems($data['user_id']);
            echo $cart;
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;
    case 'abuba-ecommerce-backend/delete-item':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $cartController->removeCartItem($data['id']);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;
    case 'abuba-ecommerce-backend/delete-cart':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $cartController->deleteCart($data['user_id']);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;
    case 'abuba-ecommerce-backend/cupons':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $cartController->applyCoupon($data['user_id'], $data['couponCode']);
            echo json_encode($response);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;
    case 'abuba-ecommerce-backend/remove-cupons':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $response = $cartController->removeCoupon($data['user_id']);
            echo json_encode($response);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;
    case 'abuba-ecommerce-backend/cart/update':
        if ($method === 'POST') {
            $cartController->expireCarts();
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;
    case 'abuba-ecommerce-backend/wishlist':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $cartController->addToWishlist($data['user_id'], $data['product_id']);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;
    case 'abuba-ecommerce-backend/wishlist/delete':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $cartController->removeFromWishlist($data['user_id'], $data['product_id']);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;
    case 'abuba-ecommerce-backend/wishlist/list':
        if ($method === 'GET') {
            $data = json_decode(file_get_contents('php://input'), true);
            $cartController->getWishlistItems($data['user_id']);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;
    case 'abuba-ecommerce-backend/wishlist/migrate':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $cartController->moveToCart($data['user_id'], $data['product_id'], $data['quantity']);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;
    case 'abuba-ecommerce-backend/checkout':
        if ($method === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $cartController->checkout($data['user_id']);
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "error" => "Method Not Allowed",
                "message" => "You cannot use $method on this endpoint"
            ]);
        }
        break;
    default:
        http_response_code(404);
        echo json_encode(["error" => "Endpoint not found"]);
        break;
}
