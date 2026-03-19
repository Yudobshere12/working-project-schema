<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "working_project_schema");

// Check connection
if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed"]);
    exit();
}

// We pull all columns from system_status because log_action.php 
// now fills in 'lat', 'lng', and 'location' automatically.
$query = "SELECT * FROM system_status WHERE id = 1";

$res = $conn->query($query);

if ($res && $row = $res->fetch_assoc()) {
    // Ensure numbers are sent as floats for the Leaflet Map
    if (isset($row['lat'])) $row['lat'] = (float)$row['lat'];
    if (isset($row['lng'])) $row['lng'] = (float)$row['lng'];
    
    echo json_encode($row);
} else {
    echo json_encode(["is_active" => 0]);
}
?>