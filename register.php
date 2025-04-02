<?php
session_start();
require 'database.php';

$db = new Database();
$conn = $db->getConnection();
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        // Check if the username already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $message = "❌ Username already taken!";
        } else {
            // Register user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashedPassword);
            if ($stmt->execute()) {
                $message = "✅ Registration successful! Please login.";
            } else {
                $message = "❌ Error: " . $stmt->error;
            }
        }
        $checkStmt->close();
    } else {
        $message = "⚠️ Please enter both fields!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex justify-center items-center min-h-screen">
    <div class="bg-gray-700 rounded-lg p-8 shadow-2xl max-w-sm w-full">
        <h2 class="text-center text-3xl text-white mb-6 font-bold">Register</h2>
        <p class="text-center text-yellow-400"><?= $message ?></p>
        <form method="POST">
            <input class="w-full px-3 py-2 mb-4 bg-gray-600 border-gray-700 rounded-md text-white" type="text" name="username" placeholder="Username" required>
            <input class="w-full px-3 py-2 mb-4 bg-gray-600 border-gray-700 rounded-md text-white" type="password" name="password" placeholder="Password" required>
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md" type="submit">Register</button>
        </form>
        <p class="text-center text-gray-400 mt-4">Already have an account? <a href="login.php" class="text-blue-400">Login</a></p>
    </div>
</body>
</html>
