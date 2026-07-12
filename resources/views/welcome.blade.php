@php
    $regUsers = \App\Services\SettingsService::get('stats_registered_users', '14,250');
    $compTrades = \App\Services\SettingsService::get('stats_completed_trades', '89,400');
    $dailyVol = \App\Services\SettingsService::get('stats_daily_volume', '485,000');
    $activeTraders = \App\Services\SettingsService::get('stats_active_traders', '4,200');
    $successRate = \App\Services\SettingsService::get('stats_success_rate', '99.5');
    
    $heroTitle = \App\Services\SettingsService::get('hero_title', 'Buy & Sell USDT Securely');
    $heroSubtitle = \App\Services\SettingsService::get('hero_subtitle', 'Trade with verified users, 100% secure escrow locking, and low operations fee.');
    
    $whatsapp = \App\Services\SettingsService::get('whatsapp_number');
    $telegram = \App\Services\SettingsService::get('telegram_link');
    $supportEmail = \App\Services\SettingsService::get('support_email', 'support@tradeflow.com');
    $announcement = \App\Services\SettingsService::get('landing_announcement');

    // Fetch live ads for preview safely
    $previewAds = collect();
    if (\Illuminate\Support\Facades\Schema::hasTable('advertisements')) {
        $previewAds = \App\Models\Advertisement::with(['user', 'country', 'paymentMethods'])
            ->where('status', 'active')
            ->latest()
            ->limit(3)
            ->get();
    }
@endphp
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
            --success-color: #0ecb81;
            --danger-color: #f6465d;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(at 0% 0%, rgba(243, 156, 18, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(230, 126, 34, 0.06) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-color);
            min-height: 100vh;
        }

        /* Announcement Bar */
        .announcement-bar {
            background: linear-gradient(to right, #e67e22, #f39c12);
            color: #0b0c0e;
            font-weight: 700;
            text-align: center;
            padding: 8px 10px;
            font-size: 0.88rem;
        }

        /* Navbar */
        .navbar-custom {
            background-color: rgba(18, 20, 24, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 1030;
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
            color: var(--text-muted) !important;
            font-weight: 500;
            padding: 8px 18px !important;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            color: var(--text-color) !important;
            background-color: rgba(255, 255, 255, 0.03);
        }

        /* Hero */
        .hero-section {
            padding: 120px 0 100px 0;
            position: relative;
            overflow: hidden;
        }

        .hero-title {
            font-size: 3.8rem;
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
            color: white !important;
            padding: 15px 32px;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 4px 20px rgba(243, 156, 18, 0.25);
            transition: all 0.2s ease;
        }

        .btn-premium:hover {
            background: var(--btn-hover);
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

        /* Glass Cards */
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
            transform: translateY(-4px);
            border-color: rgba(243, 156, 18, 0.18);
            box-shadow: 0 12px 40px rgba(243, 156, 18, 0.06);
        }

        /* Stats */
        .stats-section {
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            background-color: rgba(18, 20, 24, 0.5);
            padding: 60px 0;
            margin-bottom: 80px;
        }

        .stat-value {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--accent-orange);
            margin-bottom: 6px;
            letter-spacing: -1px;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        /* Table */
        .table-custom {
            color: var(--text-color);
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-custom th {
            color: var(--text-muted);
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            padding: 16px 20px;
            border-bottom: 2px solid var(--border-color);
        }

        .table-custom td {
            padding: 18px 20px;
            border-bottom: 1px solid var(--border-color);
        }

        /* FAQ */
        .faq-accordion .accordion-item {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            margin-bottom: 12px;
            border-radius: 12px !important;
            overflow: hidden;
        }

        .faq-accordion .accordion-button {
            background-color: transparent;
            color: var(--text-color);
            font-weight: 700;
            padding: 20px;
            box-shadow: none;
        }

        .faq-accordion .accordion-button:not(.collapsed) {
            color: var(--accent-orange);
        }

        .faq-accordion .accordion-body {
            color: var(--text-muted);
            padding: 0 20px 20px 20px;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Footer */
        footer {
            border-top: 1px solid var(--border-color);
            color: var(--text-muted);
            padding: 40px 0;
            margin-top: 100px;
        }
    </style>
</head>
<body>

    <!-- Announcement Banner -->
    @if(!empty($announcement))
        <div class="announcement-bar">
            📢 {{ $announcement }}
        </div>
    @endif

    <!-- Header Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="/">
                <h3>{{ App\Services\SettingsService::get('platform_name', 'TradeFlow P2P') }}</h3>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation" style="border-color: var(--border-color);">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-5">
                    <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('marketplace') }}">Marketplace</a></li>
                    <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link" href="#faq">FAQ</a></li>
                    <li class="nav-item"><a class="nav-link" href="{{ route('support.help') }}">Help Center</a></li>
                </ul>

                <div class="d-flex gap-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-custom py-2.5 px-4">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-custom py-2.5 px-4">Log In</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-premium py-2.5 px-4">Register</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <div class="col-lg-7">
                    <h1 class="hero-title">
                        {!! str_replace('USDT', '<span>USDT</span>', $heroTitle) !!}
                    </h1>
                    <p class="hero-subtitle">
                        {{ $heroSubtitle }}
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route('marketplace') }}" class="btn btn-premium px-5 py-3">Start Trading Now</a>
                        <a href="{{ route('login') }}" class="btn btn-outline-custom px-5 py-3">Connect Wallet</a>
                    </div>
                </div>
                <div class="col-lg-5 d-none d-lg-block">
                    <!-- Right column visualization -->
                    <div class="glass-card text-center p-5 position-relative overflow-hidden" style="border-color: rgba(243,156,18,0.18);">
                        <div class="position-absolute top-0 start-50 translate-middle-x" style="width: 250px; height: 250px; background: radial-gradient(circle, rgba(243,156,18,0.2) 0%, transparent 70%); filter: blur(30px);"></div>
                        <div class="mb-4" style="font-size: 4.5rem; filter: drop-shadow(0 0 10px rgba(243,156,18,0.3));">🛡️</div>
                        <h4 class="fw-bold mb-3">100% Escrow Escort</h4>
                        <p class="text-muted small mb-4">Every peer negotiation holds locked cryptocurrency assets in security escrow vaults. Funds are released automatically after verification checks.</p>
                        <span class="badge bg-success py-2 px-3 fw-bold">Active Shield Enabled</span>
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
                    <div class="stat-value">{{ $regUsers }}</div>
                    <div class="stat-label">Registered Users</div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-value">{{ $compTrades }}</div>
                    <div class="stat-label">Trades Completed</div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-value">${{ $dailyVol }}</div>
                    <div class="stat-label">Daily Volume</div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="stat-value">{{ $successRate }}%</div>
                    <div class="stat-label">Success Rate</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Live Marketplace Preview -->
    <section class="container mb-5 pb-5">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="fw-bold fs-2 mb-1">Live P2P Offers</h2>
                <p class="text-muted mb-0">Quick preview of active trading proposals currently available on the marketplace.</p>
            </div>
            <a href="{{ route('marketplace') }}" class="btn btn-outline-custom btn-sm">View All Marketplace Offers →</a>
        </div>

        <div class="glass-card p-0 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-custom align-middle border-0 mb-0">
                    <thead>
                        <tr>
                            <th>Merchant</th>
                            <th>Rate</th>
                            <th>Available / Limits</th>
                            <th>Payment Methods</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($previewAds->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    No advertisements active in the system.
                                </td>
                            </tr>
                        @else
                            @foreach($previewAds as $ad)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; background-color: var(--accent-orange) !important; color: #fff !important;">
                                                {{ substr($ad->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <span class="fw-bold">{{ $ad->user->name }}</span>
                                                    <span class="badge bg-success-subtle text-success small" style="font-size: 0.65rem; background-color: rgba(14,203,129,0.1); border: 1px solid rgba(14,203,129,0.25);">✓ Verified</span>
                                                </div>
                                                <small class="text-muted" style="font-size: 0.75rem;">98.4% Completion</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <h5 class="mb-0 fw-bold" style="color: var(--accent-orange);">
                                            {{ number_format($ad->rate, 2) }}
                                            <span class="small text-muted" style="font-size: 0.75rem;">{{ $ad->country->currency_code }}</span>
                                        </h5>
                                    </td>
                                    <td>
                                        <div class="small mb-1">
                                            <span class="text-muted">Available:</span> <strong>{{ number_format($ad->amount, 2) }} USDT</strong>
                                        </div>
                                        <div class="small text-muted">
                                            <span>Limits:</span> {{ number_format($ad->min_limit, 2) }} - {{ number_format($ad->max_limit, 2) }} {{ $ad->country->currency_code }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($ad->paymentMethods as $pm)
                                                <span class="badge border border-secondary text-muted" style="background-color: rgba(255,255,255,0.03); font-size: 0.75rem;">
                                                    {{ $pm->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('orders.create', $ad->id) }}" class="btn btn-premium btn-sm px-4">
                                            Buy USDT
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Trust & Core Features Section -->
    <section id="features" class="container mb-5 pb-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold fs-1">Guaranteed Security & Trust</h2>
            <p class="text-muted">Every transaction is guided by our automated escrow contract engines to ensure zero-risk transfers.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="glass-card text-center p-4">
                    <div style="font-size: 3rem;" class="mb-3">🔒</div>
                    <h5 class="fw-bold mb-2">Escrow Protection</h5>
                    <p class="text-muted small mb-0">Seller's cryptocurrency is automatically isolated and locked the moment an order starts, making it impossible to double-spend or run.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card text-center p-4">
                    <div style="font-size: 3rem;" class="mb-3">💸</div>
                    <h5 class="fw-bold mb-2">Zero Trading Fees</h5>
                    <p class="text-muted small mb-0">Trade directly with peers without paying heavy transaction commissions. Deposit, advertise, and buy at raw market rates.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card text-center p-4">
                    <div style="font-size: 3rem;" class="mb-3">💬</div>
                    <h5 class="fw-bold mb-2">Live Secure Chats</h5>
                    <p class="text-muted small mb-0">Coordinate bank details and transfers inside order rooms with real-time websocket updates and image document proof uploads.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="container mb-5 pb-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold fs-1">Frequently Asked Questions</h2>
            <p class="text-muted">Have questions? We have answers to help you navigate our P2P platform.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion faq-accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqHeadingOne">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseOne" aria-expanded="true" aria-controls="faqCollapseOne">
                                How does the escrow protection work?
                            </button>
                        </h2>
                        <div id="faqCollapseOne" class="accordion-collapse collapse show" aria-labelledby="faqHeadingOne" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                When a buyer creates an order, the seller's USDT is immediately deducted from their wallet and locked in a secure escrow hold. The seller cannot withdraw or move these funds. Once the buyer completes the bank transfer and the seller confirms the receipt, the escrow releases the USDT straight to the buyer's wallet.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqHeadingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseTwo" aria-expanded="false" aria-controls="faqCollapseTwo">
                                What if a seller refuses to release my USDT?
                            </button>
                        </h2>
                        <div id="faqCollapseTwo" class="accordion-collapse collapse" aria-labelledby="faqHeadingTwo" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                If you have paid the seller but they do not release the USDT, you can open a dispute inside the trade room. Our compliance team will immediately intervene, verify your uploaded payment screenshot receipt, and release the locked escrow funds directly to you.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="faqHeadingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseThree" aria-expanded="false" aria-controls="faqCollapseThree">
                                Are there any fees for depositing or withdrawing?
                            </button>
                        </h2>
                        <div id="faqCollapseThree" class="accordion-collapse collapse" aria-labelledby="faqHeadingThree" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Depositing USDT via NOWPayments is completely free of charge. For external withdrawals to TRC-20 addresses, a flat transaction fee of 2.0 USDT is applied to cover TRON network blockchain gas fees.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Support Help Details -->
    <section class="container mb-5">
        <div class="glass-card text-center p-5">
            <h3 class="fw-bold mb-3">Need Immediate Assistance?</h3>
            <p class="text-muted mb-4 mx-auto" style="max-width: 600px;">Our client support desk is available 24/7. Connect with us directly via WhatsApp, Telegram, or raise a support ticket via email.</p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                @if(!empty($whatsapp))
                    <a href="{{ route('support.help') }}" class="btn btn-premium px-4">💬 Chat on WhatsApp</a>
                @endif
                @if(!empty($telegram))
                    <a href="{{ $telegram }}" target="_blank" class="btn btn-outline-custom px-4">✈️ Join Telegram Group</a>
                @endif
                <a href="mailto:{{ $supportEmail }}" class="btn btn-outline-custom px-4">✉️ Email Support</a>
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
