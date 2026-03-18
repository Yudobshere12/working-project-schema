<?php
$conn = new mysqli("localhost", "root", "", "working_project_schema");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']); // Note: In a real app, use password_hash()
    $role = $_POST['role'];

    $sql = "INSERT INTO users (username, password, full_name, role) 
            VALUES ('$username', '$password', '$fullname', '$role')";

    if ($conn->query($sql) === TRUE) {
        header("Location: index.php?msg=Registration Success! Please Login.");
        exit();
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - GAS-SIMHOT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #2ec4b6; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reg-card { background: white; padding: 30px; border-radius: 15px; width: 100%; max-width: 400px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    </style>
</head>
<body>

<div class="reg-card">
    <h3 class="text-center mb-4">Create Account</h3>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-2">
            <label class="form-label">Full Name</label>
            <input type="text" name="fullname" class="form-control" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-2">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Assign Role</label>
            <select name="role" class="form-select">
                <option value="staff">User</option>
                <option value="admin">Administrator</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success w-100">Register Account</button>
    </form>
    <div class="text-center mt-3">
        <a href="index.php" class="text-decoration-none text-muted small">Already have an account? Login here</a>
    </div>
</div>

</body>
</html>