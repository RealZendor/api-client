<?php

namespace studiointern\Api;

use studiointern\Io\StorageInterface;
use studiointern\Io\SessionStorage;
use studiointern\Io\FileStorage;

class ApiConfig
{
    public const VERSION = '1.1.1';
    public const USER_AGENT = 'SI/API-Tester/' . self::VERSION;
    public const OAUTH_TOKEN_URL = '/oauth/token';

    public const ENDPOINTS = [
        '/pub/tariffs'              => ['GET', 'Preislisten', 1, ['tariffs'], false],
        '/pub/summer_courses'       => ['GET', 'Sommerkurse', 1, ['courses', 'rooms'], false],
        '/pub/studio/signature'     => ['GET', 'Studio-Signatur anzeigen', 2, ['data'], false],
        '/pub/schedule'             => ['GET', 'Kursplan', 1, ['events', 'planned_events'], false],
        '/pub/plans'                => ['GET', 'Pläne', 1, ['plans'], false],
        '/pub/nextcourses'          => ['GET', 'Nächste Kurse (nur bei echtem Kalender)', 1, ['courses'], false],
        '/pub/events'               => ['GET', 'Veranstaltungen', 1, ['events'], false],
        '/pub/courses'              => ['GET', 'Kurse', 1, ['courses'], false],
        '/pub/courses/styles'       => ['GET', 'Kursgruppen (Tanzstile) anzeigen', 2, ['data'], false],
        '/pub/customer'             => ['GET', 'Workflow für Kunden-Anlage zeigen', 2, ['data'], true],
        '/pub/customer/register'    => ['POST', 'Neuen Kunden anlegen', 2, ['data'], false],
        '/pub/mailcheck'            => ['GET', 'Mailcheck', 2, ['data'], true],
        '/own/customer'             => ['GET', 'Kundenliste (Studiobesitzer)', 2, ['data'], false],
        '/own/customer'             => ['GET', 'Einen Kunden anzeigen (Studiobesitzer)', 2, ['data'], true],
        '/own/student'              => ['GET', 'Schülerliste (Studiobesitzer)', 2, ['data'], false],
        '/own/student'              => ['GET', 'Einen Schüler anzeigen (Studiobesitzer)', 2, ['data'], true],
        '/own/account'              => ['POST', 'Kontobuchung erstellen', 2, ['data'], false],
    ];

    /**
     * Endpoints that require OAuth authentication
     */
    public const ENDPOINTS_OAUTH = [
        '/own/customer',
        '/own/customer/',
        '/own/account',
        '/own/student',
        '/own/student/',
    ];

    private array $credentials;
    private array $headers;
    private ?string $endpoint;
    private string $api_base_uri;
    private StorageInterface $storage;
    private string $endpoint_params;
    private string $client_type;
    private string $date_timezone;
    private int $timeout;

    public function __construct(string $config_file_full_path = '')
    {
        if (empty($config_file_full_path)) {
            $config_file_full_path = realpath($_SERVER['DOCUMENT_ROOT'] . '/../config.php');
        }
        if (! is_readable($config_file_full_path)) {
            throw new \Exception('Config file not found or not readable');
        }

        // check if config file is outside webroot
        if (strpos($config_file_full_path, $_SERVER['DOCUMENT_ROOT']) === 0) {
            throw new \Exception('Config file is inside webroot which is not allowed.');
        }

        $config = include $config_file_full_path;
        $this->credentials  = $config['credentials'];
        $this->headers      = $config['headers'];
        $this->api_base_uri = $config['misc']['api_base_uri'];
        $this->storage      = match ($config['misc']['storage']) {
            'session' => new SessionStorage([]),
            'file' => new FileStorage($config['misc']),
            default => throw new \Exception('Invalid storage type'),
        };
        $this->endpoint = null;
        $this->endpoint_params = '';
        $this->client_type = $config['misc']['client_type'] ?? ApiClientFactory::CLIENT_TYPE_GUZZLE;
        $this->date_timezone = $config['misc']['timezone'] ?? 'Europe/Berlin';
        $this->timeout       = $config['misc']['timeout'] ?? 30;
    }

    public function createApiClient(string $api_url, string $method, array $headers, array $body): ApiClientInterface
    {
        return ApiClientFactory::create($api_url, $method, $headers, $body, (int) $this->timeout, $this->client_type);
    }

    public function setEndpoint(string $endpoint)
    {
        if (!in_array($endpoint, array_keys(self::ENDPOINTS))) {
            throw new \InvalidArgumentException("Unknown endpoint");
        }
        $this->endpoint = $endpoint;
    }

    public function setEndpointParams(string $params)
    {
        $this->endpoint_params = $params;
    }

    public function getOauthPostData(): array
    {
        $post_data = [];
        $post_data['client_id']     = $this->credentials['oauth']['client_id'];
        $post_data['client_secret'] = $this->credentials['oauth']['client_secret'];
        $post_data['grant_type']    = $this->credentials['oauth']['grant_type'];
        $post_data['scope']         = $this->credentials['oauth']['scope'];
        return $post_data;
    }

    public function getOauthTokenUrl(): string
    {
        return $this->api_base_uri .
            '/' . $this->credentials['tenant_id'] .
            '/v2' .
            self::OAUTH_TOKEN_URL;
    }

    public function getEndpointUrl(): string
    {
        if (null === $this->endpoint) {
            throw new \Exception('Endpoint not set');
        }
        return $this->api_base_uri .
            '/' . $this->credentials['tenant_id'] .
            '/v' . self::ENDPOINTS[$this->endpoint][2] .
            $this->endpoint .
            $this->endpoint_params;
    }

    public function getEndpointMethod(): string
    {
        if (null === $this->getEndpoint()) {
            throw new \Exception('Endpoint not set');
        }
        return self::ENDPOINTS[$this->getEndpoint()][0];
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function getDefaultHeaders(int $version = 0): array
    {
        if ($version === 0) {
            $version = $this->getVersion();
        }

        $headers = array_merge(
            [
                'User-Agent'    => self::USER_AGENT . " (" . $this->credentials['tenant_id'] . ")",
                'Accept'        => 'application/json',
            ],
            $this->headers
        );

        return match ($version) {
            1 => array_merge($headers, [
                'Authorization' => $this->getApiKey(),
            ]),
            2 => array_merge($headers, [
                'X-Api-Key'     => $this->getApiKey(),
            ])
        };
    }

    public function getEndpoint(bool $with_params = false): string
    {
        return $this->endpoint . ($with_params ? $this->endpoint_params : '');
    }

    public function getVersion(): int
    {
        return (int) self::ENDPOINTS[$this->getEndpoint()][2];
    }

    public function getApiKey(): string
    {
        if (null === $this->credentials['api_key']) {
            throw new \Exception('API key not set');
        }
        return $this->credentials['api_key'];
    }

    public function getEndpointDataKeys(): ?array
    {
        return self::ENDPOINTS[$this->getEndpoint()][3];
    }

    public function requiresOauth(): bool
    {
        return in_array($this->getEndpoint(), self::ENDPOINTS_OAUTH);
    }

    public function requiresParams(): bool
    {
        return (bool) self::ENDPOINTS[$this->getEndpoint()][4];
    }

    public function getDateTimezone(): string
    {
        return $this->date_timezone;
    }
}
