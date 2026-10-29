<?php

namespace FakeCrypto\Api;

use Exception;
use Throwable;

/**
 * Exception thrown when an API request fails.
 */
final class RequestBuilderException extends Exception
{
    private string $method;

    /**
     * @var array<string, mixed>
     */
    private array $errors;

    /**
     * @param array<string, mixed> $errors
     */
    public function __construct(
        string $message,
        int $responseCode,
        string $uri,
        array $errors = [],
        ?Throwable $previous = null
    ) {
        $this->method = $uri;
        $this->errors = $errors;

        parent::__construct($message, $responseCode, $previous);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array<string, mixed>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
