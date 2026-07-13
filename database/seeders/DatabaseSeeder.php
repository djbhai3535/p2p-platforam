<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use App\Models\Country;
use App\Models\Language;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic, idempotent demo data.
     */
    public function run(): void
    {
        // 1. Seed Default Languages
        $en = Language::updateOrCreate(
            ['code' => 'en'],
            ['name' => 'English', 'direction' => 'ltr', 'is_active' => true, 'is_default' => true]
        );

        $ur = Language::updateOrCreate(
            ['code' => 'ur'],
            ['name' => 'Urdu', 'direction' => 'rtl', 'is_active' => true, 'is_default' => false]
        );

        // 2. Seed Default Countries
        $pakistan = Country::updateOrCreate(
            ['iso_code' => 'PK'],
            ['name' => 'Pakistan', 'currency_code' => 'PKR', 'currency_symbol' => '₨', 'phone_code' => '+92', 'is_active' => true]
        );

        $usa = Country::updateOrCreate(
            ['iso_code' => 'US'],
            ['name' => 'United States', 'currency_code' => 'USD', 'currency_symbol' => '$', 'phone_code' => '+1', 'is_active' => true]
        );

        $uk = Country::updateOrCreate(
            ['iso_code' => 'GB'],
            ['name' => 'United Kingdom', 'currency_code' => 'GBP', 'currency_symbol' => '£', 'phone_code' => '+44', 'is_active' => true]
        );

        $uae = Country::updateOrCreate(
            ['iso_code' => 'AE'],
            ['name' => 'United Arab Emirates', 'currency_code' => 'AED', 'currency_symbol' => 'د.إ', 'phone_code' => '+971', 'is_active' => true]
        );

        // 3. Seed dynamic country-specific payment methods (Pakistan)
        $paymentMethods = [
            [
                'name' => 'JazzCash',
                'slug' => 'jazzcash',
                'fields' => [
                    ['name' => 'account_title', 'label' => 'Account Title', 'type' => 'text', 'required' => true],
                    ['name' => 'mobile_number', 'label' => 'Mobile Number', 'type' => 'text', 'required' => true],
                ],
            ],
            [
                'name' => 'EasyPaisa',
                'slug' => 'easypaisa',
                'fields' => [
                    ['name' => 'account_title', 'label' => 'Account Title', 'type' => 'text', 'required' => true],
                    ['name' => 'mobile_number', 'label' => 'Mobile Number', 'type' => 'text', 'required' => true],
                ],
            ],
            [
                'name' => 'Meezan Bank',
                'slug' => 'meezan-bank',
                'fields' => [
                    ['name' => 'account_title', 'label' => 'Account Title', 'type' => 'text', 'required' => true],
                    ['name' => 'account_number', 'label' => 'Account Number', 'type' => 'text', 'required' => true],
                    ['name' => 'iban', 'label' => 'IBAN', 'type' => 'text', 'required' => true],
                    ['name' => 'branch_name', 'label' => 'Branch Name', 'type' => 'text', 'required' => false],
                ],
            ],
            [
                'name' => 'HBL',
                'slug' => 'hbl',
                'fields' => [
                    ['name' => 'account_title', 'label' => 'Account Title', 'type' => 'text', 'required' => true],
                    ['name' => 'account_number', 'label' => 'Account Number', 'type' => 'text', 'required' => true],
                    ['name' => 'iban', 'label' => 'IBAN', 'type' => 'text', 'required' => true],
                ],
            ],
            [
                'name' => 'UBL',
                'slug' => 'ubl',
                'fields' => [
                    ['name' => 'account_title', 'label' => 'Account Title', 'type' => 'text', 'required' => true],
                    ['name' => 'account_number', 'label' => 'Account Number', 'type' => 'text', 'required' => true],
                    ['name' => 'iban', 'label' => 'IBAN', 'type' => 'text', 'required' => true],
                ],
            ],
        ];

        foreach ($paymentMethods as $pm) {
            PaymentMethod::updateOrCreate(
                ['country_id' => $pakistan->id, 'slug' => $pm['slug']],
                ['name' => $pm['name'], 'fields' => $pm['fields'], 'is_active' => true]
            );
        }

        // Add USA payment methods
        $bankTransfer = PaymentMethod::updateOrCreate(
            ['country_id' => $usa->id, 'slug' => 'zelle'],
            [
                'name' => 'Zelle',
                'fields' => [
                    ['name' => 'account_title', 'label' => 'Account Name', 'type' => 'text', 'required' => true],
                    ['name' => 'email', 'label' => 'Zelle Email / Phone', 'type' => 'text', 'required' => true],
                ],
                'is_active' => true,
            ]
        );

        $venmo = PaymentMethod::updateOrCreate(
            ['country_id' => $usa->id, 'slug' => 'venmo'],
            [
                'name' => 'Venmo',
                'fields' => [
                    ['name' => 'username', 'label' => 'Venmo Username', 'type' => 'text', 'required' => true],
                ],
                'is_active' => true,
            ]
        );

        // 4. Seed default users
        // Admin
        $admin = User::where('email', 'admin@tradeflow.com')->first();
        if (! $admin) {
            $admin = User::create([
                'name' => 'TradeFlow Admin',
                'email' => 'admin@tradeflow.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'country_id' => $pakistan->id,
                'language_id' => $en->id,
                'is_admin' => true,
                'is_active' => true,
            ]);
        }
        $admin->wallet->update(['available_balance' => 10000.00000000]);

        // Seller user
        $seller = User::where('email', 'seller@tradeflow.com')->first();
        if (! $seller) {
            $seller = User::create([
                'name' => 'John Seller',
                'email' => 'seller@tradeflow.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'country_id' => $pakistan->id,
                'language_id' => $en->id,
                'is_admin' => false,
                'is_active' => true,
            ]);
        }
        $seller->wallet->update(['available_balance' => 500.00000000]);

        // Buyer user
        $buyer = User::where('email', 'buyer@tradeflow.com')->first();
        if (! $buyer) {
            $buyer = User::create([
                'name' => 'John Buyer',
                'email' => 'buyer@tradeflow.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'),
                'country_id' => $pakistan->id,
                'language_id' => $en->id,
                'is_admin' => false,
                'is_active' => true,
            ]);
        }
        $buyer->wallet->update(['available_balance' => 50.00000000]);

        // 12 Additional Realistic Demo Merchants & Users
        $demoUsers = [
            ['name' => 'Apex Merchant', 'email' => 'apex@tradeflow.com', 'balance' => 2500.0, 'country' => $pakistan],
            ['name' => 'Crypto King', 'email' => 'cryptoking@tradeflow.com', 'balance' => 5000.0, 'country' => $pakistan],
            ['name' => 'Zeeshan Trader', 'email' => 'zeeshan@tradeflow.com', 'balance' => 1500.0, 'country' => $pakistan],
            ['name' => 'Fatima Noor', 'email' => 'fatima@tradeflow.com', 'balance' => 3000.0, 'country' => $pakistan],
            ['name' => 'USDT Reserve', 'email' => 'reserve@tradeflow.com', 'balance' => 8000.0, 'country' => $usa],
            ['name' => 'London OTC', 'email' => 'london@tradeflow.com', 'balance' => 4500.0, 'country' => $uk],
            ['name' => 'Dubai Gold Escrow', 'email' => 'dubai@tradeflow.com', 'balance' => 12000.0, 'country' => $uae],
            ['name' => 'Ali Raza', 'email' => 'aliraza@tradeflow.com', 'balance' => 350.0, 'country' => $pakistan],
            ['name' => 'Sarah Connor', 'email' => 'sarah@tradeflow.com', 'balance' => 200.0, 'country' => $usa],
            ['name' => 'Asif Khan', 'email' => 'asif@tradeflow.com', 'balance' => 80.0, 'country' => $pakistan],
            ['name' => 'David Miller', 'email' => 'david@tradeflow.com', 'balance' => 900.0, 'country' => $uk],
            ['name' => 'Fatma Sultan', 'email' => 'fatmasultan@tradeflow.com', 'balance' => 600.0, 'country' => $uae],
        ];

        $users = [];
        foreach ($demoUsers as $du) {
            $u = User::where('email', $du['email'])->first();
            if (! $u) {
                $u = User::create([
                    'name' => $du['name'],
                    'email' => $du['email'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('password123'),
                    'country_id' => $du['country']->id,
                    'language_id' => $en->id,
                    'is_admin' => false,
                    'is_active' => true,
                ]);
            }
            $u->wallet->update(['available_balance' => $du['balance']]);
            $users[] = $u;

            // Seed user payment account safely
            if ($du['country']->iso_code === 'PK') {
                $pm = PaymentMethod::where('slug', 'easypaisa')->first();
                if ($pm) {
                    $u->userPaymentMethods()->updateOrCreate(
                        ['payment_method_id' => $pm->id],
                        [
                            'account_title' => $du['name'],
                            'account_details' => ['mobile_number' => '03'.rand(0, 9).rand(1000000, 9999999)],
                            'is_active' => true,
                        ]
                    );
                }
            } elseif ($du['country']->iso_code === 'US') {
                $u->userPaymentMethods()->updateOrCreate(
                    ['payment_method_id' => $bankTransfer->id],
                    [
                        'account_title' => $du['name'],
                        'account_details' => ['email' => strtolower(str_replace(' ', '', $du['name'])).'@gmail.com'],
                        'is_active' => true,
                    ]
                );
            }
        }

        // 5. Seed active P2P advertisements
        $methodsPK = PaymentMethod::where('country_id', $pakistan->id)->pluck('id')->toArray();
        $methodsUS = PaymentMethod::where('country_id', $usa->id)->pluck('id')->toArray();

        // Apex Sell ad
        $ad1 = Advertisement::where('user_id', $users[0]->id)->where('type', 'sell')->first();
        if (! $ad1) {
            $ad1 = Advertisement::create([
                'user_id' => $users[0]->id, // Apex Merchant
                'country_id' => $pakistan->id,
                'type' => 'sell',
                'amount' => 1500.0,
                'rate' => 278.50,
                'min_limit' => 5000.0,
                'max_limit' => 400000.0,
                'payment_method_ids' => $methodsPK,
                'terms' => 'Instant release after payment confirmation. Meezan Bank / Easypaisa preferred.',
                'status' => 'active',
            ]);
        }

        // Crypto King Sell ad
        $ad2 = Advertisement::where('user_id', $users[1]->id)->where('type', 'sell')->first();
        if (! $ad2) {
            $ad2 = Advertisement::create([
                'user_id' => $users[1]->id, // Crypto King
                'country_id' => $pakistan->id,
                'type' => 'sell',
                'amount' => 4000.0,
                'rate' => 279.10,
                'min_limit' => 10000.0,
                'max_limit' => 1000000.0,
                'payment_method_ids' => [$methodsPK[0], $methodsPK[1]],
                'terms' => 'JazzCash or Easypaisa transfers only. No third party payments allowed.',
                'status' => 'active',
            ]);
        }

        // Zeeshan Buy ad
        $ad3 = Advertisement::where('user_id', $users[2]->id)->where('type', 'buy')->first();
        if (! $ad3) {
            $ad3 = Advertisement::create([
                'user_id' => $users[2]->id, // Zeeshan Trader
                'country_id' => $pakistan->id,
                'type' => 'buy',
                'amount' => 800.0,
                'rate' => 277.20,
                'min_limit' => 2000.0,
                'max_limit' => 200000.0,
                'payment_method_ids' => $methodsPK,
                'terms' => 'I am buying USDT. Instant cash transfer will be sent to your bank account.',
                'status' => 'active',
            ]);
        }

        // USDT Reserve USA Sell ad
        $ad4 = Advertisement::where('user_id', $users[4]->id)->where('type', 'sell')->first();
        if (! $ad4) {
            $ad4 = Advertisement::create([
                'user_id' => $users[4]->id, // USDT Reserve
                'country_id' => $usa->id,
                'type' => 'sell',
                'amount' => 5000.0,
                'rate' => 1.02,
                'min_limit' => 100.0,
                'max_limit' => 5000.0,
                'payment_method_ids' => $methodsUS,
                'terms' => 'Quick release for Zelle payments. Fast and secure.',
                'status' => 'active',
            ]);
        }

        // 6. Seed completed orders to simulate realistic history
        if ($ad1 && Order::where('advertisement_id', $ad1->id)->count() < 10) {
            for ($i = 0; $i < 10; $i++) {
                $order = Order::create([
                    'advertisement_id' => $ad1->id,
                    'buyer_id' => $buyer->id,
                    'seller_id' => $users[0]->id, // Apex
                    'amount_usdt' => 100.0,
                    'amount_fiat' => 27850.0,
                    'rate' => 278.50,
                    'status' => 'completed',
                ]);
                $order->escrow()->create([
                    'wallet_id' => $users[0]->wallet->id,
                    'amount' => 100.0,
                    'status' => 'released',
                ]);
            }
        }

        // 7. Seed platform settings
        $settings = [
            'platform_name' => ['value' => 'TradeFlow P2P', 'group' => 'general'],
            'platform_logo' => ['value' => '', 'group' => 'general'],
            'fee_percentage' => ['value' => '1.0', 'group' => 'fees'],
            'min_limit_usdt' => ['value' => '10.0', 'group' => 'fees'],
            'max_limit_usdt' => ['value' => '5000.0', 'group' => 'fees'],
            'nowpayments_api_key' => ['value' => '', 'group' => 'nowpayments'],
            'nowpayments_sandbox' => ['value' => 'true', 'group' => 'nowpayments'],
            'nowpayments_ipn_secret' => ['value' => '', 'group' => 'nowpayments'],
            'google_recaptcha_site_key' => ['value' => '', 'group' => 'theme'],
            'google_recaptcha_secret_key' => ['value' => '', 'group' => 'theme'],
            'recaptcha_enabled' => ['value' => 'false', 'group' => 'theme'],
            'email_verification_required' => ['value' => 'false', 'group' => 'general'],

            // New dynamic help and contact configurations
            'whatsapp_number' => ['value' => '+923001234567', 'group' => 'general'],
            'telegram_link' => ['value' => 'https://t.me/tradeflow_p2p', 'group' => 'general'],
            'support_email' => ['value' => 'support@tradeflow.com', 'group' => 'general'],
            'support_url' => ['value' => 'https://tradeflow.com/support', 'group' => 'general'],

            // Hero section details
            'hero_title' => ['value' => 'Buy & Sell USDT Securely', 'group' => 'general'],
            'hero_subtitle' => ['value' => 'Trade with verified users, 100% secure escrow locking, and low operations fee.', 'group' => 'general'],

            // Statistics counters
            'stats_registered_users' => ['value' => '14250', 'group' => 'general'],
            'stats_completed_trades' => ['value' => '89400', 'group' => 'general'],
            'stats_daily_volume' => ['value' => '485000', 'group' => 'general'],
            'stats_active_traders' => ['value' => '4200', 'group' => 'general'],
            'stats_success_rate' => ['value' => '99.5', 'group' => 'general'],
            'landing_announcement' => ['value' => 'Warning: Zero fee promo has been extended for another 30 days! Trade securely.', 'group' => 'general'],
        ];

        foreach ($settings as $key => $data) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $data['value'], 'group' => $data['group']]
            );
        }
    }
}
