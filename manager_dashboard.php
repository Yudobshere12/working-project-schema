<?php
session_start();
$conn = new mysqli("localhost", "root", "", "working_project_schema");

// Security Check: Ensure only Manager can access
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'manager') { 
    header("Location: index.php"); 
    exit(); 
}

// Handle User/Admin Deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM users WHERE id = '$id'");
    header("Location: manager_dashboard.php?msg=deleted");
}

// Handle Adding User/Admin
if (isset($_POST['add_user'])) {
    $fn = $_POST['full_name'];
    $un = $_POST['username'];
    $pw = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $ro = $_POST['role'];
    $lo = $_POST['location'];
    $conn->query("INSERT INTO users (full_name, username, password, role, location) VALUES ('$fn', '$un', '$pw', '$ro', '$lo')");
    header("Location: manager_dashboard.php?msg=added");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manager Command Center</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .stat-card { border-radius: 15px; border: none; transition: 0.3s; }
        .stat-card:hover { transform: translateY(-5px); }
        .role-badge { font-size: 0.75rem; padding: 4px 8px; border-radius: 10px; }
        .online-dot { width: 10px; height: 10px; background: #28a745; border-radius: 50%; display: inline-block; margin-right: 5px; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark px-4 shadow">
        <span class="navbar-brand fw-bold">🚀 GAS-SIMHOT | Manager Control</span>
        <div class="d-flex align-items-center text-white">
            <span class="me-3 small">Role: <span class="badge bg-warning text-dark">Super User</span></span>
            <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <?php
            $total_users = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
            $active_leaks = $conn->query("SELECT is_active FROM system_status WHERE id=1")->fetch_assoc()['is_active'];
            ?>
            <div class="col-md-4">
                <div class="card stat-card bg-primary text-white p-3 shadow">
                    <h6>Total Registered Personnel</h6>
                    <h2><?php echo $total_users; ?></h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card <?php echo $active_leaks ? 'bg-danger' : 'bg-success'; ?> text-white p-3 shadow">
                    <h6>System Safety Status</h6>
                    <h2><?php echo $active_leaks ? '⚠️ EMERGENCY' : '✅ SECURED'; ?></h2>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <button class="btn btn-dark shadow-sm py-3 px-4" data-bs-toggle="modal" data-bs-target="#addUserModal">➕ Register New Personnel</button>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card p-4 shadow-sm border-0">
                    <h5 class="fw-bold mb-3">Manage Admins & User</h5>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Status</th>
                                    <th>Full Name</th>
                                    <th>Role</th>
                                    <th>Location Assignment</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $users = $conn->query("SELECT * FROM users ORDER BY role DESC");
                                while($u = $users->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><span class="online-dot"></span><small>Active</small></td>
                                    <td>
                                        <div class="fw-bold"><?php echo $u['full_name']; ?></div>
                                        <small class="text-muted">@<?php echo $u['username']; ?></small>
                                    </td>
                                    <td>
                                        <span class="role-badge <?php echo $u['role']=='admin' ? 'bg-info text-dark' : 'bg-secondary text-white'; ?>">
                                            <?php echo strtoupper($u['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $u['location']; ?></td>
                                    <td>
                                        <?php if($u['role'] !== 'manager'): ?>
                                            <a href="?delete=<?php echo $u['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this user?')">Remove</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card p-3 shadow-sm border-0">
                    <h6 class="fw-bold border-bottom pb-2">Recent System Audit</h6>
                    <div style="max-height: 450px; overflow-y: auto;">
                        <?php
                        $audit = $conn->query("SELECT l.*, u.full_name FROM user_activity_logs l JOIN users u ON l.user_id = u.id ORDER BY created_at DESC LIMIT 20");
                        while($a = $audit->fetch_assoc()):
                        ?>
                        <div class="mb-3 border-bottom pb-1">
                            <div class="small fw-bold text-primary"><?php echo $a['full_name']; ?></div>
                            <div class="small"><?php echo $a['action']; ?></div>
                            <div class="text-muted" style="font-size: 0.7rem;"><?php echo $a['created_at']; ?></div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Personnel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label>Full Name</label><input type="text" name="full_name" class="form-control" required></div>
                    <div class="mb-3"><label>Username</label><input type="text" name="username" class="form-control" required></div>
                    <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                    <div class="mb-3">
                        <label>Role</label>
                        <select name="role" class="form-select">
                            <option value="staff">Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Location Assignment</label>
                        <select name="location" class="form-select">
                            <option value="Kitchen">Kitchen</option>
                            <option value="Laboratory">Laboratory</option>
                            <option value="Warehouse">Warehouse</option>
                            <option value="Main Office">Main Office</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_user" class="btn btn-primary w-100">Save Personnel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>