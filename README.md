REthis is the backend app for abuba e-commerce

for now the default endpoint for every thing is index.php using this end point You can only get all products and 
get product by specifying Id. No authorization is required at all to perform any of this actions exept onlyfor adding product

here is all my endpoints

1. **Add Product**

**/add-product**

This endpoint allows you to add a new product to the ecommerce platform.
Request Body
id (number) - The unique identifier for the product.
name (string) - The name of the product.
description (string) - A brief description of the product.
price (number) - The price of the product.
stock_quantity (number) - The available quantity of the product in stock.
category_id (string) - The category identifier for the product.

Response
Upon successful creation of the product, the API returns a status code of 200 and a message indicating 
that the product was created successfully.
Example:


JSON

```js
{"message":"Product created successfully."}
```


Request Headers
Authorization:
Bearer xxxxxxxxxxx

Body
raw (json)
json
```json
{
    "id": 763423,
    "name": "Roy loyce",
    "description": "A great Car for everyone",
    "price": 12.87,
    "stock_quantity": 3,
    "category_id": "women213"
}

```
2.**GET /abuba-ecommerce-backend/**
This endpoint retrieves a list of products from the Abuba E-commerce backend.
Request
No request body is required for this endpoint.
Response
The response is a JSON array containing objects with the following properties:
id (string): The unique identifier of the product.
p_name (string): The name of the product.
p_discription (string): The description of the product.
price (string): The price of the product.
s_quantity (string): The available quantity of the product.
category_id (string): The unique identifier of the category to which the product belongs.

Example:


JSON
```json
[
    {
        "id": "",
        "p_name": "",
        "p_discription": "",
        "price": "",
        "s_quantity": "",
        "category_id": ""
    }
]
```


JSON Schema


JSON
```json
{
    "type": "array",
    "items": {
        "type": "object",
        "properties": {
            "id": { "type": "string" },
            "p_name": { "type": "string" },
            "p_discription": { "type": "string" },
            "price": { "type": "string" },
            "s_quantity": { "type": "string" },
            "category_id": { "type": "string" }
        }
    }
}
```

