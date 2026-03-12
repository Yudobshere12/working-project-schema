<?php
session_start();
// Database connection
$conn = new mysqli("localhost", "root", "", "working_project_schema");

$error = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST["username"]);
    $password = $conn->real_escape_string($_POST["password"]);

    $result = $conn->query("SELECT * FROM users WHERE username='$username' AND password='$password'");

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // SAVE DATA TO SESSION
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = strtolower(trim($user['role'])); // Force lowercase to avoid mismatch

        // REDIRECT BASED ON ROLE
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Gas System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #2ec4b6, #0a2540); height: 100vh; display: flex; justify-content: center; align-items: center; }
        .login-card { background: white; padding: 30px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
<div class="login-card">
    <h2 class="text-center">Login</h2>
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required></div>
        <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
        <button type="submit" class="btn btn-primary w-100">Sign In</button>
    </form>
    <div class="text-center mt-3"><a href="register.php">Create Account</a></div>
</div>
</body>
</html>