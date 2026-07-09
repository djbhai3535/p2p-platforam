<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NOWPaymentsWebhookController extends Controller
{
    protected WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Handle NOWPayments IPN callback.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $signature = $request->header('x-nowpayments-sig');

        Log::info('NOWPayments webhook received:', [
            'payload' => $payload,
            'signature' => $signature,
        ]);

        if (empty($signature)) {
            return response()->json(['error' => 'Missing signature header.'], 400);
        }

        try {
            $success = $this->walletService->processDepositWebhook($payload, $signature);

            return response()->json(['success' => $success]);
        } catch (Exception $e) {
            Log::error('NOWPayments webhook processing error: '.$e->getMessage());

            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
