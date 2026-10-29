<?php

namespace FakeCrypto\Api;

/**
 * Entry point for the API client.
 */
final class Client
{
    public static function payout(string $payoutKey, string $merchantUuid, ?string $url = null): Payout
    {
        return new Payout($payoutKey, $merchantUuid, $url);
    }

    public static function payment(string $paymentKey, string $merchantUuid, ?string $url = null): Payment
    {
        return new Payment($paymentKey, $merchantUuid, $url);
    }
}
