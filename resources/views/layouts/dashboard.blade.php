<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - {{ App\Services\SettingsService::get('platform_name', 'TradeFlow P2P') }}</title>

    <!-- Google Fonts (Plus Jakarta Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Custom CSS (Premium Binance/Bybit Inspired Dark Charcoal & Orange Theme) -->
    <style>
        :root {
            --bg-color: #0b0c0e;
            --nav-bg: rgba(18, 20, 24, 0.92);
            --card-bg: rgba(28, 30, 36, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-color: #f5f6f7;
            --text-muted: #848e9c;
            --accent-orange: #f39c12;
            --accent-amber: #e67e22;
            --accent-gold: #f1c40f;
            --btn-gradient: linear-gradient(135deg, #ff9f43, #f39c12);
            --btn-hover: linear-gradient(135deg, #e67e22, #d35400);
            --success-color: #0ecb81;
            --danger-color: #f6465d;
            --warning-color: #f3ba2f;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(243, 156, 18, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(230, 126, 34, 0.05) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-color);
            min-height: 100vh;
        }

        /* Navbar */
        .navbar-custom {
            background-color: var(--nav-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-color);
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: all 0.3s ease;
        }

        .navbar-brand h3 {
            font-weight: 800;
            background: linear-gradient(to right, #ff9f43, #f1c40f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            letter-spacing: -0.5px;
        }

        .nav-link {
            color: var(--text-muted);
            font-weight: 500;
            padding: 8px 18px !important;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            color: var(--text-color);
            background-color: rgba(255, 255, 255, 0.03);
        }

        .nav-link.active {
            color: #ffffff !important;
            background: rgba(243, 156, 18, 0.12);
            border: 1px solid rgba(243, 156, 18, 0.2);
        }

        /* Card styles */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px 0 rgba(243, 156, 18, 0.08);
            border-color: rgba(243, 156, 18, 0.15);
        }

        .card-title-custom {
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--text-color);
        }

        /* Form elements */
        .form-control, .form-select {
            background-color: rgba(18, 20, 24, 0.8) !important;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-color) !important;
            padding: 12px 16px;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .form-control:focus, .form-select:focus {
            background-color: rgba(18, 20, 24, 0.9) !important;
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 3px rgba(243, 156, 18, 0.15);
        }

        /* Buttons */
        .btn-premium {
            background: var(--btn-gradient);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 12px 24px;
            font-weight: 600;
            box-shadow: 0 4px 16px rgba(243, 156, 18, 0.2);
            transition: all 0.2s ease;
        }

        .btn-premium:hover {
            background: var(--btn-hover);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(243, 156, 18, 0.35);
        }

        .btn-outline-custom {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            color: var(--text-color);
            background: transparent;
            padding: 12px 24px;
            font-weight: 600;
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
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table-custom th {
            background-color: rgba(255, 255, 255, 0.01);
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            border-bottom: 2px solid var(--border-color);
            padding: 16px 20px;
        }

        .table-custom td {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
            transition: background-color 0.2s ease;
        }

        .table-custom tr:hover td {
            background-color: rgba(255, 255, 255, 0.015);
        }

        /* Balance badge */
        .balance-badge {
            background-color: rgba(243, 156, 18, 0.1);
            border: 1px solid rgba(243, 156, 18, 0.25);
            color: var(--accent-orange);
            padding: 8px 18px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.88rem;
        }

        /* Alerts */
        .alert-custom-info {
            background-color: rgba(243, 156, 18, 0.08);
            border: 1px solid rgba(243, 156, 18, 0.15);
            color: var(--accent-orange);
            border-radius: 12px;
            padding: 15px 20px;
        }

        .alert-custom-success {
            background-color: rgba(14, 203, 129, 0.08);
            border: 1px solid rgba(14, 203, 129, 0.15);
            color: var(--success-color);
            border-radius: 12px;
            padding: 15px 20px;
        }

        .alert-custom-danger {
            background-color: rgba(246, 70, 93, 0.08);
            border: 1px solid rgba(246, 70, 93, 0.15);
            color: var(--danger-color);
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
