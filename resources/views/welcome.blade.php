<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ App\Services\SettingsService::get('platform_name', 'TradeFlow P2P') }} - Premium Escrow USDT Exchange</title>

    <!-- Google Fonts (Plus Jakarta Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Custom Premium CSS -->
    <style>
        :root {
            --bg-color: #0b0c0e;
            --card-bg: rgba(28, 30, 36, 0.7);
            --border-color: rgba(255, 255, 255, 0.08);
            --text-color: #f5f6f7;
            --text-muted: #848e9c;
            --accent-orange: #f39c12;
            --btn-gradient: linear-gradient(135deg, #ff9f43, #f39c12);
            --btn-hover: linear-gradient(135deg, #e67e22, #d35400);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(243, 156, 18, 0.07) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(230, 126, 34, 0.05) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Navbar */
        .navbar-custom {
            background-color: rgba(18, 20, 24, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border-color);
            padding: 20px 0;
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

        /* Hero Section */
        .hero-section {
            padding: 100px 0 80px 0;
            position: relative;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -1.5px;
            margin-bottom: 24px;
        }

        .hero-title span {
            background: linear-gradient(to right, #ff9f43, #f1c40f);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            color: var(--text-muted);
            font-size: 1.15rem;
            line-height: 1.6;
            margin-bottom: 40px;
            max-width: 580px;
        }

        /* Buttons */
        .btn-premium {
            background: var(--btn-gradient);
            border: none;
            border-radius: 12px;
            color: white;
            padding: 15px 32px;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 4px 20px rgba(243, 156, 18, 0.25);
            transition: all 0.2s ease;
        }

        .btn-premium:hover {
            background: var(--btn-hover);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(243, 156, 18, 0.4);
        }

        .btn-outline-custom {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-color);
            background: transparent;
            padding: 15px 32px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .btn-outline-custom:hover {
            background-color: rgba(255, 255, 255, 0.05);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        /* Features grid */
        .feature-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            height: 100%;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            border-color: rgba(243, 156, 18, 0.2);
            box-shadow: 0 12px 40px rgba(0,0,0,0.4);
        }

        .feature-icon {
            font-size: 2.2rem;
            margin-bottom: 20px;
            display: inline-block;
        }

        .feature-title {
            font-weight: 700;
            margin-bottom: 12px;
            font-size: 1.15rem;
        }

        .feature-desc {
            color: var(--text-muted);
            font-size: 0.95rem;
            line-height: 1.5;
            margin: 0;
        }

        /* Stats */
        .stats-section {
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            background-color: rgba(18, 20, 24, 0.5);
            padding: 50px 0;
            margin-bottom: 80px;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--accent-orange);
            margin-bottom: 6px;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 500;
        }

        /* Footer */
        footer {
            border-top: 1px solid var(--border-color);
            color: var(--text-muted);
            padding: 40px 0;
            margin-top: auto;
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="/">
                <h3>{{ App\Services\SettingsService::get('platform_name', 'TradeFlow P2P') }}</h3>
            </a>
            <div class="ms-auto d-flex gap-2">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-custom py-2 px-4">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-custom py-2 px-4">Log In</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-premium py-2 px-4">Register</a>
                    @endif
                @endauth
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <h1 class="hero-title">
                        Secure USDT P2P Trading <br><span>With Escrow Protection</span>
                    </h1>
                    <p class="hero-subtitle">
                        Trade cryptocurrency directly with other verified users in your local currency. Zero deposit fees, fast releases, and industry-standard security locks.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('marketplace') }}" class="btn btn-premium">Enter P2P Marketplace</a>
                        <a href="{{ route('login') }}" class="btn btn-outline-custom">Connect Wallet</a>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <!-- Premium visual placeholder -->
                    <div class="glass-card text-center p-5 position-relative overflow-hidden" style="border-color: rgba(243,156,18,0.15);">
                        <div class="position-absolute top-0 start-50 translate-middle-x" style="width: 200px; height: 200px; background: radial-gradient(circle, rgba(243,156,18,0.2) 0%, transparent 70%); filter: blur(20px);"></div>
                        <div class="mb-4" style="font-size: 4rem;">🛡️</div>
                        <h4 class="fw-bold mb-3">100% Escrow Protection</h4>
                        <p class="text-muted small mb-4">Every trade is guarded by TradeFlow's automated lock-and-release engine. Funds are only transferred after verification.</p>
                        <span class="badge bg-success py-2 px-3">Verified Operations</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row text-center g-4">
                <div class="col-md-3 col-6">
                    <div class="stat-value">$12.4M+</div>
                    <div class="stat-label">Trading Volume</div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-value">50,000+</div>
                    <div class="stat-label">Successful Trades</div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-value">&lt; 5 Mins</div>
                    <div class="stat-label">Avg. Release Time</div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-value">0%</div>
                    <div class="stat-label">Maker/Taker Fees</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="container mb-5 pb-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold fs-1">Why Trade On TradeFlow</h2>
            <p class="text-muted">A premium peer-to-peer cryptocurrency escrow system designed for speed and security.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h5 class="feature-title">Instant Locking</h5>
                    <p class="feature-desc">When a buyer starts a trade, the seller's USDT is immediately locked in our escrow engine, preventing double-selling.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">💬</div>
                    <h5 class="feature-title">Real-Time Chat</h5>
                    <p class="feature-desc">Communicate instantly inside secure trade rooms with real-time websocket updates and image receipts upload support.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">⚖️</div>
                    <h5 class="feature-title">Fair Dispute Resolution</h5>
                    <p class="feature-desc">Our dedicated compliance support reviews chat transcripts and payment receipts to resolve conflicts fairly and instantly.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} {{ App\Services\SettingsService::get('platform_name', 'TradeFlow P2P') }}. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
