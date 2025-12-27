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
    <!-- Select2 for searchable dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
           ALERTS - Fixed visibility
           ============================================ */
        .alert-success {
            background: var(--bg-alternate);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-color: #ffeeba;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }

        /* ============================================
           BADGES - Ensure readable text
           ============================================ */
        .badge {
            font-weight: 600;
        }

        .badge.bg-secondary {
            background-color: var(--text-muted) !important;
            color: #ffffff !important;
        }

        .badge.bg-success {
            background-color: #198754 !important;
            color: #ffffff !important;
        }

        .badge.bg-danger {
            background-color: #dc3545 !important;
            color: #ffffff !important;
        }

        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #212529 !important;
        }

        .badge.bg-info {
            background-color: #0dcaf0 !important;
            color: #212529 !important;
        }

        .badge.bg-primary {
            background-color: var(--accent) !important;
            color: var(--btn-primary-text) !important;
        }

        /* ============================================
           TEXT HELPERS - Improved visibility
           ============================================ */
        .text-muted {
            color: var(--text-muted) !important;
        }

        .text-secondary {
            color: var(--text-secondary) !important;
        }

        small, .small {
            color: var(--text-muted);
        }

        /* Form labels visibility */
        .form-label, label {
            color: var(--text-primary);
            font-weight: 500;
        }

        /* ============================================
           DARK THEME - Extra visibility fixes
           ============================================ */
        [data-theme="dark-green"] .text-muted,
        [data-theme="dark-green"] small,
        [data-theme="dark-green"] .small {
            color: #8ec5a4 !important;
        }

        [data-theme="dark-green"] .form-text,
        [data-theme="dark-green"] .text-secondary {
            color: #a8d4b8 !important;
        }

        [data-theme="dark-green"] .alert-success {
            background: #1a3829;
            color: #a8d4b8;
            border-color: #2d543e;
        }

        [data-theme="dark-green"] .card-header {
            color: var(--text-primary);
        }

        [data-theme="dark-green"] .list-group-item {
            background: var(--bg-card);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        [data-theme="dark-green"] .progress-bar {
            color: #ffffff;
        }

        [data-theme="dark-green"] .table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text-primary);
        }

        /* Progress bars - ensure text is visible */
        .progress-bar {
            font-weight: 600;
            font-size: 12px;
        }

        /* Card headers */
        .card-header {
            background: var(--bg-header);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        /* Select2 dropdown styling - ensure visible text */
        .select2-container--default .select2-selection--single {
            background: #ffffff !important;
            border: 1px solid #ced4da !important;
            color: #333333 !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #333333 !important;
        }

        .select2-container--default .select2-results__option {
            color: #333333 !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            color: #333333 !important;
            background: #ffffff !important;
        }

        /* Fix red-bordered validation inputs text color */
        input[style*="border"][style*="red"],
        input[style*="dc3545"],
        .is-invalid,
        .has-error input {
            color: #333333 !important;
            background: #ffffff !important;
        }

        /* All select elements should have visible text */
        select {
            color: #333333 !important;
            background: #ffffff !important;
        }

        select option {
            color: #333333 !important;
            background: #ffffff !important;
        }

        /* Dropdowns */
        .dropdown-menu {
            background: var(--bg-card);
            border-color: var(--border-color);
        }

        .dropdown-item {
            color: var(--text-primary);
        }

        .dropdown-item:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        /* Pagination */
        .page-link {
            background: var(--bg-card);
            color: var(--text-primary);
            border-color: var(--border-color);
        }

        .page-link:hover {
            background: var(--bg-hover);
            color: var(--accent);
        }

        .page-item.active .page-link {
            background: var(--accent);
            border-color: var(--accent);
            color: var(--btn-primary-text);
        }

        .page-item.disabled .page-link {
            background: var(--bg-alternate);
            color: var(--text-muted);
        }
        .unified-action-bar {
            background: var(--bg-header);
            border: 1px solid var(--border-color);
            padding: 12px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 25px;
            margin: 20px 0;
            box-shadow: 0 4px 15px var(--shadow);
            flex-wrap: wrap;
            transition: all 0.3s ease;
        }

        .action-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .action-label {
            font-weight: 700;
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .action-select, .action-input-file {
            padding: 7px 14px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--input-bg);
            color: var(--text-primary);
            font-size: 13px;
            outline: none;
            transition: all 0.2s;
        }

        .action-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px var(--shadow);
        }

        .btn-action-primary {
            background: var(--btn-primary-bg);
            color: var(--btn-primary-text);
            padding: 9px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-action-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .btn-action-primary:active {
            transform: translateY(0);
        }

        .btn-action-secondary {
            background: var(--bg-card);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 9px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-action-secondary:hover {
            background: var(--bg-hover);
            border-color: var(--accent);
            color: var(--accent);
        }

        .divider-vertical {
            width: 1px;
            height: 30px;
            background: var(--border-color);
            opacity: 0.6;
        }

        @media (max-width: 768px) {
            .unified-action-bar {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            .divider-vertical {
                display: none;
            }
            .action-group {
                justify-content: space-between;
            }
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
            <li><a href="{{ route('analytics.index') }}" class="{{ request()->routeIs('analytics.*') ? 'active' : '' }}"><i class="bi bi-graph-up"></i> Analytics Dashboard</a></li>
            <li><a href="{{ route('attendance.index') }}" class="{{ request()->routeIs('attendance.*') ? 'active' : '' }}"><i class="bi bi-calendar-check"></i> Attendance Dashboard</a></li>
            <li><a href="{{ route('attendance-records.index') }}" class="{{ request()->routeIs('attendance-records.*') ? 'active' : '' }}"><i class="bi bi-list-check"></i> Attendance Records</a></li>
            <li><a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}"><i class="bi bi-people"></i> Employees</a></li>
            <li><a href="{{ route('livefeed.index') }}" class="{{ request()->routeIs('livefeed.*') ? 'active' : '' }}"><i class="bi bi-broadcast"></i> Live Feed</a></li>
            @if(session('user_role') !== 'super_admin')
            <li><a href="{{ route('shifts.index') }}" class="{{ request()->routeIs('shifts.*') ? 'active' : '' }}"><i class="bi bi-clock"></i> Shifts</a></li>
            <li><a href="{{ route('salary.defaults') }}" class="{{ request()->routeIs('salary.defaults') ? 'active' : '' }}"><i class="bi bi-sliders"></i> Salary Defaults</a></li>
            <li><a href="{{ route('salary.index') }}" class="{{ request()->routeIs('salary.*') && !request()->routeIs('salary.defaults') ? 'active' : '' }}"><i class="bi bi-cash-stack"></i> Salary Statistics</a></li>
            <li><a href="{{ route('holidays.index') }}" class="{{ request()->routeIs('holidays.*') ? 'active' : '' }}"><i class="bi bi-calendar-heart"></i> Holidays</a></li>
            @endif
            <li><a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-text"></i> Reports</a></li>
            @if(session('user_role') !== 'super_admin')
            <li><a href="{{ route('settings.voice_message') }}" class="{{ request()->routeIs('settings.voice_message') ? 'active' : '' }}"><i class="bi bi-volume-up"></i> Voice & Message</a></li>
            <li><a href="{{ route('settings.context') }}" class="{{ request()->routeIs('settings.context') ? 'active' : '' }}"><i class="bi bi-gear"></i> Context Settings</a></li>
            <li><a href="{{ route('settings.company') }}" class="{{ request()->routeIs('settings.company') ? 'active' : '' }}"><i class="bi bi-building-gear"></i> My Organization</a></li>
            @endif
            @if(in_array(session('user_role'), ['super_admin', 'org_main_admin', 'org_admin']))
            <li><a href="{{ route('org-users.index') }}" class="{{ request()->routeIs('org-users.*') ? 'active' : '' }}"><i class="bi bi-person-gear"></i> Manage Users</a></li>
            @endif
            @if(session('user_role') === 'super_admin')
            <li><a href="{{ route('settings.integration') }}" class="{{ request()->routeIs('settings.integration') ? 'active' : '' }}"><i class="bi bi-plug"></i> Integration</a></li>
            @endif
            <li><a href="{{ route('audit.index') }}" class="{{ request()->routeIs('audit.*') ? 'active' : '' }}"><i class="bi bi-clock-history"></i> Audit Logs</a></li>
            <li><a href="{{ route('export.index') }}" class="{{ request()->routeIs('export.*') ? 'active' : '' }}"><i class="bi bi-arrow-down-up"></i> Export / Import</a></li>
            @if(session('user_role') === 'super_admin')
            <li style="border-top:1px solid var(--border-color); margin-top:10px; padding-top:10px;">
                <a href="{{ route('organizations.index') }}" class="{{ request()->routeIs('organizations.*') ? 'active' : '' }}">
                    <i class="bi bi-buildings"></i> Organizations
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
                    <span style="font-size:0.8rem; font-weight:400; margin-left:10px; padding:4px 10px; background:var(--accent); color:white; border-radius:4px; display:inline-flex; align-items:center; gap:6px;">
                        @if(session('selected_organization_logo'))
                            <img src="/media/{{ session('selected_organization_logo') }}" alt="Logo" style="height:20px; width:20px; border-radius:3px; object-fit:cover; background:white;">
                        @endif
                        {{ session('selected_organization_name') }}
                    </span>
                @elseif(session('organization_name'))
                    <span style="font-size:0.8rem; font-weight:400; margin-left:10px; padding:4px 10px; background:var(--accent); color:white; border-radius:4px; display:inline-flex; align-items:center; gap:6px;">
                        @if(session('organization_logo'))
                            <img src="/media/{{ session('organization_logo') }}" alt="Logo" style="height:20px; width:20px; border-radius:3px; object-fit:cover; background:white;">
                        @endif
                        {{ session('organization_name') }}
                    </span>
                @endif
            </div>
            <div class="header-actions">
                @if(session('user_role') === 'super_admin' && count(session('organizations', [])) > 0)
                <!-- Organization Selector for Super Admin -->
                <form action="{{ route('switch.organization') }}" method="POST" id="org-switch-form" style="margin:0; display:flex; align-items:center; gap:8px;">
                    @csrf
                    <select name="organization_id" id="org-selector" style="width:220px;" onchange="this.form.submit()">
                        <option value="all" {{ !session('selected_organization_id') ? 'selected' : '' }}>All Organizations</option>
                        @foreach(session('organizations', []) as $org)
                            <option value="{{ $org['id'] }}" {{ session('selected_organization_id') == $org['id'] ? 'selected' : '' }}>
                                {{ $org['name'] }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endif

                <!-- Language Switcher -->
                <div style="position:relative;">
                    <select id="lang-selector" style="padding:6px 12px; border-radius:6px; border:1px solid var(--border-color); background:var(--bg-card); color:var(--text-primary); font-size:13px; cursor:pointer;" onchange="window.location.href='{{ url('language') }}/' + this.value">
                        <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>🇺🇸 English</option>
                        <option value="bn" {{ app()->getLocale() == 'bn' ? 'selected' : '' }}>🇧🇩 বাংলা</option>
                        <option value="hi" {{ app()->getLocale() == 'hi' ? 'selected' : '' }}>🇮🇳 हिंदी</option>
                        <option value="es" {{ app()->getLocale() == 'es' ? 'selected' : '' }}>🇪🇸 Español</option>
                    </select>
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
</div>

@yield('modal')

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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 on organization dropdown
    if ($('#org-selector').length) {
        $('#org-selector').select2({
            placeholder: 'Search organization...',
            allowClear: false,
            minimumResultsForSearch: 0, // Always show search
            width: '220px'
        }).on('change', function() {
            $('#org-switch-form').submit();
        });
    }
});
</script>
<style>
/* Select2 Theme Styling */
.select2-container--default .select2-selection--single {
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    height: 32px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: var(--text-primary);
    line-height: 30px;
    font-size: 12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 30px;
}
.select2-dropdown {
    background-color: var(--bg-card);
    border-color: var(--border-color);
}
.select2-container--default .select2-search--dropdown .select2-search__field {
    background-color: var(--input-bg);
    color: var(--text-primary);
    border-color: var(--input-border);
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: var(--accent);
    color: var(--btn-primary-text);
}
.select2-container--default .select2-results__option[aria-selected=true] {
    background-color: var(--bg-hover);
    color: var(--text-primary);
}
.select2-results__option {
    color: var(--text-primary);
    font-size: 12px;
}
</style>
</body>
</html>
