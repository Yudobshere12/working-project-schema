<?php
session_start();
$conn = new mysqli("localhost", "root", "", "working_project_schema");

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$role = $_SESSION['role'];
$full_name = $_SESSION['full_name'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Gas-Simhot System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .navbar { background-color: #0a2540; color: white; }
        .status-card { 
            background: white; padding: 40px; border-radius: 15px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); max-width: 500px; margin: 40px auto; 
        }
        .gas-circle {
            width: 180px; height: 180px; border-radius: 50%;
            background-color: #28a745; color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; font-weight: bold; margin: 20px auto;
        }
        .admin-section { margin-top: 40px; background: white; padding: 20px; border-radius: 10px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg px-4">
    <div class="container-fluid">
        <span class="navbar-brand text-white">Gas-Simhot System</span>
        <div class="ms-auto text-white">
            <span>Welcome, <strong><?php echo $full_name; ?></strong> (<?php echo ucfirst($role); ?>)</span>
            <a href="logout.php" class="btn btn-outline-light btn-sm ms-3">Logout</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="status-card text-center">
        <h3>LPG Concentration Level</h3>
        <div class="gas-circle" id="ppm-display">0 PPM</div>
        <p class="text-muted">Status: <span id="status-text">System Normal</span></p>
        
        <div class="d-flex justify-content-center gap-2">
            <button class="btn btn-light border" onclick="simulateLeak()">Simulate Gas Leak</button>
            <button class="btn btn-light border" onclick="resetSystem()">Reset System</button>
        </div>
    </div>

    <?php if ($role === 'admin'): ?>
    <div class="admin-section shadow-sm">
        <h4 class="mb-4">User Activity Logs (Admin Only)</h4>
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Log ID</th>
                    <th>User ID</th>
                    <th>Action Performed</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
<tbody>
    <?php
    // We JOIN the users table so we can see the NAME of the person who did the action
    $query = "SELECT l.id, u.full_name, l.action, l.created_at 
              FROM user_activity_logs l
              JOIN users u ON l.user_id = u.id 
              ORDER BY l.created_at DESC LIMIT 10";
              
    $logs = $conn->query($query);

    if ($logs && $logs->num_rows > 0) {
        while($row = $logs->fetch_assoc()) {
            echo "<tr>
                    <td>#{$row['id']}</td>
                    <td><strong>" . htmlspecialchars($row['full_name']) . "</strong></td>
                    <td>" . htmlspecialchars($row['action']) . "</td>
                    <td>" . date('M d, Y H:i A', strtotime($row['created_at'])) . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='4' class='text-center text-muted'>No activity logs found. Click 'Simulate' to generate data!</td></tr>";
    }
    ?>
</tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<script>
    function simulateLeak() {
    // 1. Visual change
    document.getElementById('ppm-display').innerText = "450 PPM";
    document.getElementById('ppm-display').style.backgroundColor = "#dc3545";
    document.getElementById('status-text').innerText = "Leak Detected!";
    document.getElementById('status-text').style.color = "red";

    // 2. Send to Database
    saveLog("Simulated a Gas Leak");
}

function resetSystem() {
    // 1. Visual change
    document.getElementById('ppm-display').innerText = "0 PPM";
    document.getElementById('ppm-display').style.backgroundColor = "#28a745";
    document.getElementById('status-text').innerText = "System Normal";
    document.getElementById('status-text').style.color = "black";

    // 2. Send to Database
    saveLog("Reset the System");
}

// The "Log Machine"
function saveLog(actionName) {
    let formData = new FormData();
    formData.append('action', actionName);

    fetch('log_action.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => console.log("Activity logged: " + actionName));
}
</script>

</body>
</html>