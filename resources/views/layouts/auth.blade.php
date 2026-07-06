<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Auth') - {{ config('app.name', 'TradeFlow P2P') }}</title>

    <!-- Google Fonts (Outfit) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Custom CSS (Premium Bybit-Inspired Dark theme & Glassmorphism) -->
    <style>
        :root {
            --bg-color: #0c0e12;
            --card-bg: rgba(20, 26, 33, 0.65);
            --border-color: rgba(255, 255, 255, 0.08);
            --primary-accent: linear-gradient(135deg, #6c5ce7, #a29bfe);
            --secondary-accent: linear-gradient(135deg, #00c6ff, #0072ff);
            --text-color: #e2e8f0;
            --text-muted: #94a3b8;
            --input-bg: rgba(13, 17, 23, 0.8);
            --btn-gradient: linear-gradient(135deg, #6366f1, #a855f7);
            --btn-hover: linear-gradient(135deg, #4f46e5, #9333ea);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 10% 20%, rgba(99, 102, 241, 0.15) 0px, transparent 50%),
                radial-gradient(at 90% 80%, rgba(168, 85, 247, 0.15) 0px, transparent 50%);
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
            backdrop-filter: blur(16px) saturate(120%);
            -webkit-backdrop-filter: blur(16px) saturate(120%);
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
            font-weight: 700;
            background: linear-gradient(to right, #6366f1, #a855f7, #00c6ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -1px;
            display: inline-block;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text-color);
            margin-bottom: 8px;
        }

        .form-control {
            background-color: var(--input-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-color);
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background-color: var(--input-bg);
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.25);
            color: var(--text-color);
        }

        .btn-premium {
            background: var(--btn-gradient);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
        }

        .btn-premium:hover {
            background: var(--btn-hover);
            color: white;
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.6);
            transform: translateY(-1px);
        }

        .btn-premium:active {
            transform: translateY(0);
        }

        .text-muted-custom {
            color: var(--text-muted);
        }

        a {
            color: #a855f7;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        a:hover {
            color: #c084fc;
        }

        .alert-custom-danger {
            background-color: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.4);
            color: #fca5a5;
            border-radius: 10px;
        }

        .alert-custom-success {
            background-color: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.4);
            color: #a7f3d0;
            border-radius: 10px;
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
