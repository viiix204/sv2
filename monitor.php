<?php
/**
 * AGV Monitor Dashboard — Đại học Công nghiệp TP.HCM
 * Fullstack: PHP + HTML5 + Tailwind CSS + Lucide Icons
 * Version : 2.1.0  (mobile layout fix)
 */
header('Content-Type: text/html; charset=UTF-8');
$pageTitle = 'AGV Monitor — IUI';
$buildDate = date('d/m/Y H:i');
?>
<!DOCTYPE html>
<html lang="vi" class="dark">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover"/>
<title><?= htmlspecialchars($pageTitle) ?></title>

<!-- ═══ FONTS ═══ -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet"/>

<!-- ═══ TAILWIND ═══ -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        sans : ['Outfit', 'sans-serif'],
        mono : ['"JetBrains Mono"', 'monospace'],
      },
      colors: {
        navy: { 950:'#060b14', 900:'#0b1120', 800:'#0f172a', 700:'#151e2f', 600:'#1e293b' }
      },
    }
  }
};
</script>

<!-- ═══ LUCIDE ═══ -->
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

<style>
/* ══════════════════════════════════════════════════════
   CORE LAYOUT STRATEGY
   ─────────────────────────────────────────────────────
   MOBILE  : body is flex-col, NO height/overflow lock.
             Page scrolls naturally: Header → Map → Panel.
   DESKTOP : body is h-100% + overflow:hidden.
             Everything fits in the viewport.
══════════════════════════════════════════════════════ */

*, *::before, *::after { box-sizing: border-box; }

html {
  /* Mobile: auto height so page can scroll */
  height: auto;
  min-height: 100%;
}
body {
  margin: 0; padding: 0;
  min-height: 100vh;
  background: #060b14;
  font-family: 'Outfit', sans-serif;
  color: #cbd5e1;
  display: flex;
  flex-direction: column;
  /* ⚠ NO overflow:hidden on mobile — panel must be visible below map */
  overflow-x: hidden;
}

/* Desktop: lock everything to viewport */
@media (min-width: 1024px) {
  html, body { height: 100%; overflow: hidden; }
}

/* ─────────── APP-BODY (below header) ─────────────
   Mobile  → column, natural height, no clipping
   Desktop → row, fills remaining height, clips overflow
──────────────────────────────────────────────────── */
#app-body {
  display: flex;
  flex-direction: row;   /* sidebar always beside content */
  flex: 1;
  /* Mobile: no min-height constraint needed */
}
@media (min-width: 1024px) {
  #app-body { overflow: hidden; min-height: 0; }
}

/* ─────────── CONTENT AREA (Map + Panel) ──────────
   Mobile  → column
   Desktop → row, overflow hidden
──────────────────────────────────────────────────── */
#content-area {
  flex: 1;
  display: flex;
  flex-direction: column;  /* mobile: map above, panel below */
  min-width: 0;
}
@media (min-width: 1024px) {
  #content-area {
    flex-direction: row;
    overflow: hidden;
    min-height: 0;
  }
}

/* ─────────── MAP SECTION ─────────────────────────
   Mobile  : fixed 50vh, never grows/shrinks
   Desktop : fills all remaining space
──────────────────────────────────────────────────── */
#map-section {
  height: 50vh;
  min-height: 220px;   /* floor so it never collapses */
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
}
@media (min-width: 1024px) {
  #map-section {
    height: auto;       /* let flexbox control height */
    flex: 1 1 0%;
    min-height: 0;
    flex-shrink: 1;
  }
}

/* ─────────── RIGHT PANEL ─────────────────────────
   Mobile  : full width, auto height (stacks below map)
   Desktop : 30% fixed width, scrolls internally
──────────────────────────────────────────────────── */
#right-panel {
  width: 100%;
  flex-shrink: 0;
  background: #060b14;
  border-top: 1px solid #1e293b;
}
@media (min-width: 1024px) {
  #right-panel {
    width: 30%;
    max-width: 360px;
    border-top: none;
    border-left: 1px solid #1e293b;
    overflow-y: auto;
    align-self: stretch;
  }
}

/* ─────────── SCROLLBAR ─────────────────────────── */
::-webkit-scrollbar { width: 4px; height: 4px; }
::-webkit-scrollbar-track { background: #0b1120; }
::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 2px; }
::-webkit-scrollbar-thumb:hover { background: #334155; }

/* ─────────── SIDEBAR (Desktop) ─────────────────── */
#sidebar {
  transition: width 0.3s cubic-bezier(0.16,1,0.3,1);
  flex-shrink: 0;
}
#sidebar.collapsed { width: 72px; }
#sidebar.expanded  { width: 256px; }
.sidebar-label { transition: opacity 0.2s ease, width 0.2s ease; }
#sidebar.collapsed .sidebar-label { opacity:0; width:0; overflow:hidden; white-space:nowrap; }
#sidebar.expanded  .sidebar-label { opacity:1; width:auto; }

/* ─────────── MOBILE DRAWER ─────────────────────── */
#sidebar-mobile {
  transform: translateX(-100%);
  transition: transform 0.3s cubic-bezier(0.16,1,0.3,1);
}
#sidebar-mobile.open { transform: translateX(0); }

/* ─────────── TYPOGRAPHY ─────────────────────────── */
.font-mono { font-family: 'JetBrains Mono', monospace; }

/* ─────────── STATUS BADGES ─────────────────────── */
.badge-running  { background:#052e16; color:#4ade80; border:1px solid #166534; }
.badge-charging { background:#1c1408; color:#fbbf24; border:1px solid #854d0e; }
.badge-error    { background:#1e0a0a; color:#f87171; border:1px solid #7f1d1d; }
.badge-idle     { background:#0f172a; color:#94a3b8; border:1px solid #1e293b; }

/* ─────────── BATTERY BAR ───────────────────────── */
.progress-track { background:#1e293b; border-radius:4px; overflow:hidden; height:7px; }
.progress-fill  {
  height:100%; border-radius:4px;
  transition: width 0.8s cubic-bezier(0.4,0,0.2,1);
  background: linear-gradient(90deg, #10b981, #34d399);
}
.progress-fill.warn { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.progress-fill.crit { background: linear-gradient(90deg, #ef4444, #f87171); }

/* ─────────── PANEL GLOW ────────────────────────── */
.panel-glow { border:1px solid #1e293b; box-shadow:inset 0 1px 0 rgba(148,163,184,0.04); }

/* ─────────── MAP NAV BUTTONS ───────────────────── */
.map-nav-btn {
  padding: 5px 11px; font-size: 12px; font-weight: 500;
  border-radius: 6px; border: 1px solid #1e293b;
  background: #0b1120; color: #64748b;
  cursor: pointer; transition: all 0.2s ease;
  letter-spacing: 0.02em; white-space: nowrap;
}
.map-nav-btn:hover  { border-color:#334155; color:#94a3b8; }
.map-nav-btn.active {
  background:#0d1f3c; border-color:#2563eb;
  color:#60a5fa; box-shadow:0 0 12px rgba(37,99,235,0.2);
}

/* ─────────── SIDEBAR NAV ───────────────────────── */
.nav-item {
  display:flex; align-items:center; gap:12px;
  padding:9px 14px; border-radius:8px;
  cursor:pointer; font-size:14px; font-weight:500;
  color:#64748b; border:1px solid transparent;
  transition:all 0.2s ease; text-decoration:none;
  white-space:nowrap; overflow:hidden;
}
.nav-item:hover  { background:#0f172a; color:#94a3b8; border-color:#1e293b; }
.nav-item.active { background:#0d1f3c; color:#60a5fa; border-color:#1e3a6e; box-shadow:inset 2px 0 0 #3b82f6; }
.nav-icon { flex-shrink:0; }

/* ─────────── ODOMETRY CELLS ────────────────────── */
.odom-cell {
  background:#0b1120; border:1px solid #1e293b;
  border-radius:8px; padding:10px 8px;
  text-align:center; flex:1;
}

/* ─────────── CONNECTION DOTS ───────────────────── */
.conn-dot { width:8px; height:8px; border-radius:50%; display:inline-block; flex-shrink:0; }
.conn-dot.online  { background:#4ade80; box-shadow:0 0 6px #4ade80; }
.conn-dot.offline { background:#6b7280; }
.conn-dot.warn    { background:#fbbf24; box-shadow:0 0 6px #fbbf24; }

/* ─────────── MAP GRID ──────────────────────────── */
.grid-bg {
  background-color: #070d1a;
  background-image:
    linear-gradient(rgba(30,41,59,0.55) 1px, transparent 1px),
    linear-gradient(90deg, rgba(30,41,59,0.55) 1px, transparent 1px),
    linear-gradient(rgba(30,41,59,0.18) 1px, transparent 1px),
    linear-gradient(90deg, rgba(30,41,59,0.18) 1px, transparent 1px);
  background-size: 80px 80px, 80px 80px, 16px 16px, 16px 16px;
}

/* ─────────── AGV DOT ───────────────────────────── */
.agv-dot {
  position:absolute; width:14px; height:14px;
  background:#ef4444; border-radius:50%;
  animation: agvPulse 2s ease-out infinite;
}
@keyframes agvPulse {
  0%   { box-shadow:0 0 0 0 rgba(239,68,68,0.7); }
  70%  { box-shadow:0 0 0 14px rgba(239,68,68,0); }
  100% { box-shadow:0 0 0 0 rgba(239,68,68,0); }
}

/* ─────────── SCAN LINE ─────────────────────────── */
@keyframes scanLine {
  0%   { transform:translateY(0); opacity:0.6; }
  100% { transform:translateY(100%); opacity:0; }
}
.scan-line {
  position:absolute; top:0; left:0; right:0; height:2px; pointer-events:none;
  background: linear-gradient(90deg, transparent, #3b82f620, #3b82f6, #3b82f620, transparent);
  animation: scanLine 4s linear infinite;
}

/* ─────────── LIDAR POINTS ──────────────────────── */
.lidar-point { position:absolute; width:3px; height:3px; background:#06b6d4; border-radius:50%; opacity:0.6; }

/* ─────────── MAP BLINK ─────────────────────────── */
.blink-map { animation: blinkMap 0.4s ease-in-out 2; }
@keyframes blinkMap {
  0%,100% { opacity:1; }
  50%     { opacity:0.25; }
}

/* ─────────── VALUE FLASH ───────────────────────── */
.value-flash { transition: color 0.3s ease; }
.value-flash.updated { color:#38bdf8 !important; }

/* ─────────── FADE-IN ───────────────────────────── */
@keyframes fadeInUp {
  from { opacity:0; transform:translateY(10px); }
  to   { opacity:1; transform:translateY(0); }
}
.fade-in-up { animation: fadeInUp 0.45s ease-out both; }
.d1 { animation-delay:0.05s; }
.d2 { animation-delay:0.12s; }
.d3 { animation-delay:0.19s; }
.d4 { animation-delay:0.26s; }
</style>
</head>

<!-- ═══ BODY: flex-col, NO overflow-hidden on mobile ═══ -->
<body>

<!-- Mobile Overlay -->
<div id="overlay"
     class="fixed inset-0 z-40 hidden lg:hidden"
     style="background:rgba(0,0,0,0.6); backdrop-filter:blur(4px);"
     onclick="closeMobileSidebar()"></div>

<!-- Mobile Sidebar Drawer -->
<aside id="sidebar-mobile"
       class="fixed top-0 left-0 h-full w-64 z-50 flex flex-col lg:hidden"
       style="background:#0b1120; border-right:1px solid #1e293b;">
  <div class="flex items-center justify-between px-4 py-4" style="border-bottom:1px solid #1e293b;">
    <div class="flex items-center gap-2">
      <div class="w-7 h-7 rounded-lg bg-blue-600 flex items-center justify-center">
        <i data-lucide="cpu" class="w-4 h-4 text-white"></i>
      </div>
      <span class="text-sm font-semibold text-slate-200">AGV Control</span>
    </div>
    <button onclick="closeMobileSidebar()" class="p-1 text-slate-500 hover:text-slate-300">
      <i data-lucide="x" class="w-4 h-4"></i>
    </button>
  </div>
  <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
    <a href="#" class="nav-item active" onclick="closeMobileSidebar()">
      <i data-lucide="layout-dashboard" class="w-4 h-4 nav-icon"></i><span>Dashboard</span>
    </a>
    <a href="#" class="nav-item" onclick="closeMobileSidebar()">
      <i data-lucide="route" class="w-4 h-4 nav-icon"></i><span>Lộ Trình</span>
    </a>
    <a href="#" class="nav-item" onclick="closeMobileSidebar()">
      <i data-lucide="activity" class="w-4 h-4 nav-icon"></i><span>Telemetry</span>
    </a>
    <a href="#" class="nav-item" onclick="closeMobileSidebar()">
      <i data-lucide="map-pin" class="w-4 h-4 nav-icon"></i><span>Điểm Waypoint</span>
    </a>
    <a href="#" class="nav-item" onclick="closeMobileSidebar()">
      <i data-lucide="bell" class="w-4 h-4 nav-icon"></i>
      <span>Cảnh Báo</span>
      <span class="ml-auto bg-red-500 text-white text-[10px] font-bold px-1.5 rounded-full">3</span>
    </a>
    <div class="pt-3 mt-3" style="border-top:1px solid #1e293b;">
      <a href="#" class="nav-item" onclick="closeMobileSidebar()">
        <i data-lucide="settings" class="w-4 h-4 nav-icon"></i><span>Cài Đặt</span>
      </a>
    </div>
  </nav>
  <div class="px-4 py-3" style="border-top:1px solid #1e293b;">
    <p class="text-[11px] text-slate-600 font-mono">Build: <?= $buildDate ?></p>
  </div>
</aside>

<!-- ═══════════════════════════════════════════════
     HEADER — flex-shrink-0, always 56px
═══════════════════════════════════════════════════ -->
<header class="flex-shrink-0 flex items-center justify-between px-4 lg:px-5"
        style="height:64px; background:#0b1120; border-bottom:1px solid #1e293b; position:relative; z-index:10;">

  <div class="flex items-center gap-2.5">
    <!-- Mobile hamburger -->
    <button onclick="openMobileSidebar()"
            class="lg:hidden p-1.5 rounded-md text-slate-500 hover:text-slate-300 transition-colors">
      <i data-lucide="menu" class="w-5 h-5"></i>
    </button>
    <!-- Desktop sidebar toggle -->
    <button onclick="toggleSidebar()"
            class="hidden lg:flex p-1.5 rounded-md text-slate-500 hover:text-slate-300 transition-colors">
      <i data-lucide="panel-left" class="w-5 h-5"></i>
    </button>
    <!-- Logo -->
    <div class="flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center"
           style="box-shadow:0 4px 14px rgba(37,99,235,0.4);">
        <i data-lucide="cpu" class="w-5 h-5 text-white"></i>
      </div>
      <div>
        <p class="text-[14px] font-semibold text-slate-200 leading-none">Đại học Công nghiệp TP.HCM</p>
        <p class="text-[11px] text-slate-500 leading-none mt-1">AGV Monitoring System</p>
      </div>
    </div>
  </div>

  <!-- Center pills (hidden on small mobile) -->
  <div class="hidden md:flex items-center gap-2">
    <div class="flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-medium"
         style="background:#052e16; border:1px solid #166534; color:#4ade80;">
      <span class="conn-dot online"></span><span>ROS Bridge</span>
    </div>
    <div class="flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-medium"
         style="background:#0f172a; border:1px solid #1e293b; color:#60a5fa;">
      <span class="conn-dot online"></span><span>MQTT Broker</span>
    </div>
  </div>

  <!-- Clock -->
  <div class="flex items-center gap-3">
    <div class="text-right hidden sm:block">
      <p id="current-time" class="font-mono text-[15px] font-semibold text-blue-400 leading-none">--:--:--</p>
      <p id="current-date" class="font-mono text-[11px] text-slate-600 leading-none mt-1">--/--/----</p>
    </div>
    <div class="w-8 h-8 rounded-full flex items-center justify-center cursor-pointer hover:border-blue-500 transition-colors"
         style="border:1px solid #1e293b; background:#0f172a;">
      <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
    </div>
  </div>
</header>

<!-- ═══════════════════════════════════════════════
     APP BODY
     CSS: flex-row | mobile: natural | desktop: overflow-hidden
═══════════════════════════════════════════════════ -->
<div id="app-body">

  <!-- Desktop sidebar — hidden on mobile -->
  <aside id="sidebar"
         class="hidden lg:flex flex-col expanded"
         style="background:#0b1120; border-right:1px solid #1e293b; overflow:hidden;">
    <nav class="flex-1 px-2 py-4 space-y-0.5 overflow-y-auto overflow-x-hidden">
      <a href="#" class="nav-item active">
        <i data-lucide="layout-dashboard" class="w-4 h-4 nav-icon"></i>
        <span class="sidebar-label">Tổng quan</span>
      </a>
      <a href="#" class="nav-item">
        <i data-lucide="route" class="w-4 h-4 nav-icon"></i>
        <span class="sidebar-label">Lộ Trình</span>
      </a>
      <a href="#" class="nav-item">
        <i data-lucide="activity" class="w-4 h-4 nav-icon"></i>
        <span class="sidebar-label">Telemetry</span>
      </a>
      <a href="#" class="nav-item">
        <i data-lucide="map-pin" class="w-4 h-4 nav-icon"></i>
        <span class="sidebar-label">Điểm Waypoint</span>
      </a>
      <a href="#" class="nav-item">
        <i data-lucide="bell" class="w-4 h-4 nav-icon"></i>
        <span class="sidebar-label">Cảnh Báo</span>
        <span class="ml-auto sidebar-label bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full flex-shrink-0">3</span>
      </a>
      <div class="pt-3 mt-3" style="border-top:1px solid #1e293b;">
        <a href="#" class="nav-item">
          <i data-lucide="settings" class="w-4 h-4 nav-icon"></i>
          <span class="sidebar-label">Cài Đặt</span>
        </a>
        <a href="#" class="nav-item">
          <i data-lucide="book-open" class="w-4 h-4 nav-icon"></i>
          <span class="sidebar-label">Tài Liệu</span>
        </a>
      </div>
    </nav>
    <div class="px-3 py-3 sidebar-label overflow-hidden" style="border-top:1px solid #1e293b;">
      <div class="flex items-center gap-2 px-1">
        <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0"
             style="background:#1e3a5f; border:1px solid #2563eb44;">
          <i data-lucide="user" class="w-3.5 h-3.5 text-blue-400"></i>
        </div>
        <div class="overflow-hidden">
          <p class="text-[12px] font-medium text-slate-300 whitespace-nowrap">Operator</p>
          <p class="text-[10px] text-slate-600 whitespace-nowrap">IUI — Lab 305</p>
        </div>
      </div>
    </div>
  </aside>

  <!-- ═════════════════════════════════════════════
       CONTENT AREA (Map + Panel)
  ═════════════════════════════════════════════ -->
  <div id="content-area">

    <!-- ──────────────────────────────────────────
         MAP SECTION
         CSS: 50vh mobile → flex-1 desktop
    ────────────────────────────────────────── -->
    <section id="map-section">

      <!-- Toolbar -->
      <div class="flex-shrink-0 flex items-center justify-between px-3 py-2"
           style="background:#0b1120; border-bottom:1px solid #1e293b;">
        <!-- Mode buttons: allow horizontal scroll on tiny screens -->
        <div class="flex items-center gap-1.5 overflow-x-auto flex-1 min-w-0"
             style="scrollbar-width:none; -webkit-overflow-scrolling:touch;">
          <button onclick="setMapMode('lidar')"  id="btn-lidar"  class="map-nav-btn active">LiDAR</button>
          <button onclick="setMapMode('sensor')" id="btn-sensor" class="map-nav-btn">Cảm Biến</button>
          <button onclick="setMapMode('zone')"   id="btn-zone"   class="map-nav-btn">Vùng</button>
          <button onclick="setMapMode('map')"    id="btn-map"    class="map-nav-btn">Bản Đồ</button>
        </div>
        <!-- Controls -->
        <div class="flex items-center gap-1.5 flex-shrink-0 ml-2">
          <button onclick="zoomMap(1)"
                  class="w-7 h-7 rounded-md flex items-center justify-center text-slate-500 hover:text-slate-300"
                  style="background:#0f172a; border:1px solid #1e293b;">
            <i data-lucide="zoom-in" class="w-3.5 h-3.5"></i>
          </button>
          <button onclick="zoomMap(-1)"
                  class="w-7 h-7 rounded-md flex items-center justify-center text-slate-500 hover:text-slate-300"
                  style="background:#0f172a; border:1px solid #1e293b;">
            <i data-lucide="zoom-out" class="w-3.5 h-3.5"></i>
          </button>
          <button onclick="resetMapView()"
                  class="w-7 h-7 rounded-md flex items-center justify-center text-slate-500 hover:text-slate-300"
                  style="background:#0f172a; border:1px solid #1e293b;">
            <i data-lucide="crosshair" class="w-3.5 h-3.5"></i>
          </button>
          <span id="map-mode-label"
                class="hidden sm:inline text-[11px] text-slate-600 font-mono ml-1">LiDAR View</span>
        </div>
      </div>

      <!-- Map canvas — fills remaining height of section -->
      <div id="map-visuals-container"
           class="grid-bg relative overflow-hidden"
           style="flex:1; min-height:0;">

        <div class="scan-line"></div>

        <!-- Zone overlays -->
        <div id="zone-overlays" class="absolute inset-0 pointer-events-none hidden">
          <div class="absolute rounded-lg"
               style="left:10%;top:15%;width:35%;height:30%;background:rgba(59,130,246,0.06);border:1px dashed rgba(59,130,246,0.3);">
            <span class="absolute top-1.5 left-2 text-[10px] text-blue-400 font-mono font-medium">ZONE A</span>
          </div>
          <div class="absolute rounded-lg"
               style="left:55%;top:20%;width:32%;height:40%;background:rgba(16,185,129,0.06);border:1px dashed rgba(16,185,129,0.3);">
            <span class="absolute top-1.5 left-2 text-[10px] text-emerald-400 font-mono font-medium">ZONE B</span>
          </div>
          <div class="absolute rounded-lg"
               style="left:20%;top:65%;width:20%;height:20%;background:rgba(251,191,36,0.06);border:1px dashed rgba(251,191,36,0.3);">
            <span class="absolute top-1.5 left-2 text-[10px] text-yellow-400 font-mono font-medium">CHARGE</span>
          </div>
        </div>

        <!-- LiDAR points -->
        <div id="lidar-points" class="absolute inset-0 pointer-events-none"></div>

        <!-- Waypoints -->
        <div id="waypoints-layer" class="absolute inset-0 pointer-events-none">
          <div class="absolute flex flex-col items-center" style="left:25%;top:30%;">
            <div class="w-5 h-5 rounded-full border-2 border-blue-500 flex items-center justify-center" style="background:rgba(30,58,138,0.5);">
              <span class="text-[8px] text-blue-300 font-mono font-bold">1</span>
            </div>
            <div class="w-px h-3" style="background:rgba(59,130,246,0.5);"></div>
            <span class="text-[9px] text-blue-400 font-mono px-1 rounded" style="background:rgba(11,17,32,0.6);">WP-01</span>
          </div>
          <div class="absolute flex flex-col items-center" style="left:65%;top:40%;">
            <div class="w-5 h-5 rounded-full border-2 border-emerald-500 flex items-center justify-center" style="background:rgba(6,78,59,0.5);">
              <span class="text-[8px] text-emerald-300 font-mono font-bold">2</span>
            </div>
            <div class="w-px h-3" style="background:rgba(16,185,129,0.5);"></div>
            <span class="text-[9px] text-emerald-400 font-mono px-1 rounded" style="background:rgba(11,17,32,0.6);">WP-02</span>
          </div>
          <div class="absolute flex flex-col items-center" style="left:40%;top:70%;">
            <div class="w-5 h-5 rounded-full border-2 border-slate-500 flex items-center justify-center" style="background:rgba(30,41,59,0.5);">
              <span class="text-[8px] text-slate-300 font-mono font-bold">3</span>
            </div>
            <div class="w-px h-3" style="background:rgba(100,116,139,0.5);"></div>
            <span class="text-[9px] text-slate-400 font-mono px-1 rounded" style="background:rgba(11,17,32,0.6);">WP-03</span>
          </div>
        </div>

        <!-- Path -->
        <svg class="absolute inset-0 w-full h-full pointer-events-none">
          <defs>
            <marker id="arrowhead" markerWidth="6" markerHeight="4" refX="6" refY="2" orient="auto">
              <polygon points="0 0,6 2,0 4" fill="rgba(96,165,250,0.7)"/>
            </marker>
          </defs>
          <path d="M 25% 30% Q 50% 20% 65% 40% T 40% 70%"
                fill="none" stroke="rgba(96,165,250,0.35)"
                stroke-width="1.5" stroke-dasharray="6 4"
                marker-end="url(#arrowhead)"/>
        </svg>

        <!-- AGV Robot -->
        <div id="agv-position" class="absolute"
             style="left:calc(50% - 7px); top:calc(50% - 7px); transition:left 0.8s ease,top 0.8s ease;">
          <div class="relative">
            <div class="agv-dot"></div>
            <!-- Direction arrow -->
            <div id="agv-arrow"
                 class="absolute flex flex-col items-center pointer-events-none"
                 style="bottom:100%; left:50%; transform:translateX(-50%) rotate(0deg); transition:transform 0.5s ease; padding-bottom:2px;">
              <div style="width:0;height:0;border-left:4px solid transparent;border-right:4px solid transparent;border-bottom:5px solid rgba(239,68,68,0.8);"></div>
              <div style="width:1px;height:12px;background:rgba(239,68,68,0.6);"></div>
            </div>
          </div>
        </div>

        <!-- Coordinate overlay -->
        <div class="absolute top-2 right-2 flex flex-col gap-1">
          <div class="flex items-center gap-1 text-[10px] font-mono px-2 py-1 rounded"
               style="background:rgba(11,17,32,0.85); border:1px solid #1e293b; color:#60a5fa;">
            <i data-lucide="crosshair" class="w-3 h-3"></i>
            <span>X:<span id="map-x-display">0.00</span> Y:<span id="map-y-display">0.00</span></span>
          </div>
          <div class="flex items-center gap-1 text-[10px] font-mono px-2 py-1 rounded"
               style="background:rgba(11,17,32,0.85); border:1px solid #1e293b; color:#a78bfa;">
            <i data-lucide="navigation-2" class="w-3 h-3"></i>
            <span>θ: <span id="map-theta-display">0.0°</span></span>
          </div>
        </div>

        <!-- Scale bar -->
        <div class="absolute bottom-2 left-3">
          <div style="width:56px;height:2px;background:#334155;position:relative;">
            <div style="position:absolute;left:0;top:-3px;width:1px;height:8px;background:#475569;"></div>
            <div style="position:absolute;right:0;top:-3px;width:1px;height:8px;background:#475569;"></div>
          </div>
          <p class="text-[9px] text-slate-600 font-mono mt-0.5 text-center">5 m</p>
        </div>

        <!-- Compass -->
        <div class="absolute bottom-2 right-2 w-7 h-7 rounded-full flex items-center justify-center"
             style="background:rgba(11,17,32,0.85); border:1px solid #1e293b;">
          <i data-lucide="navigation" class="w-3.5 h-3.5 text-blue-400"></i>
        </div>
      </div><!-- /map canvas -->

      <!-- Map status bar -->
      <div class="flex-shrink-0 flex items-center justify-between px-4 py-2"
           style="background:#0b1120; border-top:1px solid #1e293b; padding-bottom: max(8px, env(safe-area-inset-bottom, 8px))">
        <div class="flex items-center gap-4 text-[11px] text-slate-600">
          <span class="flex items-center gap-1.5">
            <span class="conn-dot online"></span>
            <span>LiDAR aktif</span>
          </span>
          <span class="flex items-center gap-1.5 font-mono">
            <i data-lucide="refresh-cw" class="w-3 h-3"></i>
            <span id="update-counter">0</span> cập nhật
          </span>
        </div>
        <div class="flex items-center gap-1 text-[11px] text-slate-600 font-mono">
          <i data-lucide="timer" class="w-3 h-3"></i>
          <span>Polling: 1s</span>
        </div>
      </div>
    </section><!-- /map-section -->

    <!-- ──────────────────────────────────────────
         RIGHT PANEL — Thông Số
         CSS: full-width below map on mobile
              30% fixed with internal scroll on desktop
    ────────────────────────────────────────── -->
    <aside id="right-panel">

      <!-- On desktop this div fills 100% height; on mobile it just wraps content -->
      <div class="p-3 pb-5 lg:pb-4 lg:h-full lg:flex lg:flex-col">
        <!-- Outer container (mẹ bồng con) — flex-col + flex-1 fills available height on desktop -->
        <div class="rounded-2xl panel-glow p-2.5 flex flex-col gap-2 lg:flex-1 lg:min-h-0" style="background:#0b1120;">

          <!-- Label row -->
          <div class="flex items-center justify-between px-1">
            <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-widest">Thông Số AGV</p>
            <div class="flex items-center gap-1.5 text-[10px] text-slate-600 font-mono">
              <span id="api-indicator" class="conn-dot online"></span>
              <span id="api-status-text">Live</span>
            </div>
          </div>

          <!-- ══ W1: PHƯƠNG TIỆN ══ -->
          <div class="rounded-xl px-3 py-2.5 fade-in-up d1 lg:flex-1 lg:flex lg:flex-col lg:justify-between" style="background:#151e2f; border:1px solid #1e2a3d;">
            <div class="flex items-center justify-between gap-2">
              <div class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:#0b1120; border:1px solid #1e293b;">
                  <i data-lucide="truck" class="w-5 h-5 text-blue-400"></i>
                </div>
                <div>
                  <div class="flex items-center gap-1.5">
                    <p id="agv-name" class="text-[14px] font-semibold text-slate-200 leading-none">AGV-001</p>
                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded"
                          style="background:#1e3a6e; color:#60a5fa; border:1px solid rgba(37,99,235,0.3); letter-spacing:0.05em;">
                      MAIN
                    </span>
                  </div>
                  <p class="text-[11px] text-slate-500 mt-0.5">Diff-Drive · ROS 2</p>
                </div>
              </div>
              <span id="agv-status-badge"
                    class="text-[11px] font-semibold px-2.5 py-1 rounded-lg badge-running flex items-center gap-1.5 flex-shrink-0">
                <span id="status-dot" class="w-1.5 h-1.5 rounded-full bg-green-400 inline-block"
                      style="animation:pulse 3s ease-in-out infinite;"></span>
                <span id="agv-status">CHẠY</span>
              </span>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-2 pt-2" style="border-top:1px solid #1e293b;">
              <div class="text-center">
                <p class="font-mono text-[13px] font-semibold text-blue-400">
                  <span id="agv-speed">0.38</span><span class="text-[10px] text-slate-500"> m/s</span>
                </p>
                <p class="text-[10px] text-slate-600 mt-0.5">Tốc Độ</p>
              </div>
              <div class="text-center" style="border-left:1px solid #1e293b; border-right:1px solid #1e293b;">
                <p class="font-mono text-[13px] font-semibold text-emerald-400">
                  <span id="agv-uptime">02:14</span>
                </p>
                <p class="text-[10px] text-slate-600 mt-0.5">Uptime</p>
              </div>
              <div class="text-center">
                <p class="font-mono text-[13px] font-semibold text-violet-400">
                  <span id="agv-mission">M-07</span>
                </p>
                <p class="text-[10px] text-slate-600 mt-0.5">Nhiệm Vụ</p>
              </div>
            </div>
          </div>

          <!-- ══ W2: PIN ══ -->
          <div class="rounded-xl px-3 py-2.5 fade-in-up d2 lg:flex-1 lg:flex lg:flex-col lg:justify-between" style="background:#151e2f; border:1px solid #1e2a3d;">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <i data-lucide="battery-charging" id="battery-icon" class="w-4 h-4 text-emerald-400"></i>
                <span class="text-[12px] font-semibold text-slate-300">Pin</span>
              </div>
              <div class="flex items-center gap-2">
                <span class="font-mono text-[11px] text-slate-500">
                  <span id="battery-voltage">24.6</span> V
                </span>
                <span id="battery-percent"
                      class="font-mono text-[18px] font-bold text-emerald-400 leading-none value-flash">
                  78%
                </span>
              </div>
            </div>
            <div class="progress-track mb-2">
              <div id="battery-bar" class="progress-fill" style="width:78%;"></div>
            </div>
            <div class="flex items-center justify-between text-[10px] text-slate-600">
              <span class="flex items-center gap-1">
                <i data-lucide="zap" class="w-3 h-3 text-yellow-500"></i>
                Dòng: <span id="battery-current" class="font-mono text-slate-500 ml-0.5">2.4 A</span>
              </span>
              <span class="flex items-center gap-1">
                <i data-lucide="thermometer" class="w-3 h-3 text-orange-400"></i>
                <span id="battery-temp" class="font-mono text-slate-500">36.2°C</span>
              </span>
              <span class="flex items-center gap-1">
                <i data-lucide="clock" class="w-3 h-3 text-blue-400"></i>
                ETA: <span id="battery-eta" class="font-mono text-slate-500 ml-0.5">1h 22m</span>
              </span>
            </div>
          </div>

          <!-- ══ W3: MẠNG ══ -->
          <div class="rounded-xl px-3 py-2.5 fade-in-up d3 lg:flex-1 lg:flex lg:flex-col lg:justify-between" style="background:#151e2f; border:1px solid #1e2a3d;">
            <div class="flex items-center gap-2 mb-2">
              <i data-lucide="wifi" class="w-4 h-4 text-blue-400"></i>
              <span class="text-[12px] font-semibold text-slate-300">Kết Nối Mạng</span>
            </div>
            <div class="space-y-1.5">
              <div class="flex items-center justify-between py-1 px-2 rounded-lg"
                   style="background:#0b1120; border:1px solid #1e293b;">
                <div class="flex items-center gap-2">
                  <span id="ros-dot" class="conn-dot online"></span>
                  <span class="text-[11px] text-slate-400">ROS Bridge</span>
                </div>
                <span id="ros-status" class="text-[11px] font-mono text-emerald-400">Kết nối</span>
              </div>
              <div class="flex items-center justify-between py-1 px-2 rounded-lg"
                   style="background:#0b1120; border:1px solid #1e293b;">
                <div class="flex items-center gap-2">
                  <span id="mqtt-dot" class="conn-dot online"></span>
                  <span class="text-[11px] text-slate-400">MQTT Broker</span>
                </div>
                <span id="mqtt-status" class="text-[11px] font-mono text-emerald-400">Online</span>
              </div>
              <div class="flex items-center justify-between py-1 px-2 rounded-lg"
                   style="background:#0b1120; border:1px solid #1e293b;">
                <div class="flex items-center gap-2">
                  <span id="wifi-dot" class="conn-dot online"></span>
                  <span class="text-[11px] text-slate-400">Wi-Fi (IUI-IoT)</span>
                </div>
                <span id="wifi-rssi" class="text-[11px] font-mono text-blue-400">-52 dBm</span>
              </div>
            </div>
            <div class="mt-1.5 flex items-center justify-between text-[11px]">
              <span class="text-slate-600">Trạng thái tổng:</span>
              <span id="network-status" class="font-semibold text-emerald-400 flex items-center gap-1.5">
                <span class="conn-dot online"></span>Bình Thường
              </span>
            </div>
          </div>

          <!-- ══ W4: ODOMETRY ══ -->
          <div class="rounded-xl px-3 py-2.5 fade-in-up d4 lg:flex-1 lg:flex lg:flex-col lg:justify-between" style="background:#151e2f; border:1px solid #1e2a3d;">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center gap-2">
                <i data-lucide="navigation-2" class="w-4 h-4 text-violet-400"></i>
                <span class="text-[12px] font-semibold text-slate-300">Odometry</span>
              </div>
              <span class="text-[9px] font-mono text-slate-600 px-1.5 py-0.5 rounded"
                    style="border:1px solid #1e293b;">/odom</span>
            </div>
            <div class="flex gap-2">
              <div class="odom-cell">
                <p class="text-[9px] text-slate-600 uppercase tracking-widest mb-1">Trục X</p>
                <p id="odom-x" class="font-mono text-[15px] font-semibold text-blue-400 value-flash">2.340</p>
                <p class="text-[9px] text-slate-600 mt-0.5">m</p>
              </div>
              <div class="odom-cell">
                <p class="text-[9px] text-slate-600 uppercase tracking-widest mb-1">Trục Y</p>
                <p id="odom-y" class="font-mono text-[15px] font-semibold text-emerald-400 value-flash">1.120</p>
                <p class="text-[9px] text-slate-600 mt-0.5">m</p>
              </div>
              <div class="odom-cell">
                <p class="text-[9px] text-slate-600 uppercase tracking-widest mb-1">Góc θ</p>
                <p id="odom-theta" class="font-mono text-[15px] font-semibold text-violet-400 value-flash">45.2°</p>
                <p class="text-[9px] text-slate-600 mt-0.5">deg</p>
              </div>
            </div>
            <!-- Compact velocity + accuracy inline row -->
            <div class="flex items-center justify-between mt-2 pt-2" style="border-top:1px solid #1e293b;">
              <span class="flex items-center gap-1.5 text-[10px] text-slate-600">
                <i data-lucide="move-right" class="w-3 h-3 text-blue-400"></i>
                <span id="vel-linear" class="font-mono text-slate-300 font-semibold">0.38</span>
                <span class="text-slate-600">m/s</span>
              </span>
              <span class="flex items-center gap-1.5 text-[10px] text-slate-600">
                <i data-lucide="rotate-cw" class="w-3 h-3 text-violet-400"></i>
                <span id="vel-angular" class="font-mono text-slate-300 font-semibold">0.05</span>
                <span class="text-slate-600">rad/s</span>
              </span>
              <span class="font-mono text-[10px] text-emerald-400">±0.012 m</span>
            </div>
          </div>

        </div><!-- /outer container -->
      </div><!-- /p-3 lg:h-full -->

    </aside><!-- /right-panel -->
  </div><!-- /content-area -->
</div><!-- /app-body -->

<!-- ═══════════════ JAVASCRIPT ═══════════════ -->
<script>
/* 1. INIT */
document.addEventListener('DOMContentLoaded', () => {
  lucide.createIcons();
  updateClock(); setInterval(updateClock, 1000);
  generateLidarPoints();
  fetchRealtimeData(); setInterval(fetchRealtimeData, 1000);
});

/* 2. CLOCK */
function updateClock() {
  const n = new Date(), p = v => String(v).padStart(2,'0');
  setText('current-time', `${p(n.getHours())}:${p(n.getMinutes())}:${p(n.getSeconds())}`);
  setText('current-date', `${p(n.getDate())}/${p(n.getMonth()+1)}/${n.getFullYear()}`);
}

/* 3. DESKTOP SIDEBAR */
let sidebarExpanded = true;
function toggleSidebar() {
  const s = document.getElementById('sidebar');
  if (!s) return;
  sidebarExpanded = !sidebarExpanded;
  s.classList.toggle('collapsed', !sidebarExpanded);
  s.classList.toggle('expanded',   sidebarExpanded);
}

/* 4. MOBILE SIDEBAR */
function openMobileSidebar() {
  document.getElementById('sidebar-mobile').classList.add('open');
  document.getElementById('overlay').classList.remove('hidden');
}
function closeMobileSidebar() {
  document.getElementById('sidebar-mobile').classList.remove('open');
  document.getElementById('overlay').classList.add('hidden');
}

/* 5. MAP MODE */
const mapLabels = { lidar:'LiDAR View', sensor:'Sensor Overlay', zone:'Zone Management', map:'Floor Map' };
function setMapMode(mode) {
  ['lidar','sensor','zone','map'].forEach(m => document.getElementById('btn-'+m)?.classList.remove('active'));
  document.getElementById('btn-'+mode)?.classList.add('active');
  setText('map-mode-label', mapLabels[mode]||mode);
  const z = document.getElementById('zone-overlays');
  const l = document.getElementById('lidar-points');
  if (z) z.classList.toggle('hidden', mode !== 'zone');
  if (l) l.style.opacity = mode === 'lidar' ? '1' : '0.1';
  const mc = document.getElementById('map-visuals-container');
  if (mc) { mc.classList.remove('blink-map'); void mc.offsetWidth; mc.classList.add('blink-map'); setTimeout(() => mc.classList.remove('blink-map'), 900); }
}

/* 6. LIDAR POINTS */
function generateLidarPoints() {
  const c = document.getElementById('lidar-points');
  if (!c) return; c.innerHTML = '';
  const cols = ['#06b6d4','#22d3ee','#34d399','#60a5fa'];
  for (let i = 0; i < 120; i++) {
    const pt = document.createElement('div'); pt.className = 'lidar-point';
    const a = Math.random()*Math.PI*2, r = 15+Math.random()*35;
    pt.style.left       = Math.max(2,Math.min(97, 50+r*Math.cos(a))) + '%';
    pt.style.top        = Math.max(2,Math.min(97, 50+r*Math.sin(a))) + '%';
    pt.style.opacity    = (0.3+Math.random()*0.5).toFixed(2);
    pt.style.background = cols[Math.floor(Math.random()*cols.length)];
    c.appendChild(pt);
  }
}

/* 7. ZOOM / RESET */
let mapZoom = 1.0;
function zoomMap(dir) {
  mapZoom = Math.max(0.5, Math.min(3.0, mapZoom + dir*0.15));
  ['lidar-points','waypoints-layer','zone-overlays'].forEach(id => {
    const el = document.getElementById(id);
    if (el) { el.style.transform = `scale(${mapZoom})`; el.style.transformOrigin = 'center'; }
  });
}
function resetMapView() {
  mapZoom = 1.0;
  ['lidar-points','waypoints-layer','zone-overlays'].forEach(id => {
    const el = document.getElementById(id); if (el) el.style.transform = 'scale(1)';
  });
  const a = document.getElementById('agv-position');
  if (a) { a.style.left = 'calc(50% - 7px)'; a.style.top = 'calc(50% - 7px)'; }
}

/* 8. AGV ON MAP */
function updateAgvOnMap(x, y, theta) {
  const agv = document.getElementById('agv-position');
  const arr = document.getElementById('agv-arrow');
  if (!agv) return;
  const px = 50 + (parseFloat(x)/10)*50;
  const py = 50 - (parseFloat(y)/10)*50;
  agv.style.left = Math.max(2,Math.min(96,px)).toFixed(1)+'%';
  agv.style.top  = Math.max(2,Math.min(96,py)).toFixed(1)+'%';
  if (arr) arr.style.transform = `translateX(-50%) rotate(${parseFloat(theta)||0}deg)`;
  setText('map-x-display', parseFloat(x).toFixed(2));
  setText('map-y-display', parseFloat(y).toFixed(2));
  setText('map-theta-display', parseFloat(theta).toFixed(1)+'°');
}

/* 9. STATUS STYLES */
function applyStatusStyle(status) {
  const badge = document.getElementById('agv-status-badge');
  const lbl   = document.getElementById('agv-status');
  const dot   = document.getElementById('status-dot');
  const bico  = document.getElementById('battery-icon');
  if (!badge) return;
  badge.className = badge.className.replace(/badge-\w+/g,'').trim();
  const cfg = {
    running  : { cls:'badge-running',  label:'CHẠY',    dot:'bg-green-400',  ico:'text-emerald-400' },
    charging : { cls:'badge-charging', label:'SẠC PIN', dot:'bg-yellow-400', ico:'text-yellow-400'  },
    idle     : { cls:'badge-idle',     label:'CHỜ',     dot:'bg-slate-500',  ico:'text-slate-400'   },
    error    : { cls:'badge-error',    label:'LỖI',     dot:'bg-red-500',    ico:'text-red-400'     },
  };
  const c = cfg[status]||cfg.error;
  badge.classList.add(c.cls);
  if (lbl)  lbl.textContent = c.label;
  if (dot)  dot.className   = `w-1.5 h-1.5 rounded-full ${c.dot} inline-block`;
  if (bico) bico.setAttribute('class', `w-4 h-4 ${c.ico}`);
}

/* 10. BATTERY BAR */
function updateBatteryBar(pct) {
  const bar = document.getElementById('battery-bar');
  if (!bar) return;
  bar.style.width = Math.min(100,Math.max(0,pct))+'%';
  bar.className   = 'progress-fill';
  if (pct < 20)      bar.classList.add('crit');
  else if (pct < 40) bar.classList.add('warn');
}

/* 11. NETWORK UI */
function updateNetworkUI(d) {
  const sc = (dotId, txtId, ok, label) => {
    const dot = document.getElementById(dotId), txt = document.getElementById(txtId);
    if (dot) dot.className = 'conn-dot '+(ok?'online':'offline');
    if (txt) { txt.textContent = label; txt.style.color = ok?'#4ade80':'#6b7280'; }
  };
  sc('ros-dot','ros-status',  d.ros_connected,  d.ros_connected  ? 'Kết nối':'Ngắt kết nối');
  sc('mqtt-dot','mqtt-status',d.mqtt_connected, d.mqtt_connected ? 'Online':'Offline');
  sc('wifi-dot', null,        d.wifi_connected, null);
  setText('wifi-rssi', (d.wifi_rssi||'-55')+' dBm');
  const ok   = d.ros_connected && d.mqtt_connected && d.wifi_connected;
  const ns   = document.getElementById('network-status');
  const ai   = document.getElementById('api-indicator');
  const at   = document.getElementById('api-status-text');
  if (ns) { ns.innerHTML = ok ? '<span class="conn-dot online inline-block mr-1.5"></span>Bình Thường' : '<span class="conn-dot warn inline-block mr-1.5"></span>Có vấn đề'; ns.style.color = ok?'#4ade80':'#fbbf24'; }
  if (ai) ai.className   = 'conn-dot '+(ok?'online':'warn');
  if (at) at.textContent = ok?'Live':'Cảnh báo';
}

/* 12. VALUE FLASH */
function flashValue(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('updated');
  setTimeout(()=>el.classList.remove('updated'),500);
}

/* 13. FETCH REALTIME DATA ─── CORE POLLING LOGIC
   ─────────────────────────────────────────────
   Gọi api_get_data.php mỗi 1 giây, parse JSON,
   map vào DOM bằng id tương ứng.
   JSON schema (api_get_data.php):
   {
     agv_status, agv_name, agv_speed, agv_uptime, agv_mission,
     battery_percent, battery_voltage, battery_current, battery_temp, battery_eta,
     odom_x, odom_y, odom_theta, vel_linear, vel_angular,
     ros_connected, mqtt_connected, wifi_connected, wifi_rssi
   }
──────────────────────────────────────────────── */
let updateCount = 0;
async function fetchRealtimeData() {
  try {
    const res = await fetch('api_get_data.php',{
      method:'GET',
      headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
      cache:'no-store'
    });
    if (!res.ok) throw new Error('HTTP '+res.status);
    const d = await res.json();

    if (d.agv_name)                { setText('agv-name', d.agv_name); }
    if (d.agv_speed !== undefined) { setText('agv-speed', parseFloat(d.agv_speed).toFixed(2)); flashValue('agv-speed'); }
    if (d.agv_uptime)              { setText('agv-uptime',  d.agv_uptime); }
    if (d.agv_mission)             { setText('agv-mission', d.agv_mission); }
    if (d.agv_status)              { applyStatusStyle(d.agv_status); }

    if (d.battery_percent !== undefined) {
      const pct = Math.round(d.battery_percent);
      setText('battery-percent', pct+'%'); updateBatteryBar(pct); flashValue('battery-percent');
    }
    if (d.battery_voltage !== undefined) setText('battery-voltage', parseFloat(d.battery_voltage).toFixed(1));
    if (d.battery_current !== undefined) setText('battery-current', parseFloat(d.battery_current).toFixed(1)+' A');
    if (d.battery_temp    !== undefined) setText('battery-temp',    parseFloat(d.battery_temp).toFixed(1)+'°C');
    if (d.battery_eta)                  setText('battery-eta', d.battery_eta);

    if (d.odom_x     !== undefined) { setText('odom-x',     parseFloat(d.odom_x).toFixed(3));         flashValue('odom-x');     }
    if (d.odom_y     !== undefined) { setText('odom-y',     parseFloat(d.odom_y).toFixed(3));         flashValue('odom-y');     }
    if (d.odom_theta !== undefined) { setText('odom-theta', parseFloat(d.odom_theta).toFixed(1)+'°'); flashValue('odom-theta'); }
    if (d.vel_linear  !== undefined) setText('vel-linear',  parseFloat(d.vel_linear).toFixed(2));
    if (d.vel_angular !== undefined) setText('vel-angular', parseFloat(d.vel_angular).toFixed(3));

    if (d.odom_x !== undefined) updateAgvOnMap(d.odom_x, d.odom_y, d.odom_theta||0);
    if (d.ros_connected !== undefined) updateNetworkUI(d);

    setText('update-counter', ++updateCount);
  } catch(err) {
    applyStatusStyle('error');
    const ai = document.getElementById('api-indicator');
    const at = document.getElementById('api-status-text');
    const ns = document.getElementById('network-status');
    if (ai) ai.className   = 'conn-dot offline';
    if (at) at.textContent = 'Offline';
    if (ns) { ns.innerHTML = '<span class="conn-dot offline inline-block mr-1.5"></span>Mất kết nối'; ns.style.color='#6b7280'; }
    console.warn('[AGV Monitor] fetch error:',err);
  }
}

/* 14. HELPER */
function setText(id,val) { const el=document.getElementById(id); if(el)el.textContent=val; }
</script>
</body>
</html>