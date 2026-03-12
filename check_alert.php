<?php
$conn = new mysqli("localhost", "root", "", "working_project_schema");
$res = $conn->query("SELECT * FROM system_status WHERE id = 1");
echo json_encode($res->fetch_assoc());
?>