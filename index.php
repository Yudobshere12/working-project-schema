<?php
session_start();

// 1. Database connection
$conn = new mysqli("localhost", "root", "", "working_project_schema");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error_message = "";

// 2. Login Logic
if (isset($_POST['login_submit'])) {
    $username = $conn->real_escape_string($_POST['username']);
    
    // Check sa users table
    $check_user = $conn->query("SELECT * FROM users WHERE username = '$username' LIMIT 1");
    
    if ($check_user->num_rows > 0) {
        $user = $check_user->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit();
    } else {
        $error_message = "Invalid Username! Please try again.";
    }
}

// 3. Logout Logic
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

// 4. Data Queries (Mo-load lang kung naka-login)
if (isset($_SESSION['user_id'])) {
    $ratings = $conn->query("SELECT rating, comment, created_at FROM gas_simhot_ratings ORDER BY created_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
    $users = $conn->query("SELECT id, username, updated_at FROM users ORDER BY updated_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
    $logs = $conn->query("SELECT id, user_id, action, created_at FROM user_activity_logs ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
    $admins = $conn->query("SELECT id, username, updated_at FROM admin ORDER BY updated_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
    $sessions = $conn->query("SELECT id, user_id, created_at, expires_at FROM sessions ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
    $profiles = $conn->query("SELECT id, user_id, email, first_name, last_name, created_at FROM user_profiles ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gas-Simhot | LPG Detector</title>
    <link rel="stylesheet" href="style.php">
    <style>
        /* Modern Design Add-ons */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; }
        .login-container { display: flex; justify-content: center; align-items: center; height: 100vh; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); }
        .login-card { background: white; padding: 2.5rem; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); width: 100%; max-width: 400px; text-align: center; }
        .login-card h2 { color: #333; margin-bottom: 1.5rem; }
        .login-card input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        .login-card button { width: 100%; padding: 12px; background: #28a745; border: none; color: white; border-radius: 8px; cursor: pointer; font-size: 16px; transition: 0.3s; }
        .login-card button:hover { background: #218838; }
        
        nav { background: #333; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav ul { list-style: none; display: flex; gap: 20px; padding: 0; }
        nav a { color: white; text-decoration: none; font-weight: bold; }
        .logout-link { background: #dc3545; padding: 5px 15px; border-radius: 5px; }
        
        .container { padding: 2rem; max-width: 1200px; margin: auto; }
        .monitor-card { background: white; padding: 2rem; border-radius: 12px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        #status-indicator { width: 150px; height: 150px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 20px auto; font-size: 24px; color: white; font-weight: bold; }
        .safe { background-color: #2ecc71; border: 10px solid #27ae60; }
        .danger { background-color: #e74c3c; border: 10px solid #c0392b; animation: pulse 1s infinite; }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }
        
        table { width: 100%; border-collapse: collapse; background: white; margin-top: 10px; border-radius: 8px; overflow: hidden; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; color: #333; }
        .table-section { margin-bottom: 3rem; }
    </style>
</head>
<body>

<?php if (!isset($_SESSION['user_id'])): ?>
    <div class="login-container">
        <div class="login-card">
            <h2>Gas-Simhot Login</h2>
            <?php if ($error_message): ?>
                <p style="color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px;"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <form method="POST" action="">
                <input type="text" name="username" placeholder="Username" required>
                <button type="submit" name="login_submit">Login to Dashboard</button>
            </form>
            <p style="font-size: 0.8rem; color: #666; margin-top: 1rem;">Default user is 'admin' or anyone in your users table.</p>
        </div>
    </div>

<?php else: ?>
    <nav>
        <h1>Gas-Simhot System</h1>
        <ul>
            <li><a href="index.php">Dashboard</a></li>
            <li>Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong></li>
            <li><a href="?logout=1" class="logout-link">Logout</a></li>
        </ul>
    </nav>

    <main class="container">
        <section class="monitor-card">
            <h2>LPG Concentration Level</h2>
            <div id="status-indicator" class="safe">
                <span id="gas-value">0</span> PPM
            </div>
            <p id="alert-text">Status: System Normal</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button onclick="simulateLeak()" style="padding: 10px 20px; background: #e67e22; color: white; border: none; border-radius: 5px; cursor: pointer;">Simulate Gas Leak</button>
                <button onclick="resetSystem()" style="padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer;">Reset System</button>
            </div>
        </section>

        <div class="table-grid">
            <section class="table-section">
                <h3><i class="icon"></i> Recent Activity Logs</h3>
                <table>
                    <thead><tr><th>ID</th><th>Action</th><th>Timestamp</th></tr></thead>
                    <tbody>
                        <?php foreach($logs as $log): ?>
                        <tr><td>#<?php echo $log['id']; ?></td><td><?php echo htmlspecialchars($log['action']); ?></td><td><?php echo $log['created_at']; ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>

            <section class="table-section">
                <h3>Registered Users</h3>
                <table>
                    <thead><tr><th>Username</th><th>Last Update</th></tr></thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr><td><?php echo htmlspecialchars($user['username']); ?></td><td><?php echo $user['updated_at']; ?></td></tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
    <script src="script.php"></script>
<?php endif; ?>

</body>
</html>