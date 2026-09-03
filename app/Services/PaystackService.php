<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackService
{
    protected string $secret;
    protected string $baseUrl;

    public function __construct()
    {
        $this->secret = config('services.paystack.secret');

        $this->baseUrl = rtrim(
            config(
                'services.paystack.base_url',
                'https://api.paystack.co'
            ),
            '/'
        );
    }

    /**
     * Initiate a Ghana Mobile Money charge.
     */
    public function chargeMobileMoney(
        string $email,
        float $amount,
        string $phone,
        string $provider,
        string $reference,
        array $metadata = []
    ): array {

        $response = Http::withToken($this->secret)
            ->acceptJson()
            ->post(
                $this->baseUrl . '/charge',
                [
                    'email' => $email,

                    // Paystack expects the amount
                    // in the currency subunit.
                    // GHS uses pesewas.
                    'amount' => (int) round(
                        $amount * 100
                    ),

                    'currency' => 'GHS',

                    'reference' => $reference,

                    'mobile_money' => [
                        'phone' => $phone,
                        'provider' => $provider,
                    ],

                    'metadata' => $metadata,
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Paystack request failed: '
                . $response->body()
            );
        }

        $data = $response->json();

        if (!($data['status'] ?? false)) {
            throw new RuntimeException(
                $data['message']
                ?? 'Paystack rejected the payment request.'
            );
        }

        return $data;
    }

    /**
     * Verify a Paystack transaction.
     */
    public function verify(string $reference): array
    {
        $response = Http::withToken($this->secret)
            ->acceptJson()
            ->get(
                $this->baseUrl
                . '/transaction/verify/'
                . urlencode($reference)
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Paystack verification failed: '
                . $response->body()
            );
        }

        return $response->json();
    }
}