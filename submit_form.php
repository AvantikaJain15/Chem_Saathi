<?php
// Database connection
$servername = "localhost";
$username = "root"; // Change to your MySQL username
$password = ""; // Change to your MySQL password
$dbname = "user_system";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check which form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if it's a signup or login
    if (isset($_POST['confirm_password'])) {
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            die("Passwords do not match!");
        }

        // Prepare and bind for signup
        $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $hashed_password);

        // Execute the query
        if ($stmt->execute()) {
            echo "Signup successful!";
            // Redirect to login or dashboard
            header("Location: login.html");
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close statement and connection
        $stmt->close();
    } else {
        // Prepare and bind for login
        $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($stored_hash);
            $stmt->fetch();

            // Verify the password
            if (password_verify($password, $stored_hash)) {
                echo "Login successful!";
                // Redirect to dashboard or other page
                header("Location: dashboard.html");
            } else {
                echo "Invalid credentials!";
            }
        } else {
            echo "Invalid credentials!";
        }

        $stmt->close();
    }

    $conn->close();
}
?>
