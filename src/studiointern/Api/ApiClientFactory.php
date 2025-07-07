<?php

namespace studiointern\Api;

class ApiClientFactory
{
    public const CLIENT_TYPE_GUZZLE = 'guzzle';
    public const CLIENT_TYPE_CURL = 'curl';

    /**
     * Creates an API client instance based on the configured type
     *
     * @param string $api_url The URL to send the request to
     * @param string $method The HTTP method to use
     * @param array $headers The request headers
     * @param array $body The request body
     * @param string|null $clientType The type of client to create (defaults to Guzzle)
     * @return ApiClientInterface
     * @throws \InvalidArgumentException If an invalid client type is specified
     */
    public static function create(
        string $api_url,
        string $method,
        array $headers,
        array $body,
        int $timeout,
        ?string $clientType = self::CLIENT_TYPE_GUZZLE
    ): ApiClientInterface {

        switch ($clientType) {
            case self::CLIENT_TYPE_GUZZLE:
                if (!class_exists(\GuzzleHttp\Client::class)) {
                    throw new \RuntimeException('Guzzle HTTP client is not installed. Please install it via Composer or set the client type to "curl".');
                }
                return new ApiGuzzleClient($api_url, $method, $headers, $body, $timeout);
            case self::CLIENT_TYPE_CURL:
                if (!function_exists('curl_init')) {
                    throw new \RuntimeException('cURL is not installed. Please install it via your system package manager or set the client type to "guzzle".');
                }
                return new ApiCurlClient($api_url, $method, $headers, $body, $timeout);
            default:
                throw new \InvalidArgumentException("Invalid API client type: $clientType");
        }
    }
}
