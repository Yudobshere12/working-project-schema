<?php
session_start();
$conn = new mysqli("localhost", "root", "", "working_project_schema");

// Check if user is logged in and an action is provided
if (isset($_SESSION['user_id']) && isset($_POST['action'])) {
    $uid = $_SESSION['user_id'];
    $name = $_SESSION['full_name'];
    $act = $conn->real_escape_string($_POST['action']);
    
    // 1. Log the activity for history
    if ($act !== "admin_ack") {
        $conn->query("INSERT INTO user_activity_logs (user_id, action, created_at) VALUES ('$uid', '$act', NOW())");
    }

    // 2. Handle Leak Detection (Updates the Real-Time Alert System)
    if ($act == "Leak Detected") {
        // Fetch the user's assigned location from the database
        $user_info = $conn->query("SELECT location FROM users WHERE id = '$uid'")->fetch_assoc();
        $loc_name = $user_info['location'] ?? 'General Area';
        
        // --- PRE-DEFINED ACCURATE COORDINATES ---
        // These will be used by the Admin Map
        $lat = 8.3697; // Default (Manolo Fortich)
        $lng = 124.8644;

        // Map specific locations to exact coordinates for the demo
        if (trim($loc_name) == "Kitchen") { $lat = 8.3701; $lng = 124.8650; }
        elseif (trim($loc_name) == "Laboratory") { $lat = 8.3685; $lng = 124.8630; }
        elseif (trim($loc_name) == "Main Office") { $lat = 8.3690; $lng = 124.8640; }
        
        // Update system_status with ACTIVE leak and defined coordinates
        $query = "UPDATE system_status SET 
                    is_active = 1, 
                    triggered_by = '$name', 
                    location = '$loc_name', 
                    lat = '$lat', 
                    lng = '$lng', 
                    acknowledged_by_admin = 0, 
                    ack_time = NULL 
                  WHERE id = 1";
        $conn->query($query);
    } 
    
    // 3. Handle System Reset
    elseif ($act == "System Reset") {
        $conn->query("UPDATE system_status SET is_active = 0, acknowledged_by_admin = 0, ack_time = NULL WHERE id = 1");
    }
    
    // 4. Handle Admin Acknowledgment
    elseif ($act == "admin_ack") {
        $conn->query("UPDATE system_status SET acknowledged_by_admin = 1, ack_time = NOW() WHERE id = 1");
    }

    echo json_encode(["status" => "success"]);
}
?>