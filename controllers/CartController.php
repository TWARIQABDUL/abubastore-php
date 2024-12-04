<?php

class CartController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }


    public function addToCart($userId, $productId, $quantity)
    {
        try {
            // Step 1: Check if the user has an active cart
            $cartId = $this->getActiveCart($userId);

            if (!$cartId) {
                // Create a new cart if no active cart exists
                $cartId = $this->createCart($userId);
                if ($cartId) {
                    echo "Cart created successfully!";
                } else {
                    throw new Exception("Error creating cart: " . $this->conn->error);
                }
            }

            // Step 2: Check if the product is available in stock
            $query = "SELECT s_quantity, price FROM products WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $productId);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            if (!$product) {
                throw new Exception("Product not found");
            }

            $availableStock = $product['s_quantity'];

            if ($availableStock < $quantity) {
                throw new Exception("Insufficient stock. Available stock: $availableStock");
            }

            // Step 3: Check if the product is already in the cart
            $query = "SELECT quantity, total_price FROM cart_items WHERE cart_id = ? AND product_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $cartId, $productId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                // Update the quantity and total price
                $newQuantity = $row['quantity'] + $quantity;
                $newTotalPrice = $this->calculateTotalPrice($productId, $newQuantity);

                $updateQuery = "UPDATE cart_items SET quantity = ?, total_price = ? WHERE cart_id = ? AND product_id = ?";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->bind_param("diii", $newQuantity, $newTotalPrice, $cartId, $productId);
                $updateStmt->execute();
            } else {
                // Add the product to the cart
                $totalPrice = $this->calculateTotalPrice($productId, $quantity);

                $insertQuery = "INSERT INTO cart_items (cart_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)";
                $insertStmt = $this->conn->prepare($insertQuery);
                $insertStmt->bind_param("iiid", $cartId, $productId, $quantity, $totalPrice);
                $insertStmt->execute();
            }

            // Step 4: Deduct the stock quantity from the products table
            // $newStockQuantity = $availableStock - $quantity;
            // $updateStockQuery = "UPDATE products SET s_quantity = ? WHERE id = ?";
            // $updateStockStmt = $this->conn->prepare($updateStockQuery);
            // $updateStockStmt->bind_param("is", $newStockQuantity, $productId);
            // if (!$updateStockStmt->execute()) {
            //     throw new Exception("Failed to update product stock: " . $this->conn->error);
            // }

            echo json_encode(["message" => "Product added to cart successfully"]);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    private function getActiveCart($userId)
    {
        $query = "SELECT id FROM cart WHERE user_id = ? AND status = 'active'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['id'];
        }
        return null;
    }

    private function createCart($userId)
    {
        $expiresAt = (new DateTime())->modify('+24 hours')->format('Y-m-d H:i:s');
        // echo $expiresAt;
        $query = "INSERT INTO cart (user_id, status, created_at, updated_at,expires_at) VALUES (?, 'active', NOW(), NOW(),?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $userId, $expiresAt);  // Use "i" instead of "s" for integers
        if (!$stmt->execute()) {
            throw new Exception("Error executing query: " . $this->conn->error);
        }
        return $this->conn->insert_id; // Return the new cart ID
    }

    private function calculateTotalPrice($productId, $quantity)
    {
        // Fetch product price from the database
        $query = "SELECT price FROM products WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            return $row['price'] * $quantity;
        }
        throw new Exception("Product not found");
    }

    // Check if cart exists for the user
    public function checkIfCartExists($userId)
    {
        $query = "SELECT id FROM cart WHERE user_id = ? AND status = 'active'";
        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                // Return cart ID if it exists
                $row = $result->fetch_assoc();
                return $row['id'];
            }
        }
        return null; // No active cart found for this user
    }

    // Get all items in the user's cart
    public function getCartItems($userId)
    {
        // Step 1: Check if the user has an active cart
        $cartId = $this->checkIfCartExists($userId);

        if (!$cartId) {
            return []; // No cart found for the user
        }

        // Step 2: Retrieve all items in the cart
        $query = "SELECT ci.ID AS cart_item_id, p.p_name AS product_name, ci.quantity, ci.total_price, p.price AS product_price
                  FROM cart_items ci 
                  JOIN products p ON ci.product_id = p.id
                  WHERE ci.cart_id = ?";

        if ($stmt = $this->conn->prepare($query)) {
            $stmt->bind_param("i", $cartId);
            $stmt->execute();
            $result = $stmt->get_result();

            // Step 3: Fetch all cart items
            $cartItems = [];
            while ($row = $result->fetch_assoc()) {
                $cartItems[] = [
                    'cart_item_id' => $row['cart_item_id'],
                    'product_name' => $row['product_name'],
                    'quantity' => $row['quantity'],
                    'total_price' => $row['total_price'],
                    'product_price' => $row['product_price']
                ];
            }

            return json_encode($cartItems); // Return the cart items
        }

        return []; // Return empty if something goes wrong
    }
    // Function to remove a single item from the cart
    public function removeCartItem($cartItemId)
    {
        try {
            // Step 1: Get the cart item details
            $query = "SELECT product_id, quantity FROM cart_items WHERE ID = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $cartItemId);
            $stmt->execute();
            $result = $stmt->get_result();
            $cartItem = $result->fetch_assoc();

            if (!$cartItem) {
                throw new Exception("Cart item not found");
            }

            $productId = $cartItem['product_id'];
            $quantity = $cartItem['quantity'];

            // Step 2: Update the stock in the products table
            $updateStockQuery = "UPDATE products SET s_quantity = s_quantity + ? WHERE id = ?";
            $updateStockStmt = $this->conn->prepare($updateStockQuery);
            $updateStockStmt->bind_param("ii", $quantity, $productId);
            if (!$updateStockStmt->execute()) {
                throw new Exception("Failed to update product stock: " . $this->conn->error);
            }

            // Step 3: Remove the item from the cart_items table
            $deleteQuery = "DELETE FROM cart_items WHERE ID = ?";
            $deleteStmt = $this->conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $cartItemId);
            if (!$deleteStmt->execute()) {
                throw new Exception("Failed to remove item from cart: " . $this->conn->error);
            }

            echo json_encode(["message" => "Item removed from cart successfully"]);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
    }

    // Function to delete the entire cart
    public function deleteCart($userId)
    {
        try {
            // Step 1: Get the user's active cart ID
            $cartId = $this->getActiveCart($userId);

            if (!$cartId) {
                throw new Exception("No active cart found for this user");
            }

            // Step 2: Get all items in the cart
            $query = "SELECT product_id, quantity FROM cart_items WHERE cart_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $cartId);
            $stmt->execute();
            $result = $stmt->get_result();

            // Step 3: Update stock for each item in the cart
            while ($row = $result->fetch_assoc()) {
                $productId = $row['product_id'];
                $quantity = $row['quantity'];

                $updateStockQuery = "UPDATE products SET s_quantity = s_quantity + ? WHERE id = ?";
                $updateStockStmt = $this->conn->prepare($updateStockQuery);
                $updateStockStmt->bind_param("ii", $quantity, $productId);
                if (!$updateStockStmt->execute()) {
                    throw new Exception("Failed to update product stock: " . $this->conn->error);
                }
            }

            // Step 4: Delete all items from the cart_items table
            $deleteItemsQuery = "DELETE FROM cart_items WHERE cart_id = ?";
            $deleteItemsStmt = $this->conn->prepare($deleteItemsQuery);
            $deleteItemsStmt->bind_param("i", $cartId);
            if (!$deleteItemsStmt->execute()) {
                throw new Exception("Failed to delete cart items: " . $this->conn->error);
            }

            // Step 5: Delete the cart from the cart table
            $deleteCartQuery = "DELETE FROM cart WHERE id = ?";
            $deleteCartStmt = $this->conn->prepare($deleteCartQuery);
            $deleteCartStmt->bind_param("i", $cartId);
            if (!$deleteCartStmt->execute()) {
                throw new Exception("Failed to delete cart: " . $this->conn->error);
            }

            echo json_encode(["message" => "Cart deleted successfully"]);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
    public function applyCoupon($userId, $couponCode)
    {
        try {
            // Step 1: Validate the coupon
            $query = "SELECT id, discount_percentage, expiry_date, minimum_order_value FROM coupons WHERE code = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $couponCode);
            $stmt->execute();
            $result = $stmt->get_result();
            $coupon = $result->fetch_assoc();

            if (!$coupon) {
                throw new Exception("Invalid coupon code.");
            }

            // Check if the coupon has expired
            if (new DateTime() > new DateTime($coupon['expiry_date'])) {
                throw new Exception("This coupon has expired.");
            }

            // Step 2: Get the user's active cart
            $cartId = $this->getActiveCart($userId);
            if (!$cartId) {
                throw new Exception("No active cart found.");
            }

            // Check if the coupon is already applied
            $query = "SELECT coupon_id FROM cart WHERE id = ? AND coupon_id IS NOT NULL";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $cartId);
            $stmt->execute();
            $result = $stmt->get_result();
            $cart = $result->fetch_assoc();

            if ($cart && $cart['coupon_id'] === $coupon['id']) {
                throw new Exception("This coupon has already been applied to your cart.");
            }

            // Step 3: Get cart subtotal
            $query = "SELECT SUM(total_price) AS subtotal FROM cart_items WHERE cart_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $cartId);
            $stmt->execute();
            $result = $stmt->get_result();
            $cartItems = $result->fetch_assoc();
            $subtotal = $cartItems['subtotal'] ?? 0;

            // Check if the minimum order value is met
            if ($subtotal < $coupon['minimum_order_value']) {
                throw new Exception("This coupon requires a minimum order of {$coupon['minimum_order_value']}.");
            }

            // Step 4: Calculate the discount
            $discountAmount = $subtotal * ($coupon['discount_percentage'] / 100);
            $newTotal = $subtotal - $discountAmount;

            // Step 5: Update the cart with the coupon
            $query = "UPDATE cart SET coupon_id = ?, discount_amount = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("idi", $coupon['id'], $discountAmount, $cartId);
            $stmt->execute();

            return [
                "message" => "Coupon applied successfully!",
                "subtotal" => round($subtotal, 2),
                "discount" => round($discountAmount, 2),
                "total" => round($newTotal, 2)
            ];
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function removeCoupon($userId)
    {
        try {
            // Step 1: Get the user's active cart
            $cartId = $this->getActiveCart($userId);
            if (!$cartId) {
                throw new Exception("No active cart found.");
            }

            // Step 2: Remove the coupon from the cart
            $query = "UPDATE cart SET coupon_id = NULL, discount_amount = 0 WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $cartId);
            $stmt->execute();

            return ["message" => "Coupon removed successfully!"];
        } catch (Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }
    // expires cart
    public function expireCarts()
    {
        try {
            // Get all expired carts
            $query = "
            SELECT c.id AS cart_id, ci.product_id, ci.quantity 
            FROM cart c 
            LEFT JOIN cart_items ci ON c.id = ci.cart_id 
            WHERE c.status = 'Active' AND c.expires_at <= NOW()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();

            $expiredCarts = [];
            while ($row = $result->fetch_assoc()) {
                $expiredCarts[$row['cart_id']][] = [
                    'product_id' => $row['product_id'],
                    'quantity' => $row['quantity']
                ];
            }

            // Update cart statuses and restore stock
            foreach ($expiredCarts as $cartId => $items) {
                // Mark the cart as expired
                $query = "UPDATE cart SET status = 'Abandoned' WHERE id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("i", $cartId);
                $stmt->execute();

                // Restore product stock
                foreach ($items as $item) {
                    $query = "UPDATE products SET s_quantity = s_quantity + ? WHERE id = ?";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bind_param("ii", $item['quantity'], $item['product_id']);
                    $stmt->execute();
                }
            }

            echo json_encode(["message" => "Expired carts processed successfully."]);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
    public function addToWishlist($userId, $productId)
    {
        try {
            // Check if the product is already in the wishlist
            $query = "SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $userId, $productId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                throw new Exception("Product is already in the wishlist.");
            }

            // Insert product into the wishlist
            $insertQuery = "INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bind_param("si", $userId, $productId);

            if ($insertStmt->execute()) {
                echo json_encode(["message" => "Product added to wishlist successfully."]);
            } else {
                throw new Exception("Error adding product to wishlist: " . $this->conn->error);
            }
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
    public function removeFromWishlist($userId, $productId)
    {
        try {
            $query = "DELETE FROM wishlists WHERE user_id = ? AND product_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $userId, $productId);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Product removed from wishlist successfully."]);
            } else {
                throw new Exception("Error removing product from wishlist: " . $this->conn->error);
            }
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
    public function getWishlistItems($userId)
    {
        $wishlistItems = [];
        try {
            $query = "SELECT w.id AS wishlist_id, p.id AS product_id, p.p_name AS product_name, p.price
                  FROM wishlists w
                  JOIN products p ON w.product_id = p.id
                  WHERE w.user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $userId);
            $stmt->execute();
            $result = $stmt->get_result();


            while ($row = $result->fetch_assoc()) {
                $wishlistItems[] = $row;
            }

            echo json_encode($wishlistItems);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
        // return json_encode($wishlistItems);
    }
    public function moveToCart($userId, $productId, $quantity)
    {
        try {
            // Add to cart
            $this->addToCart($userId, $productId, $quantity);

            // Remove from wishlist
            $this->removeFromWishlist($userId, $productId);

            echo json_encode(["message" => "Product moved from wishlist to cart successfully."]);
        } catch (Exception $e) {
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
    public function checkout($userId)
    {
        $this->conn->begin_transaction(); // Start transaction
        try {
            // Step 1: Get the user's active cart
            $cartId = $this->getActiveCart($userId);
            if (!$cartId) {
                throw new Exception("No active cart found.");
            }

            // Step 2: Fetch cart items
            $query = "SELECT ci.product_id, ci.quantity, p.s_quantity 
                  FROM cart_items ci 
                  JOIN products p ON ci.product_id = p.id 
                  WHERE ci.cart_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $cartId);
            $stmt->execute();
            $result = $stmt->get_result();

            $cartItems = [];
            while ($row = $result->fetch_assoc()) {
                $cartItems[] = $row;
            }

            if (empty($cartItems)) {
                throw new Exception("Your cart is empty.");
            }

            // Step 3: Check inventory for each item
            foreach ($cartItems as $item) {
                if ($item['quantity'] > $item['s_quantity']) {
                    throw new Exception("Product ID " . $item['product_id'] . " has insufficient stock.");
                }
            }

            // Step 4: Deduct inventory
            foreach ($cartItems as $item) {
                $newStock = $item['s_quantity'] - $item['quantity'];
                $updateStockQuery = "UPDATE products SET s_quantity = ? WHERE id = ?";
                $updateStmt = $this->conn->prepare($updateStockQuery);
                $updateStmt->bind_param("ii", $newStock, $item['product_id']);
                $updateStmt->execute();
            }

            // Step 5: Mark cart as completed
            $updateCartQuery = "UPDATE cart SET status = 'completed', updated_at = NOW() WHERE id = ?";
            $updateCartStmt = $this->conn->prepare($updateCartQuery);
            $updateCartStmt->bind_param("i", $cartId);
            $updateCartStmt->execute();

            $this->conn->commit(); // Commit transaction
            echo json_encode(["message" => "Checkout successful!"]);
        } catch (Exception $e) {
            $this->conn->rollback(); // Rollback on failure
            echo json_encode(["error" => $e->getMessage()]);
        }
    }
}
