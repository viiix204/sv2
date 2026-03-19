<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Hệ thống giám sát AGV & Lidar - IUH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">

    <style>
        /* ==========================================================================
           CẤU HÌNH DARK THEME
           ========================================================================== */
        :root {
            --bg-body: #0f172a;        /* Nền chính (Slate 900) */
            --bg-card: #1e293b;        /* Nền Card (Slate 800) */
            --text-main: #f8fafc;
            --text-sub: #94a3b8;
            --primary: #3b82f6;        /* Xanh dương Neon */
            --success: #10b981;        /* Xanh lá Neon */
            --border-light: rgba(255, 255, 255, 0.08);
            --glow-primary: 0 0 20px rgba(59, 130, 246, 0.5);
            --glow-success: 0 0 15px rgba(16, 185, 129, 0.5);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            /* Hiệu ứng nền Ambient Light */
            background-image: 
                radial-gradient(circle at 15% 50%, rgba(59, 130, 246, 0.08) 0%, transparent 25%),
                radial-gradient(circle at 85% 30%, rgba(16, 185, 129, 0.05) 0%, transparent 25%);
            background-attachment: fixed;
            overflow-x: hidden;
        }

        /* --- NAVBAR --- */
        .navbar {
            padding: 15px 0;
            background-color: rgba(15, 23, 42, 0.8); /* Nền tối mờ */
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-light);
        }
        .navbar-brand img { filter: drop-shadow(0 0 5px rgba(255,255,255,0.5)); }
        .nav-link { font-weight: 500; color: var(--text-sub) !important; margin-left: 15px; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { color: var(--primary) !important; text-shadow: 0 0 10px rgba(59,130,246,0.5); }
        
        .btn-contact { 
            background: transparent; color: var(--text-main); 
            border: 1px solid var(--primary);
            border-radius: 50px; padding: 6px 20px; font-weight: 600; font-size: 0.9rem;
            transition: 0.3s;
        }
        .btn-contact:hover { background: var(--primary); box-shadow: var(--glow-primary); }

        /* --- HERO SECTION --- */
        .hero-section {
            padding-top: 100px; padding-bottom: 60px;
            min-height: auto; display: flex; align-items: center;
        }

        .tag-pill {
            background: rgba(59, 130, 246, 0.1); color: var(--primary);
            padding: 6px 15px; border-radius: 50px; font-size: 0.8rem;
            font-weight: 700; display: inline-flex; align-items: center;
            margin-bottom: 25px; border: 1px solid rgba(59, 130, 246, 0.3);
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.1);
        }

        .hero-title {
            font-size: 2.8rem; font-weight: 800; line-height: 1.1; color: var(--text-main);
            margin-bottom: 20px; letter-spacing: -1px;
        }
        .hero-desc { 
            color: var(--text-sub); font-size: 1.1rem; line-height: 1.6; 
            margin-bottom: 35px; max-width: 90%;
        }
        
        .btn-hero-primary {
            background: linear-gradient(135deg, var(--primary), #2563eb); 
            color: white; border: none;
            padding: 12px 35px; border-radius: 50px; font-weight: 600; 
            box-shadow: var(--glow-primary); width: 100%; display: block; margin-bottom: 15px;
            transition: transform 0.2s;
        }
        .btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4); }

        .btn-hero-secondary {
            background: rgba(255,255,255,0.05); color: var(--text-main); 
            border: 1px solid var(--border-light);
            padding: 12px 35px; border-radius: 50px; font-weight: 600; 
            width: 100%; display: block; margin-left: 0;
            transition: 0.3s;
        }
        .btn-hero-secondary:hover { background: rgba(255,255,255,0.1); border-color: var(--text-sub); }

        /* --- MONITOR CARD (Radar Style) --- */
        .monitor-card {
            background: rgba(30, 41, 59, 0.6); /* Glassmorphism tối */
            backdrop-filter: blur(10px);
            border-radius: 24px; padding: 25px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            position: relative; overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
            margin-top: 40px;
        }
        /* Hiệu ứng viền sáng */
        .monitor-card::before {
            content: ''; position: absolute; inset: 0; border-radius: 24px; padding: 1px;
            background: linear-gradient(180deg, rgba(255,255,255,0.1), transparent); 
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor; mask-composite: exclude; pointer-events: none;
        }
        
        .live-badge {
            background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); 
            padding: 4px 12px; border-radius: 30px; font-size: 0.7rem; color: var(--success);
            font-weight: 700; letter-spacing: 0.5px;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.1);
        }
        
        .value-display { 
            font-family: 'JetBrains Mono', monospace;
            font-size: 3.5rem; font-weight: 700; line-height: 1; margin-top: 15px; letter-spacing: -2px; 
            color: var(--text-main);
            text-shadow: 0 0 20px rgba(255,255,255,0.1);
        }
        .unit { font-size: 0.9rem; color: var(--text-sub); margin-bottom: 20px; text-transform: uppercase; letter-spacing: 1px; }
        
        .badge-feature {
            background: rgba(255,255,255,0.05); border: 1px solid var(--border-light); 
            color: var(--text-sub); padding: 5px 10px; border-radius: 6px; 
            font-size: 0.65rem; font-weight: 600; font-family: 'JetBrains Mono', monospace;
        }

        /* Lidar Map (Radar) */
        .lidar-map {
            height: 160px; background: #000; border-radius: 12px; position: relative; overflow: hidden;
            border: 1px solid #334155; margin-top: 20px;
            box-shadow: inset 0 0 20px rgba(0,0,0,0.8);
        }
        /* Lưới Radar */
        .lidar-map::after {
            content: ''; position: absolute; top:0; left:0; width:100%; height:100%;
            background-image: radial-gradient(rgba(16, 185, 129, 0.2) 1px, transparent 1px);
            background-size: 20px 20px; opacity: 0.3; pointer-events: none;
        }

        .lidar-dot {
            position: absolute; width: 4px; height: 4px; background: #00ff9d; border-radius: 50%;
            box-shadow: 0 0 8px #00ff9d; /* Neon Glow */
            animation: blink 1.5s infinite alternate;
        }
        @keyframes blink { from { opacity: 0.3; transform: scale(0.8); } to { opacity: 1; transform: scale(1.5); } }

        .mini-chart-box {
            background: rgba(0,0,0,0.2); border-radius: 12px; padding: 15px 5px 5px 5px;
            margin-top: 20px; border: 1px solid var(--border-light);
        }

        .footer-info { font-size: 0.8rem; color: var(--text-sub); margin-top: 60px; border-top: 1px solid var(--border-light); padding-top: 30px; text-align: center;}

        /* --- DESKTOP OVERRIDE --- */
        @media (min-width: 992px) {
            .hero-section { min-height: 90vh; padding-top: 80px; }
            .hero-title { font-size: 4rem; }
            .btn-hero-primary, .btn-hero-secondary { width: auto; display: inline-block; }
            .btn-hero-secondary { margin-left: 15px; margin-bottom: 0; }
            .monitor-card { padding: 40px; margin-top: 0; transform: perspective(1000px) rotateY(-5deg); transition: transform 0.5s; }
            .monitor-card:hover { transform: perspective(1000px) rotateY(0deg); }
            .value-display { font-size: 5rem; }
            .lidar-map { margin-top: 0; height: 180px; }
            .footer-info { text-align: left; }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="https://iuh.edu.vn/assets/images/icons/logo.svg?v=51" alt="Logo IUH" style="height: 40px; margin-right: 12px; background: rgba(255,255,255,0.9); padding: 2px; border-radius: 4px;">
                <div style="line-height: 1.2;">
                    <div style="font-weight: 800; font-size: 0.9rem; color: var(--text-main); text-transform: uppercase; letter-spacing: 0.5px;">ĐH Công Nghiệp TP.HCM</div>
                    <div style="font-size: 0.7rem; color: var(--text-sub);">INDUSTRIAL UNIVERSITY OF HCMC</div>
                </div>
            </a>
            <a href="#" class="btn btn-contact btn-sm ms-auto d-lg-none">Liên hệ</a> 
            <button class="navbar-toggler ms-2 border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center text-center mt-3 mt-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="#">Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="manual_control.php">Điều khiển</a></li>
                    <li class="nav-item ms-3 d-none d-lg-block"><a href="#" class="btn btn-contact">Liên hệ</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container hero-section">
        <div class="row align-items-center w-100 gx-5 mx-0">
            
            <div class="col-lg-6 px-0 px-lg-3">
                <div class="tag-pill">
                    <span class="spinner-grow spinner-grow-sm me-2" role="status" aria-hidden="true"></span>
                    IoT System • Realtime Lidar
                </div>
                
                <h1 class="hero-title">
                    Hệ thống giám sát<br>
                    <span style="background: linear-gradient(to right, #3b82f6, #60a5fa); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">AGV THÔNG MINH</span>
                </h1>
                
                <p class="hero-desc">
                    Nền tảng tích hợp toàn diện: Theo dõi dữ liệu cảm biến, bản đồ SLAM Lidar và trạng thái hoạt động của Robot tự hành với độ trễ cực thấp (< 50ms).
                </p>
                
                <div class="d-flex flex-wrap gap-2 mb-5">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-3 py-2">⚡ WebSocket</span>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-2">📡 Stable Conn</span>
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3 py-2">🧊 3D Model</span>
                </div>

                <div>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <a href="dashboard.php" class="btn-hero-primary text-decoration-none text-center">
                                <i class="fa-solid fa-gauge-high me-2"></i> TRUY CẬP HỆ THỐNG
                            </a>
                        </div>
                        <div class="col-12 col-sm-6">
                            <button class="btn-hero-secondary">
                                <i class="fa-solid fa-book-open me-2"></i> Tài liệu kỹ thuật
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 ps-lg-5 px-0">
                <div class="monitor-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-0 text-white" style="font-size: 1.1rem; letter-spacing: 0.5px;">LIVE TELEMETRY</h5>
                            <small style="color: var(--text-sub); font-size: 0.75rem;">AGV-01 SENSOR DATA</small>
                        </div>
                        <div class="live-badge">
                            <span class="me-1" style="animation: blink 1s infinite; text-shadow: 0 0 5px #10b981;">●</span> LIVE
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6 border-bottom border-md-end border-secondary border-opacity-25 pb-3 pb-md-0 mb-3 mb-md-0">
                            <div class="value-display" id="liveVal">00.00</div>
                            <div class="unit">Góc quay (Degree)</div>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge-feature">IMU: MPU6050</span>
                                <span class="badge-feature">FILTER: KALMAN</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="text-sub small mb-2 d-flex justify-content-between font-monospace">
                                <span><i class="fa-solid fa-radar me-1 text-success"></i>LIDAR SCAN</span>
                                <span style="color: var(--success);">R2000</span>
                            </div>
                            <div class="lidar-map" id="lidarBox"></div>
                        </div>
                    </div>

                    <div class="mini-chart-box">
                        <canvas id="miniChart" height="60"></canvas>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="container pb-4">
        <div class="footer-info">
            <div class="d-flex justify-content-center justify-content-lg-start align-items-center gap-2">
                <img src="https://iuh.edu.vn/assets/images/icons/logo.svg?v=51" alt="Logo" style="height: 30px; background: rgba(255,255,255,0.9); padding: 2px; border-radius: 4px;">
                <span>Khoa Công Nghệ Điện - Trường ĐH Công Nghiệp TP.HCM (IUH)</span>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // 1. Hiệu ứng Lidar (Radar Scan)
        const lidarBox = document.getElementById('lidarBox');
        function createLidarDots() {
            lidarBox.innerHTML = ''; 
            // Tia quét Radar
            let scanner = document.createElement('div');
            scanner.style.cssText = "position:absolute; top:50%; left:50%; width:100%; height:2px; background:linear-gradient(90deg, transparent 50%, rgba(0,255,157,0.8) 100%); transform-origin:0 0; animation: scan 2s linear infinite; box-shadow: 0 0 10px rgba(0,255,157,0.5);";
            lidarBox.appendChild(scanner);

            // Điểm chướng ngại vật
            for(let i=0; i<15; i++){
                let dot = document.createElement('div');
                dot.className = 'lidar-dot';
                let angle = Math.random() * Math.PI * 2;
                let radius = Math.random() * 40 + 10; 
                dot.style.left = (50 + radius * Math.cos(angle)) + '%';
                dot.style.top = (50 + radius * Math.sin(angle) * 0.6) + '%';
                dot.style.animationDelay = (Math.random() * 2) + 's';
                lidarBox.appendChild(dot);
            }
            
            // Robot ở giữa
            let robot = document.createElement('div');
            robot.style.cssText = "position:absolute; top:50%; left:50%; width:8px; height:8px; background:#ef4444; border-radius:50%; transform:translate(-50%, -50%); box-shadow: 0 0 10px #ef4444; border: 1px solid #fff;";
            lidarBox.appendChild(robot);
        }
        createLidarDots();

        // 2. Chart Mini (Dark Theme Config)
        const ctx = document.getElementById('miniChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 80);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)'); // Xanh dương mờ
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

        const miniChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Array(20).fill(''),
                datasets: [{
                    data: Array(20).fill(0),
                    borderColor: '#60a5fa', // Line sáng hơn
                    backgroundColor: gradient,
                    borderWidth: 2,
                    tension: 0.4,
                    pointRadius: 0,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: false, tooltip: false },
                scales: { x: { display: false }, y: { display: false, min: -10, max: 10 } },
                animation: false
            }
        });

        // 3. Giả lập dữ liệu
        function fetchData() {
            let randomVal = (Math.random() * 10 - 5).toFixed(2);
            document.getElementById('liveVal').innerText = randomVal;
            
            let chartData = miniChart.data.datasets[0].data;
            chartData.shift();
            chartData.push(randomVal);
            miniChart.update();
        }
        setInterval(fetchData, 800); 
        setInterval(createLidarDots, 4000);
        
        // CSS Animation Injection
        const styleSheet = document.createElement("style");
        styleSheet.innerText = "@keyframes scan { 0% {transform: rotate(0deg);} 100% {transform: rotate(360deg);} }";
        document.head.appendChild(styleSheet);
    </script>
</body>
</html>