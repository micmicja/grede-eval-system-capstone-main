<x-layouts.app title="Login | Counseling System">
    {{-- GOOGLE FONTS & ICONS --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet" />

    <style>
        body {
            background: linear-gradient(135deg, #f5f7fb 0%, #e4edff 100%);
            font-family: 'Inter', sans-serif !important;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
            padding: 50px 40px;
            transition: all 0.3s ease;
        }

        /* LOGO STYLING */
        .brand-wrapper {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo {
            height: 85px; /* Adjust based on your logo shape */
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.05));
        }

        .login-header h3 {
            font-weight: 800;
            color: #1a202c;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .login-header p {
            color: #718096;
            font-size: 0.95rem;
            margin-bottom: 35px;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #4a5568;
            margin-bottom: 8px;
            display: block;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 20px;
        }

        .input-group-custom .material-symbols-rounded {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
            font-size: 22px;
            z-index: 10;
        }

        .form-control {
            height: 56px;
            border-radius: 16px;
            padding-left: 52px;
            border: 2px solid #f1f5f9;
            background: #f8fafc;
            font-size: 0.95rem;
            transition: all 0.2s ease-in-out;
            width: 100%;
            box-sizing: border-box;
        }

        .form-control:focus {
            outline: none;
            background: #fff;
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
            color: #1e293b;
        }

        .btn-login {
            height: 56px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 1rem;
            background: #0d6efd;
            color: white;
            border: none;
            width: 100%;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(13, 110, 253, 0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -5px rgba(13, 110, 253, 0.5);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert-custom {
            border-radius: 16px;
            border: none;
            background-color: #fff5f5;
            color: #c53030;
            font-size: 0.85rem;
            padding: 14px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .system-footer {
            margin-top: 30px;
            color: #a0aec0;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>

    <div class="login-card">
        {{-- LOGO SECTION --}}
        <div class="brand-wrapper">
            <img src="{{ asset('img/final2.png') }}" alt="System Logo" class="login-logo">
        </div>

        <div class="login-header text-center">
            <h3>Welcome Back</h3>
            <p>Please enter your details to sign in</p>
        </div>

        {{-- Error Alerts --}}
        @if(session('error') || $errors->any())
            <div class="alert-custom">
                <span class="material-symbols-rounded" style="font-size: 20px;">error</span>
                <div>
                    @if(session('error')) {{ session('error') }} 
                    @else Invalid credentials provided. @endif
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login.process') }}">
            @csrf

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group-custom">
                    <span class="material-symbols-rounded">person</span>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="username" 
                        name="username" 
                        placeholder="Enter your username"
                        value="{{ old('username') }}"
                        required>
                </div>
            </div>

            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group-custom">
                    <span class="material-symbols-rounded">lock</span>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••"
                        required>
                </div>
            </div>

            <button type="submit" class="btn-login">
                Sign In
                <span class="material-symbols-rounded">login</span>
            </button>
        </form>

        <div class="system-footer text-center">
            Counseling Management System v1.0
        </div>
    </div>
</x-layouts.app>