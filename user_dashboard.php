<?php
session_start();
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'staff') { header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .circle { width: 160px; height: 160px; border-radius: 50%; background: green; color: white; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 20px auto; transition: 0.4s; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .blink { animation: blinker 1s linear infinite; }
        @keyframes blinker { 50% { opacity: 0.2; } }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-primary px-4 shadow">
        <span class="navbar-brand">User: <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </nav>

    <div class="container text-center mt-5">
        <div id="leak-alert" class="alert alert-danger d-none blink shadow-lg py-4">
            <h2 class="alert-heading fw-bold">⚠️ GAS LEAK DETECTED!</h2>
            <p class="fs-5 mb-0">Evacuate area immediately and check valves.</p>
        </div>

        <div id="admin-feedback" class="alert alert-info d-none shadow-sm mt-2 border-info">
            <div class="d-flex align-items-center justify-content-center">
                <div class="spinner-border spinner-border-sm me-2 text-info"></div>
                <strong>✅ Admin Notified:</strong> Help is dispatched. <span id="ack-time" class="ms-2 badge bg-info"></span>
            </div>
        </div>

        <div class="card p-5 mx-auto shadow mt-3 border-0" style="max-width: 450px;">
            <h4 class="text-muted">Current LPG Level</h4>
            <div id="ppm" class="circle">0 PPM</div>
            <div class="d-grid gap-2 mt-4">
                <button class="btn btn-danger btn-lg shadow" onclick="sim()">Simulate Leak</button>
                <button class="btn btn-secondary shadow-sm" onclick="res()">System Reset</button>
            </div>
        </div>
    </div>

    <script>
        let audioCtx = null;
        let pulseInterval = null;

        function startBuzzer() {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            pulseInterval = setInterval(() => {
                let osc = audioCtx.createOscillator();
                let gain = audioCtx.createGain();
                osc.type = 'square';
                osc.frequency.setValueAtTime(880, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.5);
                osc.connect(gain); gain.connect(audioCtx.destination);
                osc.start(); osc.stop(audioCtx.currentTime + 0.5);
            }, 600);
        }

        function stopBuzzer() {
            if (pulseInterval) { clearInterval(pulseInterval); pulseInterval = null; }
            if (audioCtx) { audioCtx.close().then(() => { audioCtx = null; }); }
        }

        function sim() { 
            document.getElementById('leak-alert').classList.remove('d-none');
            document.getElementById('ppm').style.background = "red"; 
            document.getElementById('ppm').innerText = "450 PPM";
            startBuzzer();
            log("Leak Detected"); 
        }

        function res() { 
            document.getElementById('leak-alert').classList.add('d-none');
            document.getElementById('admin-feedback').classList.add('d-none');
            document.getElementById('ppm').style.background = "green"; 
            document.getElementById('ppm').innerText = "0 PPM";
            stopBuzzer();
            log("System Reset"); 
        }

        function log(a) { let fd = new FormData(); fd.append('action', a); fetch('log_action.php', { method: 'POST', body: fd }); }

        function checkFeedback() {
            fetch('check_alert.php').then(r => r.json()).then(data => {
                let box = document.getElementById('admin-feedback');
                if (data.is_active == 1 && data.acknowledged_by_admin == 1) {
                    box.classList.remove('d-none');
                    document.getElementById('ack-time').innerText = "Time: " + data.ack_time;
                } else { box.classList.add('d-none'); }
            });
        }
        setInterval(checkFeedback, 3000);
    </script>
</body>
</html>