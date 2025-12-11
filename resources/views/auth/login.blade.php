<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Employee Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --bg-primary: #ffffff;
            --bg-secondary: #c3e6cb;
            --bg-header: #d1e7dd;
            --text-primary: #052c18;
            --text-secondary: #0f5132;
            --border-color: #badbcc;
            --btn-primary: #198754;
            --input-bg: #ffffff;
            --input-border: #badbcc;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #d1e7dd 0%, #f0fdf4 50%, #c3e6cb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: var(--bg-primary);
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }

        .login-header {
            background: var(--btn-primary);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .login-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-group .input-wrapper {
            position: relative;
        }

        .form-group .input-wrapper i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
            font-size: 18px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 12px 12px 42px;
            border: 2px solid var(--input-border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: var(--input-bg);
            color: var(--text-primary);
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--btn-primary);
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.15);
        }

        .form-group input::placeholder {
            color: #999;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            border: 1px solid #f5c6cb;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message i {
            font-size: 18px;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: var(--btn-primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .btn-login:hover {
            background: #157347;
        }

        .btn-login:active {
            transform: scale(0.98);
        }

        .login-footer {
            text-align: center;
            padding: 20px 30px 30px;
            font-size: 12px;
            color: var(--text-secondary);
        }

        .login-footer a {
            color: var(--btn-primary);
            text-decoration: none;
            font-weight: 600;
        }

        /* Decorative elements */
        .decoration {
            position: fixed;
            border-radius: 50%;
            background: rgba(25, 135, 84, 0.1);
            z-index: -1;
        }
        .decoration.d1 { width: 300px; height: 300px; top: -100px; left: -100px; }
        .decoration.d2 { width: 200px; height: 200px; bottom: -50px; right: -50px; }
        .decoration.d3 { width: 150px; height: 150px; top: 50%; right: 10%; }
    </style>
</head>
<body>
    <div class="decoration d1"></div>
    <div class="decoration d2"></div>
    <div class="decoration d3"></div>

    <div class="login-container">
        <div class="login-header">
            <h1><i class="bi bi-person-badge"></i> Employee Management</h1>
            <p>Sign in with your credentials</p>
        </div>

        <div class="login-body">
            @if($errors->has('login'))
                <div class="error-message">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    {{ $errors->first('login') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}">
                @csrf
                
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person-fill"></i>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            placeholder="Enter username or email"
                            value="{{ old('username') }}"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="bi bi-lock-fill"></i>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter your password"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>
        </div>

        <div class="login-footer">
            Use your local account or Django admin credentials.
        </div>
    </div>
</body>
</html>
