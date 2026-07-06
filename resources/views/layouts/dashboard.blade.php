<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ App\Services\SettingsService::get('platform_name', 'TradeFlow P2P') }}</title>

    <!-- Google Fonts (Outfit) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Custom CSS (Premium Bybit-Inspired Dark theme & Glassmorphism) -->
    <style>
        :root {
            --bg-color: #0c1015;
            --nav-bg: rgba(18, 25, 34, 0.85);
            --card-bg: rgba(22, 30, 41, 0.7);
            --border-color: rgba(255, 255, 255, 0.06);
            --text-color: #f1f5f9;
            --text-muted: #64748b;
            --accent-purple: #6c5ce7;
            --accent-blue: #0072ff;
            --btn-gradient: linear-gradient(135deg, #6366f1, #a855f7);
            --btn-hover: linear-gradient(135deg, #4f46e5, #9333ea);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(168, 85, 247, 0.08) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-color);
            min-height: 100vh;
        }

        /* Navbar */
        .navbar-custom {
            background-color: var(--nav-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 15px 0;
        }

        .navbar-brand h3 {
            font-weight: 700;
            background: linear-gradient(to right, #6366f1, #a855f7, #00c6ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .nav-link {
            color: var(--text-color);
            font-weight: 500;
            padding: 8px 16px !important;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.05);
        }

        /* Card styles */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
        }

        .card-title-custom {
            font-weight: 600;
            margin-bottom: 20px;
        }

        /* Form elements */
        .form-control, .form-select {
            background-color: rgba(13, 17, 23, 0.8);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-color);
            padding: 10px 14px;
        }

        .form-control:focus, .form-select:focus {
            background-color: rgba(13, 17, 23, 0.8);
            border-color: #8b5cf6;
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2);
            color: var(--text-color);
        }

        /* Buttons */
        .btn-premium {
            background: var(--btn-gradient);
            border: none;
            border-radius: 8px;
            color: white;
            padding: 10px 20px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            transition: all 0.2s ease;
        }

        .btn-premium:hover {
            background: var(--btn-hover);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(99, 102, 241, 0.5);
        }

        .btn-outline-custom {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-color);
            background: transparent;
            padding: 10px 20px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-outline-custom:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.15);
        }

        /* Tables */
        .table-custom {
            color: var(--text-color);
        }

        .table-custom th {
            color: var(--text-muted);
            font-weight: 500;
            border-bottom: 2px solid var(--border-color);
            padding: 15px 10px;
        }

        .table-custom td {
            padding: 15px 10px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        /* Balance badge */
        .balance-badge {
            background-color: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #818cf8;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Alerts */
        .alert-custom-info {
            background-color: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.25);
            color: #93c5fd;
            border-radius: 12px;
            padding: 15px 20px;
        }

        .alert-custom-success {
            background-color: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.25);
            color: #a7f3d0;
            border-radius: 12px;
            padding: 15px 20px;
        }

        .alert-custom-danger {
            background-color: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #fca5a5;
            border-radius: 12px;
            padding: 15px 20px;
        }

        /* Footer */
        footer {
            border-top: 1px solid var(--border-color);
            color: var(--text-muted);
            padding: 30px 0;
            margin-top: 50px;
            font-size: 0.9rem;
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Header Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <h3>{{ App\Services\SettingsService::get('platform_name', 'TradeFlow P2P') }}</h3>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation" style="border-color: var(--border-color);">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('marketplace*') ? 'active' : '' }}" href="{{ route('marketplace') }}">Marketplace</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('advertisements*') ? 'active' : '' }}" href="{{ route('advertisements.my') }}">My Ads</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('orders*') ? 'active' : '' }}" href="{{ route('orders.my') }}">My Trades</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Route::is('wallet*') ? 'active' : '' }}" href="{{ route('wallet') }}">Wallet</a>
                    </li>
                </ul>

                @auth
                    <div class="d-flex align-items-center gap-3">
                        <!-- Balance badge -->
                        <div class="balance-badge d-none d-md-block">
                            Available: {{ number_format(Auth::user()->wallet->available_balance, 2) }} USDT
                        </div>

                        <!-- User Profile Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-outline-custom dropdown-toggle px-3 py-2" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end bg-dark border-secondary" aria-labelledby="profileDropdown">
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('profile.two-factor') }}">Security (2FA)</a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('profile.kyc') }}">KYC Verification</a>
                                </li>
                                @if(Auth::user()->is_admin)
                                    <li><hr class="dropdown-divider border-secondary"></li>
                                    <li>
                                        <a class="dropdown-item py-2 text-warning" href="/admin" target="_blank">Admin Panel</a>
                                    </li>
                                @endif
                                <li><hr class="dropdown-divider border-secondary"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 text-danger">Sign Out</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <div class="container my-5">
        @if (session('status'))
            <div class="alert alert-custom-success mb-4">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-custom-danger mb-4">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} {{ App\Services\SettingsService::get('platform_name', 'TradeFlow P2P') }}. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    @yield('scripts')
</body>
</html>
