<?php

namespace StudioIntern\Api;

use StudioIntern\Exception\UserNotLoggedInException;

class ApiOauthUser implements ApiUserInterface
{
    private string $token           = '';
    private string $refresh_token   = '';
    private int $token_expires_at   = 0;
    private bool $token_will_expire = false;
    private bool $token_valid       = false;

    private ApiConfig $config;
    private ApiClientInterface $api_client;

    public const TOKEN_GAP_TIME = 600;
    public const TOKEN_STORAGE_KEY = 'oauth_token';
    public const USERNAME_STORAGE_KEY = 'username';

    public function __construct(ApiConfig $config)
    {
        $this->config = $config;
        $headers = $this->config->getDefaultHeaders(2);
        $this->api_client = $config->createApiClient($this->config->getOauthTokenUrl(), 'POST', $headers, []);

        // Try to get token from storage
        $stored_token = $this->config->getStorage()->getValue(self::TOKEN_STORAGE_KEY);
        if (! empty($stored_token)) {
            $this->token = $stored_token['access_token'];
            $this->token_expires_at = $stored_token['expires_at'];
            $this->refresh_token = $stored_token['refresh_token'];
            $this->checkToken();
        }
    }

    /**
     * Check if the user is logged in
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->token_valid;
    }

    /**
     * Get the token
     *
     * @return string
     * @throws UserNotLoggedInException
     */
    public function getToken(): string
    {
        if (true === $this->token_valid) {
            if (true === $this->token_will_expire) {
                try {
                    return $this->refreshToken();
                } catch (\Exception $e) {
                    $this->deleteStorage();
                    throw new UserNotLoggedInException('Token expired and refresh failed: ' . $e->getMessage());
                }
            }
            return $this->token;
        } else {
            throw new UserNotLoggedInException('No valid token found');
        }
    }

    /**
     * Login to the API and get a new token
     *
     * This will set the token in storage and check if the token is valid.
     *
     * @param string $username
     * @param string $password
     * @return bool
     * @throws \Exception
     */
    public function login(string $username, string $password): bool
    {
        $post_data = $this->config->getOauthPostData();
        $post_data['username'] = $username;
        $post_data['password'] = $password;

        try {
            $token_data = $this->requestToken($post_data);
            $this->updateTokenData($token_data);
            $this->config->getStorage()->setValue(self::USERNAME_STORAGE_KEY, $username);
        } catch (\Exception $e) {
            throw new \Exception('Login failed: ' . $e->getMessage());
        }

        return $this->token_valid;
    }

    /**
     * Get the expiration date of the token
     *
     * @return \DateTime|null
     */
    public function getExpiration(): ?\DateTime
    {
        if (0 == $this->token_expires_at) {
            return null;
        }

        // the token expires at is in UTC, so we need to convert it to the local timezone
        $expires_at = new \DateTime('@' . $this->token_expires_at, new \DateTimeZone('UTC'));
        $expires_at->setTimezone(new \DateTimeZone($this->config->getDateTimezone()));
        return $expires_at;
    }

    /* ############ PRIVATE METHODS ############ */

    private function checkToken(): void
    {
        if (empty($this->token)) {
            $this->token_valid = false;
            $this->deleteStorage();
            return;
        }

        // tokens without expiration are always valid
        $token_expire_date = $this->getExpiration();
        if (null === $token_expire_date) {
            $this->token_will_expire = false;
            $this->token_valid = true;
        } else {
            $now = new \DateTime('now', new \DateTimeZone($this->config->getDateTimezone()));
            $this->token_will_expire = $now->getTimestamp() + self::TOKEN_GAP_TIME >= $token_expire_date->getTimestamp();
            $this->token_valid = $token_expire_date->getTimestamp() > $now->getTimestamp();
        }
    }

    private function refreshToken(): string
    {
        if (empty($this->refresh_token)) {
            $this->deleteStorage();
            throw new \Exception('No refresh token found');
        }

        $post_data = $this->config->getOauthPostData();
        $post_data['grant_type'] = 'refresh_token';
        $post_data['refresh_token'] = $this->refresh_token;

        try {
            $token_data = $this->requestToken($post_data);
            $this->updateTokenData($token_data);
        } catch (\Exception $e) {
            $this->deleteStorage();
            throw new \Exception('Refresh token failed: ' . $e->getMessage());
        }

        return $this->token;
    }

    private function requestToken(array $post_data): array
    {
        $response = $this->api_client->execute($post_data);
        $response_data = \json_decode($response, true);
        // the refresh token might be expired, so we need to check the response
        if ('refresh_token' === $post_data['grant_type'] && isset($response_data['error']) && 'Invalid refresh token' === $response_data['error_description']) {
            $this->deleteStorage();
            throw new \Exception('Refresh token expired');
        }

        $http_code = $response_data['http_code'];

        if ($http_code !== 200) {
            throw new \Exception('HTTP error: ' . $http_code);
        }

        return $response_data;
    }

    private function updateTokenData(array $token_data): void
    {
        $this->token = $token_data['access_token'];
        $this->token_expires_at = $token_data['expires_at'];
        $this->refresh_token = $token_data['refresh_token'];

        // Store token data
        $this->config->getStorage()->setValue(self::TOKEN_STORAGE_KEY, $token_data);
        $this->checkToken();
    }

    private function deleteStorage(): void
    {
        $this->config->getStorage()->deleteValue(self::TOKEN_STORAGE_KEY);
        $this->config->getStorage()->deleteValue(self::USERNAME_STORAGE_KEY);
    }
}
