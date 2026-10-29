<?php

namespace FakeCrypto\Api;

/**
 * Payments API.
 */
final class Payment
{
    private const string VERSION = 'v1';

    private RequestBuilder $requestBuilder;

    public function __construct(string $paymentKey, string $merchantUuid, ?string $url = null)
    {
        $this->requestBuilder = new RequestBuilder($paymentKey, $merchantUuid, $url);
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws RequestBuilderException
     */
    public function services(array $parameters = []): mixed
    {
        return $this->requestBuilder->sendRequest(self::VERSION . '/payment/services', $parameters);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws RequestBuilderException
     *
     * var string amount: Amount to pay
     * var string currency: Payment currency
     * var string network: Payment network
     * var string order_id: Order ID in your system
     * var string url_return: Redirect link
     * var string url_callback: Callback link
     * var boolean is_payment_multiple: Allow surcharges on payment
     * var string lifetime: Payment lifetime in seconds
     * var string to_currency: Currency to convert amount to
     */
    public function create(array $data): mixed
    {
        return $this->requestBuilder->sendRequest(self::VERSION . '/payment', $data);
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
    public function info(array $data = []): mixed
    {
        return $this->requestBuilder->sendRequest(self::VERSION . '/payment/info', $data);
    }

    /**
     * @param int|string $page Pagination cursor
     * @param array<string, mixed> $parameters Additional parameters
     *
     * @throws RequestBuilderException
     */
    public function history(int|string $page = 1, array $parameters = []): mixed
    {
        $data = array_merge($parameters, ['cursor' => (string) $page]);

        return $this->requestBuilder->sendRequest(self::VERSION . '/payment/list', $data);
    }

    /**
     * @param array<string, mixed> $parameters
     *
     * @throws RequestBuilderException
     */
    public function balance(array $parameters = []): mixed
    {
        return $this->requestBuilder->sendRequest(self::VERSION . '/balance', $parameters);
    }

    /**
     * uuid or order_id.
     *
     * @param array<string, mixed> $data
     *
     * @throws RequestBuilderException
     *
     * var string uuid: Payment's UUID
     * var string order_id: Order ID in your system
     */
    public function reSendNotifications(array $data): mixed
    {
        return $this->requestBuilder->sendRequest(self::VERSION . '/payment/resend', $data);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws RequestBuilderException
     *
     * var string order_id: Order ID in your system
     * var string url_callback: Callback url
     * var string network: Network
     * var string currency: Payment currency
     */
    public function createWallet(array $data): mixed
    {
        return $this->requestBuilder->sendRequest(self::VERSION . '/wallet', $data);
    }
}
