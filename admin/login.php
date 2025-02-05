<?php
session_start(); // Start session

$message = '';

// Load users from JSON file
$usersJson = file_get_contents('data/users.json');
$users = json_decode($usersJson, true);

// Check if the login form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Check if username exists in the array
    if (array_key_exists($username, $users)) {
        // Verify password
        if (password_verify($password, $users[$username])) {
            $_SESSION['username'] = $username; // Store username in session
            header('Location: index.php'); // Redirect to main page
            exit();
        } else {
            $message = 'Username or password is incorrect!';
        }
    } else {
        $message = 'Username or password is incorrect!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-bold mb-6 text-center">Login Form</h2>
        <form method="POST" action="">
            <div class="mb-4">
                <label for="username" class="block text-sm font-medium text-gray-700">Username:</label>
                <input type="text" id="username" name="username" required
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
                <input type="password" id="password" name="password" required
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded-md hover:bg-blue-600">Login</button>
        </form>
        <?php if ($message): ?>
            <p class="mt-4 text-red-500 text-sm text-center"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
    </div>
</body>

</html>