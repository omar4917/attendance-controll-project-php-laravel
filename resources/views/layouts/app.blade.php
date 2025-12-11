<!DOCTYPE html>
<html lang="en">
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
        :root {
            /* Light Theme (Fresh Light Green) - Default */
            --bg-primary: #ffffff;
            --bg-secondary: #c3e6cb; /* Hover green */
            --bg-tertiary: #ffffff;
            --bg-quaternary: #f0fdf4; /* Very pale green for alternating rows */
            --bg-header: #d1e7dd; /* Fresh light green header */
            --text-primary: #052c18; /* Darker green/black for better visibility */
            --text-secondary: #0f5132;
            --border-color: #badbcc; /* Soft green border */
            --border-header: #a3cfbb;
            --input-bg: #ffffff;
            --input-text: #212529;
            --input-border: #badbcc;
            --btn-primary: #198754;
            --btn-text: #fff;
            
            /* Layout variables mapped to theme */
            --nav-bg: #f8f9fa;
            --nav-text: #212529;
            --nav-hover: #e9ecef;
            --primary: #198754;
            --card: #ffffff;
            --bg: #f4f6f9;
            --body-bg: #f4f6f9;
            --body-text: #212529;
            --header-bg: #d1e7dd;
            --header-text: #052c18;
        }

        .light-theme {
            --body-bg: #ffffff;
            --body-text: #212529;
            --header-bg: #f8f9fa;
            --header-text: #212529;
            --input-bg: #ffffff;
            --input-text: #212529;
            --input-border: #ced4da;
            --nav-bg: #f8f9fa;
            --nav-text: #212529;
            --nav-hover: #e9ecef;
            --bg: #ffffff;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--body-bg);
            color: var(--body-text);
        }
        .layout { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
        nav {
            background: var(--nav-bg);
            padding: 18px 14px;
            border-right: 1px solid #d5e5da;
        }
        .logo {
            display: flex; align-items: center; gap: 10px;
            font-weight: 700; color: var(--nav-text); margin-bottom: 24px; text-decoration: none;
            font-size: 1.2rem;
        }
        .menu { list-style: none; padding: 0; margin: 0; }
        .menu li a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; margin-bottom: 6px;
            color: var(--nav-text); text-decoration: none; border-radius: 10px;
            font-weight: 500;
        }
        .menu li a:hover, .menu li a.active { 
            background: var(--nav-hover); 
            color: #0b7d5c;
        }
        header {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 22px; color: var(--header-text);
            background: var(--header-bg); backdrop-filter: blur(8px);
            position: sticky; top: 0; z-index: 10;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .light-theme header {
            border-bottom: 1px solid #dee2e6;
        }
        .content { padding: 22px; }
        .card {
            background: var(--card); border-radius: 16px; padding: 18px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            border: none;
        }
        
        /* Form Controls Override */
        .form-control, .form-select {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: var(--input-border);
        }
        .form-control:focus, .form-select:focus {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(11, 125, 92, 0.25);
        }
    </style>
    @stack('head')
</head>
<body>
<div class="layout" @if(request('popup')) style="display:block; grid-template-columns:none;" @endif>
    @if(!request('popup'))
    <nav>
        <a class="logo" href="{{ url('/') }}">
            <span>PHP BaraBD</span>
        </a>
        <ul class="menu">
            <li><a href="{{ route('attendance.index') }}">Attendance Dashboard</a></li>
            <li><a href="{{ route('attendance-records.index') }}">Attendance Records</a></li>
            <li><a href="{{ route('employees.index') }}">Employees</a></li>
            <li><a href="{{ route('livefeed.index') }}">Live Feed</a></li>
            <li><a href="{{ route('shifts.index') }}">Shifts</a></li>
            <li><a href="{{ route('moderator.index') }}">Moderator Labels</a></li>
            <li><a href="{{ route('salary.defaults') }}">Salary Defaults</a></li>
            <li><a href="{{ route('salary.index') }}">Salary Statistics</a></li>
            <li><a href="{{ route('holidays.index') }}">Holidays</a></li>
            <li><a href="{{ route('reports.index') }}">Reports</a></li>
            <li><a href="{{ route('settings.voice_message') }}">Voice & Message Settings</a></li>
            <li><a href="{{ route('settings.context') }}">Context Settings</a></li>
            <li><a href="{{ route('settings.company') }}">Company Info</a></li>
            <li><a href="{{ route('settings.integration') }}">Integration Settings</a></li>
        </ul>
    </nav>
    @endif
    <div>
        @if(!request('popup'))
        <header>
            <div>
                <div style="font-weight:700; font-size: 1.1rem;">Employee Management</div>
            </div>
            <div class="d-flex align-items-center gap-3">
                @if(session('admin_user'))
                    <span style="font-size:13px; color:#fff;">{{ session('admin_user') }}</span>
                    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="btn btn-sm" style="background:rgba(255,255,255,0.15); color:#fff; border:none;">Logout</button>
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
<!-- Bootstrap JS (Removed) -->
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> -->
@stack('scripts')
</body>
</html>
