<?php

namespace FakeCrypto\Api;

use JsonException;

/**
 * Builds and sends signed requests to the API.
 */
final class RequestBuilder
{
    public const string API_URL = 'https://api.cryptomus.com';

    private const int CONNECT_TIMEOUT = 10;

    private const int TIMEOUT = 30;

    private string $url;

    private string $secretKey;

    private string $merchantUuid;

    public function __construct(string $secretKey, string $merchantUuid, ?string $url = null)
    {
        $this->secretKey = $secretKey;
        $this->merchantUuid = $merchantUuid;
        $this->url = rtrim($url ?: self::API_URL, '/');
    }

    /**
     * @param array<string, mixed> $data
     *
     * @throws RequestBuilderException
     * @throws JsonException
     */
    public function sendRequest(string $uri, array $data = []): mixed
    {
        $curl = curl_init();

        if ($curl === false) {
            throw new RequestBuilderException('Unable to initialize cURL', 0, $uri);
        }

        $body = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $configured = curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => $this->url . '/' . ltrim($uri, '/'),
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Content-Type: application/json;charset=UTF-8',
                    'merchant: ' . $this->merchantUuid,
                    'sign: ' . md5(base64_encode($body) . $this->secretKey),
                ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
                CURLOPT_TIMEOUT => self::TIMEOUT,
            ]
        );

        if ($configured === false) {
            throw new RequestBuilderException('Unable to configure cURL', 0, $uri);
        }

        $response = curl_exec($curl);
        $responseCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if ($response === false) {
            throw new RequestBuilderException(curl_error($curl), $responseCode, $uri);
        }

        if ($response === '') {
            if ($responseCode >= 200 && $responseCode < 300) {
                return true;
            }

            throw new RequestBuilderException('Empty response', $responseCode, $uri);
        }

        try {
            $json = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RequestBuilderException('Invalid JSON response', $responseCode, $uri, [], $exception);
        }

        if (!is_array($json)) {
            throw new RequestBuilderException('Invalid JSON response', $responseCode, $uri);
        }

        $state = $json['state'] ?? null;
        $result = $json['result'] ?? null;
        $message = $json['message'] ?? null;
        $errors = $json['errors'] ?? [];

        if ($responseCode < 200 || $responseCode >= 300 || ($state !== null && (string) $state !== '0')) {
            if (is_string($message) && $message !== '') {
                throw new RequestBuilderException($message, $responseCode, $uri);
            }

            if (is_array($errors) && $errors !== []) {
                throw new RequestBuilderException('Validation error', $responseCode, $uri, $errors);
            }

            throw new RequestBuilderException('Request failed', $responseCode, $uri);
        }

        return $result ?? true;
    }
}
