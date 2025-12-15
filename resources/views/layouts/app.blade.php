<!DOCTYPE html>
<html lang="en" data-theme="light-green">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Employee Management') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ============================================
           THEME 1: LIGHT GREEN (Default)
           Fresh, clean, professional green palette
           ============================================ */
        [data-theme="light-green"] {
            --bg-body: #f8fdf9;
            --bg-nav: #f0fdf4;
            --bg-header: #d1e7dd;
            --bg-card: #ffffff;
            --bg-hover: #c3e6cb;
            --bg-alternate: #f0fdf4;
            --text-primary: #052c18;
            --text-secondary: #0f5132;
            --text-muted: #417a5c;
            --border-color: #badbcc;
            --accent: #198754;
            --accent-hover: #157347;
            --input-bg: #ffffff;
            --input-border: #badbcc;
            --btn-primary-bg: #198754;
            --btn-primary-text: #ffffff;
            --shadow: rgba(25, 135, 84, 0.1);
            --nav-active: #d1e7dd;
        }

        /* ============================================
           THEME 2: DARK GREEN
           Elegant dark mode with green accents
           ============================================ */
        [data-theme="dark-green"] {
            --bg-body: #0d1f17;
            --bg-nav: #142a1f;
            --bg-header: #1a3829;
            --bg-card: #1a3829;
            --bg-hover: #245238;
            --bg-alternate: #142a1f;
            --text-primary: #e8f5ec;
            --text-secondary: #a8d4b8;
            --text-muted: #6aaa8a;
            --border-color: #2d543e;
            --accent: #2dd36f;
            --accent-hover: #4ade80;
            --input-bg: #1a3829;
            --input-border: #2d543e;
            --btn-primary-bg: #2dd36f;
            --btn-primary-text: #0d1f17;
            --shadow: rgba(45, 211, 111, 0.15);
            --nav-active: #245238;
        }

        /* ============================================
           THEME 3: OCEAN BLUE
           Cool, calming blue palette
           ============================================ */
        [data-theme="ocean-blue"] {
            --bg-body: #f0f7ff;
            --bg-nav: #e8f1fb;
            --bg-header: #cfe2ff;
            --bg-card: #ffffff;
            --bg-hover: #b6d4fe;
            --bg-alternate: #e8f1fb;
            --text-primary: #052255;
            --text-secondary: #0a58ca;
            --text-muted: #4a7cc5;
            --border-color: #a3c4f3;
            --accent: #0d6efd;
            --accent-hover: #0b5ed7;
            --input-bg: #ffffff;
            --input-border: #a3c4f3;
            --btn-primary-bg: #0d6efd;
            --btn-primary-text: #ffffff;
            --shadow: rgba(13, 110, 253, 0.1);
            --nav-active: #cfe2ff;
        }

        /* ============================================
           BASE STYLES
           ============================================ */
        * { box-sizing: border-box; }
        
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .layout { 
            display: grid; 
            grid-template-columns: 260px 1fr; 
            min-height: 100vh; 
        }

        /* ============================================
           NAVIGATION
           ============================================ */
        nav {
            background: var(--bg-nav);
            padding: 18px 14px;
            border-right: 1px solid var(--border-color);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        
        .logo {
            display: flex; 
            align-items: center; 
            gap: 10px;
            font-weight: 700; 
            color: var(--text-primary); 
            margin-bottom: 24px; 
            text-decoration: none;
            font-size: 1.2rem;
        }
        
        .menu { 
            list-style: none; 
            padding: 0; 
            margin: 0; 
        }
        
        .menu li a {
            display: flex; 
            align-items: center; 
            gap: 10px;
            padding: 10px 12px; 
            margin-bottom: 6px;
            color: var(--text-primary); 
            text-decoration: none; 
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .menu li a:hover, .menu li a.active { 
            background: var(--nav-active); 
            color: var(--accent);
        }

        /* ============================================
           HEADER
           ============================================ */
        header {
            display: flex; 
            align-items: center; 
            justify-content: space-between;
            padding: 12px 22px; 
            color: var(--text-primary);
            background: var(--bg-header); 
            position: sticky; 
            top: 0; 
            z-index: 10;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .header-title {
            font-weight: 700; 
            font-size: 1.1rem;
        }

        .header-actions {
            display: flex !important;
            align-items: center;
            gap: 15px;
            visibility: visible !important;
        }

        /* ============================================
           THEME SWITCHER
           ============================================ */
        .theme-switcher {
            display: flex !important;
            align-items: center;
            gap: 8px;
            background: var(--bg-card);
            padding: 6px 12px;
            border-radius: 25px;
            border: 1px solid var(--border-color);
            box-shadow: 0 2px 8px var(--shadow);
        }

        .theme-btn {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-block;
            padding: 0;
            margin: 0;
        }

        .theme-btn:hover {
            transform: scale(1.1);
        }

        .theme-btn.active {
            border-color: var(--text-primary);
            box-shadow: 0 0 0 3px var(--shadow);
        }

        .theme-btn-green {
            background: linear-gradient(135deg, #198754, #d1e7dd);
        }

        .theme-btn-dark {
            background: linear-gradient(135deg, #0d1f17, #2dd36f);
        }

        .theme-btn-blue {
            background: linear-gradient(135deg, #0d6efd, #cfe2ff);
        }

        .theme-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-right: 5px;
        }

        .theme-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-right: 5px;
        }

        /* ============================================
           CONTENT AREA
           ============================================ */
        .content { 
            padding: 22px; 
        }
        
        .card {
            background: var(--bg-card); 
            border-radius: 12px; 
            padding: 18px;
            box-shadow: 0 4px 20px var(--shadow);
            border: 1px solid var(--border-color);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        /* ============================================
           FORM CONTROLS
           ============================================ */
        .form-control, .form-select {
            background-color: var(--input-bg);
            color: var(--text-primary);
            border-color: var(--input-border);
            transition: all 0.2s ease;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: var(--input-bg);
            color: var(--text-primary);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem var(--shadow);
        }

        /* ============================================
           BUTTONS
           ============================================ */
        .btn-primary {
            background-color: var(--btn-primary-bg);
            border-color: var(--btn-primary-bg);
            color: var(--btn-primary-text);
        }

        .btn-primary:hover {
            background-color: var(--accent-hover);
            border-color: var(--accent-hover);
        }

        /* ============================================
           TABLES
           ============================================ */
        .table {
            color: var(--text-primary);
        }

        .table thead th {
            background: var(--bg-header);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .table tbody td {
            border-color: var(--border-color);
        }

        .table tbody tr:nth-child(even) {
            background: var(--bg-alternate);
        }

        .table tbody tr:hover {
            background: var(--bg-hover);
        }

        /* ============================================
           LINKS
           ============================================ */
        a {
            color: var(--accent);
        }

        a:hover {
            color: var(--accent-hover);
        }

        /* ============================================
           USER INFO
           ============================================ */
        .user-info {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .btn-logout {
            background: var(--bg-hover);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
        }

        .btn-logout:hover {
            background: var(--accent);
            color: var(--btn-primary-text);
            border-color: var(--accent);
        }

        /* ============================================
           ALERTS
           ============================================ */
        .alert-success {
            background: var(--bg-alternate);
            color: var(--text-secondary);
            border-color: var(--border-color);
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
    </style>
    @stack('head')
</head>
<body>
<div class="layout" @if(request('popup')) style="display:block; grid-template-columns:none;" @endif>
    @if(!request('popup'))
    <nav>
        <a class="logo" href="{{ url('/') }}">
            <i class="bi bi-building" style="font-size: 1.5rem; color: var(--accent);"></i>
            <span>BaraBD</span>
        </a>
        <ul class="menu">
            <li><a href="{{ route('attendance.index') }}"><i class="bi bi-calendar-check"></i> Attendance Dashboard</a></li>
            <li><a href="{{ route('attendance-records.index') }}"><i class="bi bi-list-check"></i> Attendance Records</a></li>
            <li><a href="{{ route('employees.index') }}"><i class="bi bi-people"></i> Employees</a></li>
            <li><a href="{{ route('livefeed.index') }}"><i class="bi bi-broadcast"></i> Live Feed</a></li>
            <li><a href="{{ route('shifts.index') }}"><i class="bi bi-clock"></i> Shifts</a></li>
            <li><a href="{{ route('moderator.index') }}"><i class="bi bi-tags"></i> Moderator Labels</a></li>
            <li><a href="{{ route('salary.defaults') }}"><i class="bi bi-sliders"></i> Salary Defaults</a></li>
            <li><a href="{{ route('salary.index') }}"><i class="bi bi-cash-stack"></i> Salary Statistics</a></li>
            <li><a href="{{ route('holidays.index') }}"><i class="bi bi-calendar-heart"></i> Holidays</a></li>
            <li><a href="{{ route('reports.index') }}"><i class="bi bi-file-earmark-text"></i> Reports</a></li>
            <li><a href="{{ route('settings.voice_message') }}"><i class="bi bi-volume-up"></i> Voice & Message</a></li>
            <li><a href="{{ route('settings.context') }}"><i class="bi bi-gear"></i> Context Settings</a></li>
            <li><a href="{{ route('settings.company') }}"><i class="bi bi-building-gear"></i> Company Info</a></li>
            <li><a href="{{ route('settings.integration') }}"><i class="bi bi-plug"></i> Integration</a></li>
            @if(session('user_role') === 'super_admin')
            <li style="border-top:1px solid var(--border-color); margin-top:10px; padding-top:10px;">
                <a href="{{ route('companies.index') }}" class="{{ request()->routeIs('companies.*') ? 'active' : '' }}">
                    <i class="bi bi-buildings"></i> Companies (Admin)
                </a>
            </li>
            @endif
        </ul>
    </nav>
    @endif
    <div>
        @if(!request('popup'))
        <header>
            <div class="header-title">
                Employee Management System
                @if(session('user_role') === 'super_admin' && session('selected_organization_name'))
                    <span style="font-size:0.8rem; font-weight:400; margin-left:10px; padding:4px 10px; background:var(--accent); color:white; border-radius:4px;">
                        {{ session('selected_organization_name') }}
                    </span>
                @elseif(session('organization_name'))
                    <span style="font-size:0.8rem; font-weight:400; margin-left:10px; padding:4px 10px; background:var(--accent); color:white; border-radius:4px;">
                        {{ session('organization_name') }}
                    </span>
                @endif
            </div>
            <div class="header-actions">
                @if(session('user_role') === 'super_admin' && count(session('organizations', [])) > 0)
                <!-- Organization Selector for Super Admin -->
                <form action="{{ route('switch.organization') }}" method="POST" style="margin:0; display:flex; align-items:center; gap:8px;">
                    @csrf
                    <select name="organization_id" onchange="this.form.submit()" style="padding:5px 10px; border-radius:6px; border:1px solid var(--border-color); background:var(--bg-card); color:var(--text-primary); font-size:12px;">
                        <option value="all" {{ !session('selected_organization_id') ? 'selected' : '' }}>All Organizations</option>
                        @foreach(session('organizations', []) as $org)
                            <option value="{{ $org['id'] }}" {{ session('selected_organization_id') == $org['id'] ? 'selected' : '' }}>
                                {{ $org['name'] }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endif

                <!-- Theme Switcher -->
                <div class="theme-switcher">
                    <span class="theme-label">Theme:</span>
                    <button class="theme-btn theme-btn-green" data-theme="light-green" title="Light Green"></button>
                    <button class="theme-btn theme-btn-dark" data-theme="dark-green" title="Dark Green"></button>
                    <button class="theme-btn theme-btn-blue" data-theme="ocean-blue" title="Ocean Blue"></button>
                </div>
                
                @if(session('admin_user'))
                    <span class="user-info">
                        {{ session('admin_user') }}
                        <small style="opacity:0.7;">({{ session('user_role', 'admin') }})</small>
                    </span>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn-logout">Logout</button>
                    </form>
                @endif
            </div>
        </header>
        @endif
        <main class="content">
            @yield('content')
        </main>
    </div>
</div>

<script>
    // Theme Switcher Logic
    (function() {
        const THEME_KEY = 'barabd_theme';
        const DEFAULT_THEME = 'light-green';
        
        // Load saved theme or default
        function loadTheme() {
            const savedTheme = localStorage.getItem(THEME_KEY) || DEFAULT_THEME;
            applyTheme(savedTheme);
        }
        
        // Apply theme to document
        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            
            // Update active state on buttons
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.theme === theme) {
                    btn.classList.add('active');
                }
            });
            
            // Save to localStorage
            localStorage.setItem(THEME_KEY, theme);
        }
        
        // Initialize on page load
        loadTheme();
        
        // Add click handlers to theme buttons
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    applyTheme(this.dataset.theme);
                });
            });
            
            // Re-apply active state after DOM is ready
            const currentTheme = localStorage.getItem(THEME_KEY) || DEFAULT_THEME;
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.theme === currentTheme) {
                    btn.classList.add('active');
                }
            });
        });
    })();
</script>

@stack('scripts')
</body>
</html>
