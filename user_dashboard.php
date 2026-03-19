<?php
session_start();
$conn = new mysqli("localhost", "root", "", "working_project_schema");

// Security Check: Ensure only logged-in staff can access
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'staff') { 
    header("Location: index.php"); 
    exit(); 
}
$uid = $_SESSION['user_id'];

// Fetch the user's pre-assigned location from the 'users' table
$user_data = $conn->query("SELECT location FROM users WHERE id = '$uid'")->fetch_assoc();
$assigned_location = $user_data['location'] ?? 'General Area';
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Terminal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .circle { width: 140px; height: 140px; border-radius: 50%; background: green; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 20px auto; transition: 0.4s; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .blink { animation: blinker 1s linear infinite; }
        @keyframes blinker { 50% { opacity: 0.2; } }
        .progress { height: 20px; border-radius: 10px; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-4 shadow d-flex justify-content-between">
        <span class="navbar-brand fw-bold">GAS-SIMHOT User Terminal</span>
        <div class="text-white small">
            User: <strong><?php echo $_SESSION['full_name']; ?></strong> | 
            Monitoring: <span class="badge bg-warning text-dark"><?php echo $assigned_location; ?></span>
        </div>
        <a href="logout.php" class="btn btn-outline-light btn-sm px-3">Logout</a>
    </nav>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-7 text-center">
                <div id="leak-alert" class="alert alert-danger d-none blink shadow"><h4>⚠️ LEAK DETECTED! ADMIN NOTIFIED</h4></div>
                <div id="admin-feedback" class="alert alert-info d-none shadow-sm"><strong>✅ Admin Acknowledged</strong> <span id="ack-time" class="badge bg-info ms-2"></span></div>
                
                <div class="card p-4 shadow border-0 mb-4">
                    <h6 class="text-muted">Gas Concentration Level</h6>
                    <div class="progress mb-3"><div id="ppm-bar" class="progress-bar bg-success" style="width: 0%;"></div></div>
                    <div id="ppm" class="circle">0 PPM</div>
                    
                    <div class="d-grid gap-2">
                        <button class="btn btn-danger btn-lg shadow" onclick="triggerLeak()">Simulate Leak</button>
                        <button class="btn btn-secondary" onclick="res()">System Reset</button>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card shadow border-0 p-3 h-100">
                    <h6 class="fw-bold mb-3">Your Recent Actions</h6>
                    <table class="table table-sm small">
                        <tbody>
                            <?php
                            $logs = $conn->query("SELECT action, created_at FROM user_activity_logs WHERE user_id = '$uid' ORDER BY created_at DESC LIMIT 10");
                            if ($logs) {
                                while($l = $logs->fetch_assoc()) {
                                    echo "<tr><td>{$l['action']}</td><td class='text-muted small'>{$l['created_at']}</td></tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        let audioCtx = null, pulse = null;
        const autoLocation = "<?php echo $assigned_location; ?>";

        function updateUI(p) {
            document.getElementById('ppm').innerText = p + " PPM";
            document.getElementById('ppm-bar').style.width = (p/1000*100) + "%";
            
            // UI only turns RED here, but reset is handled by the setInterval check below
            if (p >= 400) {
                document.getElementById('ppm').style.background = "red";
                document.getElementById('leak-alert').classList.remove('d-none');
                if(!pulse) startBuzzer();
            }
        }

        function triggerLeak() {
            updateUI(450);
            logAction("Leak Detected");
        }

        function res() { 
            // We log the reset, but we let the Admin Acknowledgment clear the UI
            logAction("System Reset"); 
        }

        function logAction(act) { 
            let fd = new FormData(); 
            fd.append('action', act); 
            fd.append('location', autoLocation);

            fetch('log_action.php', { method: 'POST', body: fd });
            // Removed location.reload() to prevent flickering during the demo
        }

        // --- UPDATED MONITORING LOGIC ---
        setInterval(() => {
            fetch('check_alert.php')
                .then(r => r.json())
                .then(data => {
                    let alertBox = document.getElementById('leak-alert');
                    let feedbackBox = document.getElementById('admin-feedback');
                    let ppmCircle = document.getElementById('ppm');
                    let ppmBar = document.getElementById('ppm-bar');

                    // If a leak is active but NOT YET acknowledged
                    if (data.is_active == 1 && data.acknowledged_by_admin == 0) {
                        alertBox.classList.remove('d-none');
                        feedbackBox.classList.add('d-none');
                        ppmCircle.style.background = "red";
                        ppmCircle.innerText = "450 PPM";
                        ppmBar.style.width = "45%";
                        if(!pulse) startBuzzer();
                    } 
                    // If Admin has Acknowledged OR leak is cleared
                    else if (data.acknowledged_by_admin == 1 || data.is_active == 0) {
                        alertBox.classList.add('d-none');
                        stopBuzzer();
                        
                        // Show the green "Acknowledged" box if it was a leak
                        if(data.acknowledged_by_admin == 1) {
                            feedbackBox.classList.remove('d-none');
                            document.getElementById('ack-time').innerText = data.ack_time;
                        } else {
                            feedbackBox.classList.add('d-none');
                        }

                        // Reset visual gauges to green/zero
                        ppmCircle.style.background = "green";
                        if(data.is_active == 0) {
                            ppmCircle.innerText = "0 PPM";
                            ppmBar.style.width = "0%";
                        }
                    }
                }).catch(e => console.log("System Status: Normal"));
        }, 2000);

        function startBuzzer() {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            pulse = setInterval(() => {
                let o = audioCtx.createOscillator(); let g = audioCtx.createGain();
                o.type = 'square'; o.frequency.value = 880; 
                g.gain.setValueAtTime(0.1, audioCtx.currentTime);
                g.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.5);
                o.connect(g); g.connect(audioCtx.destination); 
                o.start(); o.stop(audioCtx.currentTime + 0.5);
            }, 600);
        }
        function stopBuzzer() { clearInterval(pulse); pulse = null; }
    </script>
</body>
</html>