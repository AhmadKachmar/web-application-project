<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Database connection
    $conn = new mysqli("127.0.0.1", "root", "", "users");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Modify the query to retrieve user_id and hashed password
    $stmt = $conn->prepare("SELECT user_id, password FROM userinfo WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $hashed_password);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Start the session and save user information
            $_SESSION["username"] = $username;
            $_SESSION["user_id"] = $user_id;

            // Redirect to the homepage after successful login
            header("Location: HomePage.html");
            exit();
        } else {
            header("Location: LoginPage.html?error=invalid_password");
            exit();

        }
    } else {
        header("Location: LoginPage.html?error=username_not_found");
        exit();

    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>