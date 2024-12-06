<?php

require_once BASE_PATH . '/config/databaseconnection.php';
function createGuestUser($conn) {
    $userID = generateTimestampID();
    $name = "Guest"; // Default name for guest users
    $email = null; // Null email for guests
    $password = null; // No password for guests
    $address = null; // No address for guests
    $phoneNumber = null; // No phone number for guests

    $stmt = $conn->prepare("INSERT INTO users (user_id, name, email, passwords, address, phonenumber) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $userID, $name, $email, $password, $address, $phoneNumber);

    if ($stmt->execute()) {
        // Store the guest user ID in the session
        session_start();
        $_SESSION['user_id'] = $userID;
        echo json_encode(["user_id"=>$userID]);
    } else {
        echo "Error creating guest user: " . $stmt->error;
    }

    $stmt->close();
}

// Call the function to create a guest user
// createGuestUser($con);

// $conn->close();

function generateTimestampID() {
    return date('YmdHis') . bin2hex(random_bytes(2));
}
// echo generateTimestampID(); // Example: 20231203123456ab3c
