<?php
$conn = new mysqli("localhost", "root", "", "working_project_schema");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']); 
    $role = $_POST['role'];
    $location = $conn->real_escape_string($_POST['location']); // NEW: Capture location

    // Added 'location' to the INSERT statement
    $sql = "INSERT INTO users (username, password, full_name, role, location) 
            VALUES ('$username', '$password', '$fullname', '$role', '$location')";

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
        body { background: #2ec4b6; height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .reg-card { background: white; padding: 35px; border-radius: 15px; width: 100%; max-width: 450px; box-shadow: 0 15px 30px rgba(0,0,0,0.2); }
        .btn-success { background-color: #0a2540; border: none; padding: 10px; font-weight: bold; }
        .btn-success:hover { background-color: #1a4a7a; }
    </style>
</head>
<body>

<div class="reg-card">
    <h3 class="text-center fw-bold text-dark mb-4">Create Account</h3>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger py-2 small"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="row">
            <div class="col-md-6 mb-2">
                <label class="form-label small fw-bold">Full Name</label>
                <input type="text" name="fullname" class="form-control form-control-sm" placeholder="Juan Dela Cruz" required>
            </div>
            <div class="col-md-6 mb-2">
                <label class="form-label small fw-bold">Username</label>
                <input type="text" name="username" class="form-control form-control-sm" placeholder="username123" required>
            </div>
        </div>

        <div class="mb-2">
            <label class="form-label small fw-bold">Password</label>
            <input type="password" name="password" class="form-control form-control-sm" placeholder="••••••••" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-bold">Assign Role</label>
                <select name="role" class="form-select form-select-sm" required>
                    <option value="staff">Staff / User</option>
                    <option value="admin">Administrator</option>
                    <option value="manager">Manager</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label small fw-bold">Assign Station</label>
                <select name="location" class="form-select form-select-sm" required>
                    <option value="Kitchen">Kitchen</option>
                    <option value="Laboratory">Laboratory</option>
                    <option value="Warehouse">Warehouse</option>
                    <option value="Main Office">Main Office</option>
                </select>
            </div>
        </div>

        <button type="submit" class="btn btn-success w-100 mt-2 shadow-sm">Register Account</button>
    </form>

    <div class="text-center mt-3">
        <a href="index.php" class="text-decoration-none text-muted small">Already have an account? Login here</a>
    </div>
</div>

</body>
</html>