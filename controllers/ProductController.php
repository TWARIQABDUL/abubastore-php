<?php
require_once BASE_PATH .'/models/Product.php';

class ProductController
{

    private $product;

    public function __construct($con)
    {
        $this->product = new Product($con);
    }

    // Handle getting all products
    public function getAllProducts()
    {
        $products = $this->product->getAllProducts();
        // echo $products;
        echo json_encode($products);
    }

    // Handle getting a product by id
    public function getProductById($id)
    {
        $product = $this->product->getProductById($id);
        echo json_encode($product);
    }

    // Handle creating a new product
    public function createProduct()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        // echo json_encode($data);
        // $data = json_decode(file_get_contents('../'), true);
        if (isset($data['name'], $data['description'], $data['price'], $data['stock_quantity'], $data['category_id'])) {
            $this->product->createProduct($data['id'],$data['name'], $data['description'], $data['price'], $data['stock_quantity'], $data['category_id']);
            echo json_encode(["message" => "Product created successfully."]);
        } else {
            echo json_encode(["message" => "Invalid input."]);
        }
    }
}
