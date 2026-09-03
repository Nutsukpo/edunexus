<?php

namespace App\Http\Controllers;

use App\Models\FeePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackWebhookController extends Controller
{
    /**
     * Handle Paystack webhook events.
     *
     * The webhook is intentionally independent from the
     * student's browser callback.
     */
    public function handle(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Verify Paystack signature
        |--------------------------------------------------------------------------
        */

        $signature = $request->header('x-paystack-signature');

        if (!$signature) {
            Log::warning(
                'Paystack webhook rejected: missing signature.'
            );

            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        $secretKey = config(
            'services.paystack.secret_key'
        );

        if (!$secretKey) {
            Log::error(
                'Paystack webhook failed: secret key is not configured.'
            );

            return response()->json([
                'message' => 'Webhook configuration error.',
            ], 500);
        }

        $expectedSignature = hash_hmac(
            'sha512',
            $request->getContent(),
            $secretKey
        );

        if (!hash_equals(
            $expectedSignature,
            $signature
        )) {
            Log::warning(
                'Paystack webhook rejected: invalid signature.'
            );

            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Read payload
        |--------------------------------------------------------------------------
        */

        $payload = $request->json()->all();

        $event = $payload['event'] ?? null;
        $data = $payload['data'] ?? [];

        $reference = $data['reference'] ?? null;

        Log::info(
            'Paystack webhook received.',
            [
                'event' => $event,
                'reference' => $reference,
                'transaction_id' => $data['id'] ?? null,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | 3. Only process successful charges
        |--------------------------------------------------------------------------
        */

        if ($event !== 'charge.success') {
            return response()->json([
                'message' => 'Event received.',
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | 4. Require reference
        |--------------------------------------------------------------------------
        */

        if (!$reference) {
            Log::warning(
                'Paystack charge.success webhook has no reference.'
            );

            return response()->json([
                'message' => 'Missing transaction reference.',
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Find EDUNEXUS payment
        |--------------------------------------------------------------------------
        */

        $payment = FeePayment::where(
            'reference_number',
            $reference
        )->first();

        if (!$payment) {
            Log::warning(
                'Paystack webhook payment not found.',
                [
                    'reference' => $reference,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | We acknowledge the webhook so Paystack does not repeatedly
            | retry an event that belongs to an unknown EDUNEXUS payment.
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'message' => 'Payment not found.',
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | 6. Idempotency
        |--------------------------------------------------------------------------
        */

        if ($payment->status === 'completed') {
            Log::info(
                'Paystack webhook ignored: payment already completed.',
                [
                    'payment_id' => $payment->id,
                    'reference' => $reference,
                ]
            );

            return response()->json([
                'message' => 'Payment already completed.',
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | 7. Verify transaction directly with Paystack
        |--------------------------------------------------------------------------
        |
        | Do not rely solely on the webhook payload.
        |
        */

        try {

            $verificationResponse = Http::withToken(
                $secretKey
            )
                ->acceptJson()
                ->timeout(15)
                ->get(
                    'https://api.paystack.co/transaction/verify/'
                    . urlencode($reference)
                );

        } catch (\Throwable $e) {

            Log::error(
                'Paystack webhook verification request failed.',
                [
                    'payment_id' => $payment->id,
                    'reference' => $reference,
                    'error' => $e->getMessage(),
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | Return non-2xx so Paystack can retry the webhook.
            |--------------------------------------------------------------------------
            */

            return response()->json([
                'message' => 'Unable to verify transaction.',
            ], 500);
        }

        /*
        |--------------------------------------------------------------------------
        | 8. Check Paystack API response
        |--------------------------------------------------------------------------
        */

        if (
            !$verificationResponse->successful() ||
            !$verificationResponse->json('status')
        ) {

            Log::error(
                'Paystack webhook transaction verification failed.',
                [
                    'payment_id' => $payment->id,
                    'reference' => $reference,
                    'response' =>
                        $verificationResponse->json(),
                ]
            );

            return response()->json([
                'message' => 'Transaction verification failed.',
            ], 500);
        }

        $transaction = $verificationResponse->json(
            'data'
        );

        /*
        |--------------------------------------------------------------------------
        | 9. Verify transaction status
        |--------------------------------------------------------------------------
        */

        if (
            ($transaction['status'] ?? null)
            !== 'success'
        ) {

            Log::warning(
                'Paystack transaction is not successful.',
                [
                    'payment_id' => $payment->id,
                    'reference' => $reference,
                    'status' =>
                        $transaction['status'] ?? null,
                ]
            );

            return response()->json([
                'message' => 'Transaction not successful.',
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | 10. Verify reference
        |--------------------------------------------------------------------------
        */

        if (
            ($transaction['reference'] ?? null)
            !== $reference
        ) {

            Log::error(
                'Paystack transaction reference mismatch.',
                [
                    'payment_id' => $payment->id,
                    'expected_reference' => $reference,
                    'received_reference' =>
                        $transaction['reference'] ?? null,
                ]
            );

            return response()->json([
                'message' => 'Reference mismatch.',
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | 11. Verify currency
        |--------------------------------------------------------------------------
        */

        $expectedCurrency = strtoupper(
            config(
                'services.paystack.currency',
                'GHS'
            )
        );

        $receivedCurrency = strtoupper(
            $transaction['currency'] ?? ''
        );

        if (
            $receivedCurrency !==
            $expectedCurrency
        ) {

            Log::error(
                'Paystack currency mismatch.',
                [
                    'payment_id' => $payment->id,
                    'reference' => $reference,
                    'expected_currency' =>
                        $expectedCurrency,
                    'received_currency' =>
                        $receivedCurrency,
                ]
            );

            return response()->json([
                'message' => 'Currency mismatch.',
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | 12. Verify amount
        |--------------------------------------------------------------------------
        |
        | Paystack amounts are represented in the smallest
        | currency unit. For GHS this is pesewas.
        |
        */

        $expectedAmount = (int) round(
            ((float) $payment->net_amount) * 100
        );

        $receivedAmount = (int) (
            $transaction['amount'] ?? 0
        );

        if (
            $receivedAmount !==
            $expectedAmount
        ) {

            Log::error(
                'Paystack webhook amount mismatch.',
                [
                    'payment_id' => $payment->id,
                    'reference' => $reference,
                    'expected_amount' =>
                        $expectedAmount,
                    'received_amount' =>
                        $receivedAmount,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | IMPORTANT:
            |
            | Do NOT mark the payment failed here.
            |
            | Leave it pending and record the problem.
            |--------------------------------------------------------------------------
            */

            $payment->update([
                'metadata' =>
                    array_merge(
                        $payment->metadata ?? [],
                        [
                            'webhook_error' =>
                                'Paystack amount mismatch',

                            'expected_amount' =>
                                $expectedAmount,

                            'received_amount' =>
                                $receivedAmount,

                            'webhook_verified_at' =>
                                now()->toDateTimeString(),
                        ]
                    ),
            ]);

            return response()->json([
                'message' => 'Amount mismatch.',
            ], 200);
        }

        /*
        |--------------------------------------------------------------------------
        | 13. Complete payment
        |--------------------------------------------------------------------------
        |
        | FeePayment::updated() will automatically:
        |
        | - recalculate fee account
        | - update amount_paid
        | - update balance
        | - update account status
        | - create FeeReceipt
        |
        */

        $payment->update([
            'status' => 'completed',

            'transaction_id' =>
                (string) (
                    $transaction['id']
                    ?? $payment->transaction_id
                ),

            'reference_number' =>
                $transaction['reference']
                ?? $reference,

            'payment_method' =>
                'paystack',

            'payment_date' =>
                !empty(
                    $transaction['paid_at']
                )
                    ? \Carbon\Carbon::parse(
                        $transaction['paid_at']
                    )->toDateString()
                    : now()->toDateString(),

            'metadata' =>
                array_merge(
                    $payment->metadata ?? [],
                    [
                        'paystack_status' =>
                            $transaction['status']
                            ?? null,

                        'paystack_channel' =>
                            $transaction['channel']
                            ?? null,

                        'gateway_response' =>
                            $transaction['gateway_response']
                            ?? null,

                        'paid_at' =>
                            $transaction['paid_at']
                            ?? null,

                        'webhook_event' =>
                            $event,

                        'webhook_verified_at' =>
                            now()->toDateTimeString(),
                    ]
                ),
        ]);

        /*
        |--------------------------------------------------------------------------
        | 14. Log successful completion
        |--------------------------------------------------------------------------
        */

        Log::info(
            'Paystack payment successfully completed through webhook.',
            [
                'payment_id' => $payment->id,
                'reference' => $reference,
                'transaction_id' =>
                    $payment->transaction_id,
            ]
        );

        return response()->json([
            'message' =>
                'Webhook processed successfully.',
        ], 200);
    }
}