<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Language;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Default Languages
        $en = Language::create([
            'name' => 'English',
            'code' => 'en',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => true,
        ]);

        $ur = Language::create([
            'name' => 'Urdu',
            'code' => 'ur',
            'direction' => 'rtl',
            'is_active' => true,
            'is_default' => false,
        ]);

        // 2. Seed Default Countries
        $pakistan = Country::create([
            'name' => 'Pakistan',
            'iso_code' => 'PK',
            'currency_code' => 'PKR',
            'currency_symbol' => '₨',
            'phone_code' => '+92',
            'is_active' => true,
        ]);

        $usa = Country::create([
            'name' => 'United States',
            'iso_code' => 'US',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'phone_code' => '+1',
            'is_active' => true,
        ]);

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
            [
                'name' => 'MCB',
                'slug' => 'mcb',
                'fields' => [
                    ['name' => 'account_title', 'label' => 'Account Title', 'type' => 'text', 'required' => true],
                    ['name' => 'account_number', 'label' => 'Account Number', 'type' => 'text', 'required' => true],
                    ['name' => 'iban', 'label' => 'IBAN', 'type' => 'text', 'required' => true],
                ],
            ],
            [
                'name' => 'Allied Bank',
                'slug' => 'allied-bank',
                'fields' => [
                    ['name' => 'account_title', 'label' => 'Account Title', 'type' => 'text', 'required' => true],
                    ['name' => 'account_number', 'label' => 'Account Number', 'type' => 'text', 'required' => true],
                    ['name' => 'iban', 'label' => 'IBAN', 'type' => 'text', 'required' => true],
                ],
            ],
            [
                'name' => 'Bank Alfalah',
                'slug' => 'bank-alfalah',
                'fields' => [
                    ['name' => 'account_title', 'label' => 'Account Title', 'type' => 'text', 'required' => true],
                    ['name' => 'account_number', 'label' => 'Account Number', 'type' => 'text', 'required' => true],
                    ['name' => 'iban', 'label' => 'IBAN', 'type' => 'text', 'required' => true],
                ],
            ],
            [
                'name' => 'Faysal Bank',
                'slug' => 'faysal-bank',
                'fields' => [
                    ['name' => 'account_title', 'label' => 'Account Title', 'type' => 'text', 'required' => true],
                    ['name' => 'account_number', 'label' => 'Account Number', 'type' => 'text', 'required' => true],
                    ['name' => 'iban', 'label' => 'IBAN', 'type' => 'text', 'required' => true],
                ],
            ],
            [
                'name' => 'Askari Bank',
                'slug' => 'askari-bank',
                'fields' => [
                    ['name' => 'account_title', 'label' => 'Account Title', 'type' => 'text', 'required' => true],
                    ['name' => 'account_number', 'label' => 'Account Number', 'type' => 'text', 'required' => true],
                    ['name' => 'iban', 'label' => 'IBAN', 'type' => 'text', 'required' => true],
                ],
            ],
            [
                'name' => 'Bank of Punjab',
                'slug' => 'bank-of-punjab',
                'fields' => [
                    ['name' => 'account_title', 'label' => 'Account Title', 'type' => 'text', 'required' => true],
                    ['name' => 'account_number', 'label' => 'Account Number', 'type' => 'text', 'required' => true],
                    ['name' => 'iban', 'label' => 'IBAN', 'type' => 'text', 'required' => true],
                ],
            ],
            [
                'name' => 'Soneri Bank',
                'slug' => 'soneri-bank',
                'fields' => [
                    ['name' => 'account_title', 'label' => 'Account Title', 'type' => 'text', 'required' => true],
                    ['name' => 'account_number', 'label' => 'Account Number', 'type' => 'text', 'required' => true],
                    ['name' => 'iban', 'label' => 'IBAN', 'type' => 'text', 'required' => true],
                ],
            ],
        ];

        foreach ($paymentMethods as $pm) {
            PaymentMethod::create([
                'country_id' => $pakistan->id,
                'name' => $pm['name'],
                'slug' => $pm['slug'],
                'fields' => $pm['fields'],
                'is_active' => true,
            ]);
        }

        // 4. Seed default users
        // Admin
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
        // Set admin wallet balance for testing fee operations / reserves
        $admin->wallet->update([
            'available_balance' => 1000.00000000,
        ]);

        // Seller user
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
        $seller->wallet->update([
            'available_balance' => 500.00000000, // Preload 500 USDT
        ]);

        // Buyer user
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
        $buyer->wallet->update([
            'available_balance' => 50.00000000, // Preload 50 USDT
        ]);

        // 5. Seed default dynamic platform settings
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
        ];

        foreach ($settings as $key => $data) {
            Setting::create([
                'key' => $key,
                'value' => $data['value'],
                'group' => $data['group'],
            ]);
        }
    }
}
