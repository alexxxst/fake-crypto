<?php

namespace FakeCrypto\Api;

/**
 * Payouts API.
 */
final class Payout
{
    private const string VERSION = 'v1';

    private RequestBuilder $requestBuilder;

    public function __construct(string $payoutKey, string $merchantUuid, ?string $url = null)
    {
        $this->requestBuilder = new RequestBuilder($payoutKey, $merchantUuid, $url);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws RequestBuilderException
     *
     * var string amount: Amount to payout
     * var string currency: Currency
     * var string network: Network
     * var string order_id: Your id
     * var string address: Wallet number
     * var string is_subtract: From where to withdraw the commission (1 - from the balance, 0 - from the amount)
     * var string url_callback: Callback link
     */
    public function create(array $data): mixed
    {
        return $this->requestBuilder->sendRequest(self::VERSION . '/payout', $data);
    }

    /**
     * uuid or order_id.
     *
     * @param array<string, mixed> $data
     *
     * @throws RequestBuilderException
     *
     * var string uuid
     * var string order_id
     */
    public function info(array $data): mixed
    {
        return $this->requestBuilder->sendRequest(self::VERSION . '/payout/info', $data);
    }
}
