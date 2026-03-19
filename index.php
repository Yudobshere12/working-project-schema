<?php
session_start();
// Database connection
$conn = new mysqli("localhost", "root", "", "working_project_schema");

$error = ""; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST["username"]);
    $password = $conn->real_escape_string($_POST["password"]);
    $selected_role = strtolower(trim($_POST["role"])); // Get the role from the dropdown

    // We check for Username, Password, AND the matching Role
    $result = $conn->query("SELECT * FROM users WHERE username='$username' AND password='$password' AND role='$selected_role'");

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // SAVE DATA TO SESSION
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = strtolower(trim($user['role']));

        // REDIRECT BASED ON ROLE
        if ($_SESSION['role'] === 'manager') {
            header("Location: manager_dashboard.php");
        } elseif ($_SESSION['role'] === 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php");
        }
        exit();
    } else {
        $error = "Invalid credentials or incorrect role selected!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - Gas System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #2ec4b6, #0a2540); height: 100vh; display: flex; justify-content: center; align-items: center; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .login-card { background: white; padding: 40px; border-radius: 15px; width: 100%; max-width: 420px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); }
        .btn-primary { background-color: #0a2540; border: none; padding: 12px; font-weight: bold; transition: 0.3s; }
        .btn-primary:hover { background-color: #2ec4b6; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-4">
        <h2 class="fw-bold text-dark">GAS-SIMHOT</h2>
        <p class="text-muted small">Access Your Control Dashboard</p>
    </div>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger py-2 small text-center"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label small fw-bold">Username</label>
            <input type="text" name="username" class="form-control" placeholder="Enter username" required>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>
        
        <div class="mb-4">
            <label class="form-label small fw-bold">Login As</label>
            <select name="role" class="form-select" required>
                <option value="staff">User</option>
                <option value="admin">Admin</option>
                <option value="manager">Manager</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100 shadow-sm">Sign In</button>
    </form>
    
    <div class="text-center mt-4">
        <small class="text-muted">Don't have an account? <a href="register.php" class="text-decoration-none">Create Account</a></small>
    </div>
</div>
</body>
</html>