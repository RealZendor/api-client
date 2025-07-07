<?php

/**
 * Send an API request
 * Example for using the StudioIntern API
 *
 * @category API
 * @package  StudioIntern
 * @author   Andreas Vent-Schmidt <av@studiointern.de>
 * @license  proprietary
 * @link     https://api.studiointern.de
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use StudioIntern\Api\ApiConfig;
use StudioIntern\Api\ApiOauthUser;
use StudioIntern\Exception\UserNotLoggedInException;

// Initialize config if not already done in the calling script
if (!is_a($Config, 'StudioIntern\Api\ApiConfig')) {
    $Config = new ApiConfig();
}

if (isset($post_data['endpoint'])) {
    $Config->setEndpoint($post_data['endpoint']);
}

if (isset($post_data['params'])) {
    $Config->setEndpointParams($post_data['params']);
}

// adjust the name of the form field 'params' if needed
if (true == $Config->requiresParams() && empty($post_data['params'])) {
    $_ERROR[] = "Der Endpoint " . $Config->getEndpoint() . " benötigt Parameter. Bitte fügen Sie diese hinzu.";
    return;
}

// don't fake editor's username
if ('/own/account' == $Config->getEndpoint()) {
    $post_data['pmt_user'] = $Config->getStorage()->getValue(ApiOauthUser::USERNAME_STORAGE_KEY);
}

$headers = $Config->getDefaultHeaders();

if (true === $Config->requiresOauth()) {
    $oauthClient = new ApiOauthUser($Config);
    try {
        $token = $oauthClient->getToken();
        $headers['Authorization'] = 'Bearer ' . $token;
    } catch (UserNotLoggedInException $e) {
        header('Location: /login.php?redirect=' . rawurlencode($_SERVER['PHP_SELF']));
        exit;
    }
}

try {
    // Create API client with default headers and post data
    $apiClient = $Config->createApiClient(
        $Config->getEndpointUrl(),
        $Config->getEndpointMethod(),
        $headers,
        $post_data
    );

    // Execute the API call
    $response = $apiClient->execute();
    $result = json_decode($response, true);

    if (204 == $result['http_code']) {
        $_MESSAGE[] = "OK, aber keine Daten zurückgegeben.";
        return;
    }
    // the API versions 1 and 2 have different response formats
    switch ($Config->getVersion()) {
        case 1:
            if (isset($result['message']) && 'OK' === $result['message']) {
                $_MESSAGE[] = $result['message'];
                $data_keys = $Config->getEndpointDataKeys();
                $data = (null === $data_keys) ? $result : [];
                if (null !== $data_keys) {
                    foreach ($data_keys as $data_key) {
                        $data[$data_key] = $result[$data_key] ?? null;
                    }
                }
            } else {
                $_ERROR[] = $result['message'] ?? 'Unknown error occurred';
            }
            $headers = $result['headers'] ?? null;
            break;
        case 2:
            if (isset($result['success']) && $result['success']) {
                $_MESSAGE[] = $result['message'];
            } else {
                $_ERROR[] = $result['message'] ?? 'Unknown error occurred';
                $_ERROR[] = "http code: " . ($result['http_code'] ?? 'Unknown http code');
            }
            $data = $result['data'] ?? null;
            $meta = $result['meta'] ?? null;
            $headers = $result['headers'] ?? null;
            break;
    }
} catch (\Exception $e) {
    $_ERROR[] = $e->getMessage();
}
