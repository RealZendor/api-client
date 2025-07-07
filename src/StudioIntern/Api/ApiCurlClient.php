<?php

namespace StudioIntern\Api;

class ApiCurlClient implements ApiClientInterface
{
    private string $api_url;
    private string $method;
    private array $headers;
    private array $body;
    private int $timeout;

    public function __construct(string $api_url, string $method, array $headers, array $body, int $timeout)
    {
        $this->api_url = $api_url;
        $this->method = $method;
        $this->headers = $headers;
        // filter the body: omit these keys: endpoint, getData, do
        $this->body = array_diff_key($body, array_flip(['endpoint', 'params', 'do']));
        $this->timeout = $timeout;
    }

    public function addHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    private function formatHeaders(array $headers): array
    {
        $formatted_headers = [];
        foreach ($headers as $key => $value) {
            $formatted_headers[] = $key . ': ' . $value;
        }
        return $formatted_headers;
    }

    public function execute(array $post_data = []): string
    {
        if (count($post_data) > 0) {
            $this->body = $post_data;
        }

        // Initialize cURL session
        $ch = curl_init();

        // Set the URL
        curl_setopt($ch, CURLOPT_URL, $this->api_url);

        // Set the request method
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);

        // Set headers
        $headers = $this->formatHeaders($this->headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Set to return the response as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Set to follow redirects
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        // Set SSL verification
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        // Set to include headers in the output
        curl_setopt($ch, CURLOPT_HEADER, true);

        // Set timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        // Set request body for POST requests
        if ($this->method === 'POST' && is_array($this->body) && count($this->body) > 0) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->body));
        }
        // for GET requests, add the query parameters to the URL
        if ($this->method === 'GET' && is_array($this->body) && count($this->body) > 0) {
            $this->api_url .= '?' . http_build_query($this->body);
            curl_setopt($ch, CURLOPT_URL, $this->api_url);
        }

        // Execute the request
        $response = curl_exec($ch);

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $error = curl_error($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        // Close cURL session
        curl_close($ch);

        // Handle cURL errors
        if ($error) {
            $my_resp = $this->createMyResponse(500, "cURL Error: " . $error);
            return json_encode($my_resp);
        }

        // Handle HTTP errors
        if ($httpCode >= 500) {
            $my_resp = $this->createMyResponse($httpCode, "Der Server konnte die Anfrage nicht verarbeiten. Bitte versuchen Sie es später erneut.");
            return json_encode($my_resp);
        }

        // Split response into headers and body
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        // Parse headers into an array
        $headerArray = [];
        foreach (explode("\r\n", $headers) as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headerArray[trim($key)] = trim($value);
            }
        }

        // Parse and handle the response body
        $result = json_decode($body, true);

        // Add headers to the response
        $result['headers'] = $headerArray;
        $result['http_code'] = $httpCode;

        return json_encode($result);
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
