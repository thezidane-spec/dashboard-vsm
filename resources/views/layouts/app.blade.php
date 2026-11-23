<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'VSM Dashboard - PT Mudalaya Energy Indonesia')</title>
    
    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --mei-green-brand: #059669;
            --mei-green-dark: #047857;
            --mei-green-subtle: #ecfdf5;
            --mei-sidebar-bg: #064e3b;
            --mei-bg-light: #f8fafc;
            --sidebar-width: 260px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--mei-bg-light);
            overflow-x: hidden;
            margin: 0;
        }

        /* Sidebar Wrapper */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--mei-sidebar-bg);
            color: #ffffff;
            transition: all 0.3s ease;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 20px 24px;
            background-color: rgba(0, 0, 0, 0.15);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Logo + nama PT bergantian (Mudalaya <-> Mutiara) supaya header sidebar tidak memakan banyak ruang */
        .brand-rotator { position: relative; width: 100%; height: 40px; }
        .brand-slide {
            position: absolute; inset: 0;
            display: flex; align-items: center; gap: 10px;
            opacity: 0;
            animation: brand-swap 8s ease-in-out infinite;
        }
        .brand-slide-b { animation-delay: 4s; }
        @keyframes brand-swap {
            0%        { opacity: 0; transform: translateY(4px); }
            8%, 42%   { opacity: 1; transform: translateY(0); }
            50%, 100% { opacity: 0; transform: translateY(-4px); }
        }
        @media (prefers-reduced-motion: reduce) {
            .brand-slide { animation: none; }
            .brand-slide-a { opacity: 1; }
            .brand-slide-b { display: none; }
        }

        .sidebar-menu {
            padding: 16px 12px;
            flex-grow: 1;
            overflow-y: auto;
        }

        .menu-header {
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #a7f3d0;
            padding: 12px 12px 6px;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            color: #d1fae5;
            font-size: 0.88rem;
            font-weight: 600;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.2s ease;
            margin-bottom: 4px;
        }

        .nav-link-custom:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .nav-link-custom.active {
            background-color: var(--mei-green-brand);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
        }

        /* Main Content Wrapper */
        #main-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        /* Topbar Header */
        .topbar {
            height: 70px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .content-body {
            padding: 28px;
            flex-grow: 1;
        }
    </style>

    @stack('styles')
    @include('partials.theme')
    @include('partials.responsive')
</head>
<body>

    <!-- SIDEBAR NAVIGATION -->
    <aside id="sidebar">
        <!-- Brand / Logo Header -->
        <div class="sidebar-brand">
            <div class="brand-rotator">
                <!-- Slide A: PT MUDALAYA (logo hijau) -->
                <div class="brand-slide brand-slide-a">
                    <img src="{{ asset('images/mgi-logo.png') }}" alt="Logo PT Mudalaya" style="width: 36px; height: 36px; border-radius: 8px; object-fit: contain;">
                    <div>
                        <h6 class="fw-bold m-0 text-white" style="font-size: 0.95rem;">PT MUDALAYA</h6>
                        <small class="text-emerald-200 opacity-75 d-block" style="font-size: 0.72rem; color: #a7f3d0;">VSM Dashboard System</small>
                    </div>
                </div>
                <!-- Slide B: PT MUTIARA (logo oranye) -->
                <div class="brand-slide brand-slide-b">
                    <img src="{{ asset('images/mei-logo.png') }}" alt="Logo PT Mutiara" style="width: 36px; height: 36px; border-radius: 8px; object-fit: contain;">
                    <div>
                        <h6 class="fw-bold m-0 text-white" style="font-size: 0.95rem;">PT MUTIARA</h6>
                        <small class="text-emerald-200 opacity-75 d-block" style="font-size: 0.72rem; color: #a7f3d0;">VSM Dashboard System</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Links -->
        <div class="sidebar-menu">
            <!-- 1. MAIN MONITORING -->
            <div class="menu-header">1. MAIN MONITORING</div>
            
            <a href="{{ route('dashboard') }}" class="nav-link-custom {{ request()->routeIs('dashboard') && !request('status') && !request('department') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>All Projects</span>
            </a>

            <a href="{{ route('dashboard', ['status' => 'ON_TRACK']) }}" class="nav-link-custom {{ request('status') === 'ON_TRACK' ? 'active' : '' }}">
                <i class="bi bi-check-circle-fill text-success"></i>
                <span>On Track Projects</span>
            </a>

            <a href="{{ route('dashboard', ['status' => 'LEADING']) }}" class="nav-link-custom {{ request('status') === 'LEADING' ? 'active' : '' }}">
                <i class="bi bi-arrow-up-circle-fill" style="color: #1e3a8a;"></i>
                <span>Leading Projects</span>
            </a>

            <a href="{{ route('dashboard', ['status' => 'AT_RISK']) }}" class="nav-link-custom {{ request('status') === 'AT_RISK' ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                <span>At Risk Projects</span>
            </a>

            <a href="{{ route('dashboard', ['status' => 'DELAYED']) }}" class="nav-link-custom {{ request('status') === 'DELAYED' ? 'active' : '' }}">
                <i class="bi bi-x-circle-fill text-danger"></i>
                <span>Delayed Projects</span>
            </a>

            <a href="{{ route('dashboard', ['status' => 'CLOSED_LATE']) }}" class="nav-link-custom {{ request('status') === 'CLOSED_LATE' ? 'active' : '' }}">
                <i class="bi bi-hourglass-bottom" style="color: #ea580c;"></i>
                <span>Close Terlambat</span>
            </a>

            <!-- 2. DEPARTMENT WORKFLOW -->
            <div class="menu-header mt-3">2. DEPARTMENT WORKFLOW</div>

            <a href="{{ route('dashboard', ['department' => 'engineering']) }}" class="nav-link-custom {{ request('department') === 'engineering' ? 'active' : '' }}">
                <i class="bi bi-gear-wide-connected"></i>
                <span>Engineering</span>
            </a>

            <a href="{{ route('dashboard', ['department' => 'procurement']) }}" class="nav-link-custom {{ request('department') === 'procurement' ? 'active' : '' }}">
                <i class="bi bi-cart-check-fill"></i>
                <span>Procurement</span>
            </a>

            <a href="{{ route('dashboard', ['department' => 'ppic']) }}" class="nav-link-custom {{ request('department') === 'ppic' ? 'active' : '' }}">
                <i class="bi bi-calendar3"></i>
                <span>PPIC</span>
            </a>

            <a href="{{ route('dashboard', ['department' => 'production']) }}" class="nav-link-custom {{ request('department') === 'production' ? 'active' : '' }}">
                <i class="bi bi-tools"></i>
                <span>Production</span>
            </a>

            <a href="{{ route('dashboard', ['department' => 'qc_qa']) }}" class="nav-link-custom {{ request('department') === 'qc_qa' ? 'active' : '' }}">
                <i class="bi bi-shield-check"></i>
                <span>Finish Process</span>
            </a>

            <a href="{{ route('dashboard', ['department' => 'qaqc']) }}" class="nav-link-custom {{ request('department') === 'qaqc' ? 'active' : '' }}">
                <i class="bi bi-patch-check-fill"></i>
                <span>QA/QC</span>
            </a>

            <!-- 3. REPORTS & ANALYTICS -->
            <div class="menu-header mt-3">3. REPORTS & ANALYTICS</div>

            <a href="{{ route('pdca') }}" class="nav-link-custom {{ request()->routeIs('pdca') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>PDCA Control Center</span>
            </a>
            <a href="{{ route('reports.index') }}" class="nav-link-custom {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-bar-graph-fill"></i>
                <span>Summary Report</span>
            </a>
            <a href="{{ route('radar.index') }}" class="nav-link-custom {{ request()->routeIs('radar.*') ? 'active' : '' }}">
                <i class="bi bi-table"></i>
                <span>Material Radar List</span>
            </a>

            <!-- 4. MANAGEMENT (KHUSUS ADMIN / PPIC) -->
            @if(in_array(auth()->user()->role ?? '', ['admin', 'ppic']))
                <div class="menu-header mt-3">4. MANAGEMENT</div>

                <a href="{{ route('projects.create') }}" class="nav-link-custom {{ request()->routeIs('projects.create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Tambah Proyek Baru</span>
                </a>
            @endif
        </div>

        <!-- User Profile Bottom Sidebar -->
        <div class="p-3 bg-black bg-opacity-20 border-top border-white border-opacity-10 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="bg-emerald-600 text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px; background-color: var(--mei-green-brand);">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="overflow-hidden" style="max-width: 130px;">
                    <div class="fw-bold text-truncate small">{{ auth()->user()->name ?? 'Admin VSM' }}</div>
                    <small class="text-emerald-300 d-block text-truncate" style="font-size: 0.7rem; color: #a7f3d0;">{{ auth()->user()->email ?? 'admin@mudalaya.co.id' }}</small>
                </div>
            </div>

            <!-- Form Logout -->
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-link text-white-50 p-1 text-hover-white" title="Keluar">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                </button>
            </form>
        </div>
    </aside>

    <div class="sidebar-backdrop"></div>

    <!-- MAIN CONTENT AREA -->
    <div id="main-wrapper">
        <!-- TOPBAR -->
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="mobile-menu-btn" data-sidebar-toggle aria-label="Buka menu"><i class="bi bi-list"></i></button>
                <h5 class="fw-bold m-0 text-dark">@yield('page_title', 'Visual Management Dashboard')</h5>
            </div>
            
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="theme-toggle" aria-label="Ganti tema" title="Tema gelap"><i class="bi bi-moon-stars-fill"></i></button>
                <span class="badge bg-success-subtle text-success border border-success fw-bold px-3 py-2 rounded-pill">
                    <i class="bi bi-shield-check me-1"></i> System Active
                </span>
            </div>
        </header>

        <!-- MAIN BODY SECTION -->
        <main class="content-body">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>