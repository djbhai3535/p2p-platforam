<?php

use App\Http\Controllers\Api\NOWPaymentsWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// NOWPayments Webhook IPN Callback (requires no auth)
Route::post('/payments/nowpayments/webhook', [NOWPaymentsWebhookController::class, 'handle'])
    ->name('api.nowpayments.webhook');
