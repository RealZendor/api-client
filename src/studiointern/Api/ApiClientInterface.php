<?php

namespace studiointern\Api;

interface ApiClientInterface
{
    /**
     * Creates a new API client instance
     *
     * @param string $api_url The URL to send the request to
     * @param string $method The HTTP method to use
     * @param array $headers The request headers
     * @param array $body The request body
     */
    public function __construct(string $api_url, string $method, array $headers, array $body, int $timeout);

    /**
     * Adds a header to the request
     *
     * @param string $key The header name
     * @param string $value The header value
     */
    public function addHeader(string $key, string $value): void;

    /**
     * Executes the API request
     *
     * @return string The JSON encoded response
     */
    public function execute(array $post_data = []): string;
}
