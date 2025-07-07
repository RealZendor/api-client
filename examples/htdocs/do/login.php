<?php

/**
 * Login to the API
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
use StudioIntern\Io\RequestHelper;

// Initialize config
$Config = new ApiConfig();

$pars = [
    'username' => [FILTER_SANITIZE_ADD_SLASHES],
    'password' => [FILTER_SANITIZE_ADD_SLASHES],
    'redirect' => [FILTER_SANITIZE_ADD_SLASHES],
];

$post_data = RequestHelper::filterInput($pars);
$oauthClient = new ApiOauthUser($Config);

try {
    if (true === $oauthClient->login($post_data['username'], $post_data['password'])) {
        header('Location: ' . urldecode($post_data['redirect'] ?? ''));
        exit;
    }
    $_ERROR[] = 'Login failed';
} catch (UserNotLoggedInException $e) {
    $_ERROR[] = $e->getMessage();
} catch (\Exception $e) {
    $_ERROR[] = 'Login failed: ' . $e->getMessage();
}
