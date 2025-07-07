<?php

namespace studiointern\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class ApiGuzzleClient implements ApiClientInterface
{
    private string $api_url;
    private string $method;
    private array $headers;
    private array $body;
    private Client $client;

    public function __construct(string $api_url, string $method, array $headers, array $body, int $timeout)
    {
        $this->api_url  = $api_url;
        $this->method   = $method;
        $this->headers  = $headers;

        $this->body     = array_diff_key($body, array_flip(['endpoint', 'params', 'do']));
        $this->client   = new Client(['timeout' => $timeout]);
    }

    public function addHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    public function execute(array $post_data = []): string
    {
        try {
            $options = [
                'headers' => $this->headers,
                'http_errors' => false, // Don't throw exceptions for HTTP errors
                'verify' => true, // Enable SSL verification
                'allow_redirects' => true,
            ];

            if (count($post_data) > 0) {
                $this->body = $post_data;
            }

            // Add request body for POST requests
            if ($this->method === 'POST' && count($this->body) > 0) {
                $options['form_params'] = $this->body;
            }

            // For GET requests, add query parameters
            if ($this->method === 'GET' && count($this->body) > 0) {
                $options['query'] = $this->body;
            }

            // Execute the request
            $response = $this->client->request($this->method, $this->api_url, $options);

            $result = [
                'headers' => $response->getHeaders(),
                'http_code' => $response->getStatusCode(),
            ];

            // Get the response body
            $body = $response->getBody()->getContents();
            $decodedBody = json_decode($body, true);

            // Merge the decoded body with our result
            if (is_array($decodedBody)) {
                $result = array_merge($decodedBody, $result);
            }

            return json_encode($result);
        } catch (GuzzleException $e) {
            $my_resp = $this->createMyResponse(500, "Guzzle Error: " . $e->getMessage());
            return json_encode($my_resp);
        }
    }

    private function createMyResponse(int $error_code, string $error_message): ApiPlainObject
    {
        $my_response = new ApiPlainObject();
        $my_response->success = false;
        $my_response->code = $error_code;
        $my_response->locale = 'de';
        $my_response->message = $error_message;
        return $my_response;
    }
}
