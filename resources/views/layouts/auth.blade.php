<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Auth') - {{ config('app.name', 'TradeFlow P2P') }}</title>

    <!-- Google Fonts (Plus Jakarta Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Custom CSS (Premium Bybit-Inspired Dark theme & Glassmorphism) -->
    <style>
        :root {
            --bg-color: #0b0c0e;
            --card-bg: rgba(28, 30, 36, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-color: #f5f6f7;
            --text-muted: #848e9c;
            --accent-orange: #f39c12;
            --accent-amber: #e67e22;
            --btn-gradient: linear-gradient(135deg, #ff9f43, #f39c12);
            --btn-hover: linear-gradient(135deg, #e67e22, #d35400);
            --input-bg: rgba(18, 20, 24, 0.8);
            --success-color: #0ecb81;
            --danger-color: #f6465d;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 10% 20%, rgba(243, 156, 18, 0.06) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgba(230, 126, 34, 0.06) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-container {
            width: 100%;
            max-width: 460px;
            padding: 20px;
        }

        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px) saturate(120%);
            -webkit-backdrop-filter: blur(20px) saturate(120%);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        .brand-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .brand-logo h2 {
            font-weight: 800;
            background: linear-gradient(to right, #ff9f43, #f1c40f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
            display: inline-block;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.88rem;
            color: var(--text-color);
            margin-bottom: 8px;
        }

        .form-control {
            background-color: var(--input-bg) !important;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-color) !important;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: rgba(18, 20, 24, 0.9) !important;
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.15);
        }

        .btn-premium {
            background: var(--btn-gradient);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.2);
        }

        .btn-premium:hover {
            background: var(--btn-hover);
            color: white;
            box-shadow: 0 6px 20px rgba(243, 156, 18, 0.35);
            transform: translateY(-1px);
        }

        .btn-premium:active {
            transform: translateY(0);
        }

        .text-muted-custom {
            color: var(--text-muted);
        }

        a {
            color: var(--accent-orange);
            text-decoration: none;
            transition: color 0.2s ease;
            font-weight: 500;
        }

        a:hover {
            color: var(--accent-amber);
        }

        .alert-custom-danger {
            background-color: rgba(246, 70, 93, 0.08);
            border: 1px solid rgba(246, 70, 93, 0.15);
            color: var(--danger-color);
            border-radius: 10px;
            padding: 12px 16px;
        }

        .alert-custom-success {
            background-color: rgba(14, 203, 129, 0.08);
            border: 1px solid rgba(14, 203, 129, 0.15);
            color: var(--success-color);
            border-radius: 10px;
            padding: 12px 16px;
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="auth-container">
        <div class="glass-card">
            <div class="brand-logo">
                <h2>TradeFlow P2P</h2>
            </div>
            
            @if ($errors->any())
                <div class="alert alert-custom-danger mb-4">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="alert alert-custom-success mb-4">
                    {{ session('status') }}
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @yield('scripts')
</body>
</html>
