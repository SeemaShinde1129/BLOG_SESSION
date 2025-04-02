<?php
session_start();
require 'database.php';

$db = new Database();
$conn = $db->getConnection();
$error = "";

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit();
        } else {
            $error = "❌ Invalid username or password!";
        }
    } else {
        $error = "⚠️ Please enter both fields!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex justify-center items-center min-h-screen">
    <div class="bg-gray-700 rounded-lg p-8 shadow-2xl max-w-sm w-full">
        <h2 class="text-center text-3xl text-white mb-6 font-bold">Login</h2>
        <p class="text-center text-red-400"><?= $error ?></p>
        <form method="POST">
            <input class="w-full px-3 py-2 mb-4 bg-gray-600 border-gray-700 rounded-md text-white" type="text" name="username" placeholder="Username" required>
            <input class="w-full px-3 py-2 mb-4 bg-gray-600 border-gray-700 rounded-md text-white" type="password" name="password" placeholder="Password" required>
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-md" type="submit">Login</button>
        </form>
        <p class="text-center text-gray-400 mt-4">Don't have an account? <a href="register.php" class="text-blue-400">Register</a></p>
    </div>
</body>
</html>
