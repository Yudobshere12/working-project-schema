<?php
session_start();
$conn = new mysqli("localhost", "root", "", "working_project_schema");

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $conn->query("DELETE FROM user_activity_logs");
    header("Location: admin_dashboard.php?msg=Logs Cleared");
}
?>