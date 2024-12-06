<?php
// define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/config/databaseconnection.php';
class Product
{
    private $conn;
    private $table = 'products';


    public function __construct($con)
    {
        $this->conn = $con;
    }


    public function getAllProducts()
    {


        $query = "SELECT p.id, p.p_name , p.p_discription AS description,  p.price,p.rank, p.s_quantity AS stock_quantity, c.collection_name
FROM 
    $this->table p
LEFT JOIN 
    collection c
ON 
    p.collection_id = c.collection_id;
";

        $result = $this->conn->query($query);
        if (!$result) {
            die("Error: " . $this->conn->error);
        }
        $products = [];
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }

        return $products;
    }
    public function getProductById($id)
    {
        // Prepare the query to fetch a product by ID
        $query = "SELECT * FROM " . $this->table . " WHERE id = ?";

        // Initialize the prepared statement
        $stmt = $this->conn->prepare($query);

        // Bind the parameter (id) to the prepared statement
        $stmt->bind_param("i", $id);  // "i" indicates the parameter is an integer

        // Execute the statement
        $stmt->execute();

        // Get the result
        $result = $stmt->get_result();

        // Check if the product is found
        if ($result->num_rows > 0) {
            // Fetch the product as an associative array
            return $result->fetch_assoc();
        } else {
            // If no product is found, return null or an error message
            return json_encode(["message" => "Item not Found"]);
        }
    }

    // Function to create a new product in the database
    public function createProduct($id, $name, $description, $price, $stock_quantity, $category_id)
    {
        // Prepare the query to insert a new product
        $query = "INSERT INTO " . $this->table . " (`id`, `p_name`, `p_discription`, `price`, `s_quantity`, `category_id`) 
                  VALUES (?,?, ?, ?, ?,?)";

        // Initialize the prepared statement
        $stmt = $this->conn->prepare($query);

        // Bind the parameters to the prepared statement
        $stmt->bind_param("issdis", $id, $name, $description, $price, $stock_quantity, $category_id);
        // "s" for string (name, description), "d" for double (price), "i" for integer (stock_quantity, category_id)

        // Execute the statement
        if ($stmt->execute()) {
            return true;  // Return true if the product is created successfully
        } else {
            return false; // Return false if there is an error
        }
    }
}
