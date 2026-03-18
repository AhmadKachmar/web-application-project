<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Securely hash the password

    // Database connection
    $conn = new mysqli("127.0.0.1", "root", "", "users"); 

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Check if username or email exists
    $checkQuery = "SELECT * FROM userinfo WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['username'] === $username) {
            // Username already exists
            header("Location: LoginPage.html?error=username_taken");
        } elseif ($row['email'] === $email) {
            // Email already exists
            header("Location: LoginPage.html?error=email_taken");
        }
        exit();
    } else {
        // If no duplicates, proceed with insertion
        $stmt = $conn->prepare("INSERT INTO userinfo (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $password);

        if ($stmt->execute()) {
            // Fetch the auto-generated user_id
            $user_id = $conn->insert_id; // Retrieve the ID of the newly created record
            $_SESSION['user_id'] = $user_id; // Store user_id in the session
            header("Location: HomePage.html"); // Redirect to the homepage
            exit();
        } else {
            header("Location: LoginPage.html?error=registration_failed");
            exit();
        }
    }

    $stmt->close();
    $conn->close();
}
?>